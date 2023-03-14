<?php


namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAttachments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AccommodationServices
{
    /**
     * @var accommodation
     */
    private Accommodation $accommodation;
        /**
     * @var accommodationAttachments
     */
    private AccommodationAttachments $accommodationAttachments;

    public function __construct(Accommodation $accommodation, AccommodationAttachments $accommodationAttachments)
    {
        $this->accommodation = $accommodation;
        $this->accommodationAttachments = $accommodationAttachments;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if(!($this->accommodation->validate($request->all()))){
            return $this->accommodation->errors();
        }
    }
    /**
     * Show the form for creating a new Accommodation.
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {     
        $input = $request->all();
        // if (request()->hasFile('attachment')){
        //     $uploadedfile = $request->file('attachment');
        //     $fileName = time() . '.' . $uploadedfile->getClientOriginalExtension();
        //     $destinationPath = storage_path('uploads');
        //     $uploadedfile->move($destinationPath, $fileName);
        //     $input['attachment'] = "uploads/".$fileName;
        // }
        
        $accommodationData = $this->accommodation::create([
            'name' => $input["name"],
            'maximum_pax_per_unit' => $input["maximum_pax_per_unit"],
            'deposit' => $input["deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'vendor_id' => $input["vendor_id"],
        ]);
        $accommodationId = $accommodationData->id;
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();                    
                // $fileName = time() . '.' . $file->getClientOriginalExtension();                 
                $filePath = '/vendor/accommodation/' . $fileName; 
                // if (!Storage::disk('s3')->exists($filePath)) {
                    Storage::disk('s3')-> setvisibility($filePath, visibility: 'public'); 
                    $s3 = Storage::disk('s3'); 
                    $s3->put($filePath, file_get_contents($file)); 
                    $s3Path = Storage::disk('s3')->url($filePath);
                    echo $s3Path;
                    $data=$this->accommodationAttachments::create([
                            "file_id" => $accommodationId,
                            "file_name" => $fileName,
                            "file_type" => 'accommodation',
                            "file_url" => Storage::disk('s3')->url($filePath)                
                        ]);  
                // }         
            }
        }
        return $accommodationData;
        
    }
    /**
     * Display a listing of the Accommodation.
     *
     * @return LengthAwarePaginator
     */
    public function show()
    {
        return $this->accommodation::with('vendor')->paginate(10);
    }
    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        return $this->accommodation::with('accommodationData')->findorfail($id);
    }
    /**
     * Update the specified Accommodation data.
     *
     * @param $id
     * @param $request
     * @return mixed
     */
    public function update($id, $request): mixed
    {    
        $data = $this->accommodation::findorfail($id);
        $input = $request->all();
        if (request()->hasFile('attachment')){
            $uploadedfile = $request->file('attachment');
            $fileName = time() . '.' . $uploadedfile->getClientOriginalExtension();
            $destinationPath = storage_path('uploads');
            $uploadedfile->move($destinationPath, $fileName);
            $input['attachment'] = "uploads/".$fileName;

            if (request()->hasFile('attachment')){
                foreach($request->file('attachment') as $file){
                    $fileName = $file->getClientOriginalName();                    
                    // $fileName = time() . '.' . $file->getClientOriginalExtension();                 
                    $filePath = '/vendor/accommodation/' . $fileName; 
                    if (!Storage::disk('s3')->exists($filePath)) {
                        echo "test";
                        Storage::disk('s3')-> setvisibility($filePath, visibility: 'public'); 
                        $s3 = Storage::disk('s3'); 
                        $s3->put($filePath, file_get_contents($file)); 
                        $s3Path = Storage::disk('s3')->url($filePath);
                        $data=$this->accommodationAttachments::create([
                                "file_id" => $id,
                                "file_name" => $fileName,
                                "file_type" => 'accommodation',
                                "file_url" => Storage::disk('s3')->url($filePath)                
                            ]); 
                    }    
                }
            }
        }
        return $data->update($input);
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return void
     */    
    public function delete($id): void
    {   
        $data = $this->accommodation::findorfail($id);
        $data->delete();
    }
    /**
     * searching Accommodation data.
     *
     * @param $request
     * @return mixed
     */
    public function search($request)
    {
        return $this->accommodation->where('accommodation_name', 'like', '%' . $request->name . '%')->get(['name',
            'location',
            'square_feet',
            'accommodation_name',
            'maximum_pax_per_room',
            'cost_per_pax',
            'attachment',
            'deposit',
            'rent_per_month',
            'vendor_id',
            'id']);
    }

    public function deleteFile($request) { 
        // https://hcm-storage.s3.ap-south-1.amazonaws.com/vendor/accommodation/illustration-indian-army-soilder-holding-falg-india-pride-indian-army-soilder-holding-falg-india-pride-106768059.jpg
        $filePath = '/vendor/accommodation/illustration-indian-army-soilder-holding-falg-india-pride-indian-army-soilder-holding-falg-india-pride-106768059.jpg'; 
        $s3 = Storage::disk('s3'); 
        $s3->delete($filePath); 
    return $s3->url($filePath);
    }

}



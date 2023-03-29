<?php


namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAttachments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
    /**
     * @var Storage
     */
    private Storage $storage;

    public function __construct(Accommodation $accommodation, AccommodationAttachments $accommodationAttachments, Storage $storage)
    {
        $this->accommodation = $accommodation;
        $this->accommodationAttachments = $accommodationAttachments;
        $this->storage = $storage;
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
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->accommodation->validateUpdation($request->all()))){
            return $this->accommodation->errors();
        }
    }
    
    /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {     
        $input = $request->all();        
        $accommodationData = $this->accommodation::create([
            'name' => $input["name"],
            'location' => $input["location"],
            'maximum_pax_per_unit' => $input["maximum_pax_per_unit"],
            'deposit' => $input["deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'vendor_id' => $input["vendor_id"],
        ]);
        $accommodationId = $accommodationData->id;
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/accommodation/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->accommodationAttachments::create([
                        "file_id" => $accommodationId,
                        "file_name" => $fileName,
                        "file_type" => 'accommodation',
                        "file_url" =>  $fileUrl          
                    ]);  
            }
        }
        return $accommodationData;
        
    }
    /**
     *
     * @return LengthAwarePaginator
     */
    public function retrieveAll()
    {
        return $this->accommodation::with('vendor')->orderBy('accommodation.created_at','DESC')->paginate(10);
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function  retrieve($request) : mixed
    {
        return $this->accommodation::with('accommodationAttachments')->findorfail($request['id']);
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {    
        $data = $this->accommodation::findorfail($request['id']);
        $input = $request->all();
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/accommodation/' . $fileName; 
                if (!$this->storage::disk('linode')->exists($filePath)) {
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);
                    $data=$this->accommodationAttachments::create([
                            "file_id" => $request['id'],
                            "file_name" => $fileName,
                            "file_type" => 'accommodation',
                            "file_url" => $fileUrl                
                        ]); 
                }    
            }
        }
        return  [
            "isUpdated" => $data->update($input),
            "message" => "Updated Successfully"
        ];
    }
    /**
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {   
        $data = $this->accommodation::find($request['id']);        
        $data->accommodationAttachments()->delete();
        $data->delete();
        return  [
            "message" => "Deleted Successfully"
        ];
    }
    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->accommodationAttachments::find($request['id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function search($request)
    {
        return $this->accommodation->where('name', 'like', '%' . $request->search . '%')
        ->orWhere('location', 'like', '%' . $request->search . '%')
        ->orderBy('accommodation.created_at','DESC')
        ->paginate(10);
    }

}



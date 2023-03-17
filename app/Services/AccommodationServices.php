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
        return $this->accommodation::with('vendor')->paginate(10);
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
        return $data->update($input);
    }
    /**
     *
     * @param $request
     * @return void
     */    
    public function delete($request): void
    {   
        $data = $this->accommodation::find($request['id']);        
        $data->accommodationAttachments()->delete();
        $data->delete();
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function search($request): mixed
    {
        return $this->accommodation->where('name', 'like', '%' . $request->name . '%')
        ->get(['name',
            'location',
            'maximum_pax_per_unit',
            'deposit',
            'rent_per_month',
            'vendor_id',
            'id']);
    }

}



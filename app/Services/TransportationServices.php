<?php


namespace App\Services;

use App\Models\Transportation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use App\Models\TransportationAttachments;
use Illuminate\Support\Facades\Storage;

class TransportationServices
{
    /**
     * @var transportation
     */
    private Transportation $transportation;
    /**
     * @var transportationAttachments
     */
    private TransportationAttachments $transportationAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;

    public function __construct(Transportation $transportation, TransportationAttachments $transportationAttachments, Storage $storage)
    {
        $this->transportation = $transportation;
        $this->transportationAttachments = $transportationAttachments;
        $this->storage = $storage;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
       if(!($this->transportation->validate($request->all()))){
           return $this->transportation->errors();
       }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->transportation->validateUpdation($request->all()))){
            return $this->transportation->errors();
        }
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        $transportationData = $this->transportation::create([
            'driver_name' => $request["driver_name"],
            'driver_contact_number' => $request["driver_contact_number"],
            'vehicle_type' => $request["vehicle_type"],
            'number_plate' => $request["number_plate"],
            'vehicle_capacity' => $request["vehicle_capacity"],
            'vendor_id' => $request["vendor_id"],
        ]);
        $transportationId = $transportationData->id;
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/transportation/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->transportationAttachments::create([
                        "file_id" => $transportationId,
                        "file_name" => $fileName,
                        "file_type" => 'transportation',
                        "file_url" =>  $fileUrl          
                    ]);  
            }
        }
        return $transportationData;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->transportation::with('vendor','transportationAttachments')
        ->where(function ($query) use ($request) {
            if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
                $query->where('vendor_id', '=', $request['vendor_id']);
            }
            if (isset($request['search']) && !empty($request['search'])) {
                $query->where('vendor_id', '=', $request['vendor_id'])
                ->where('driver_name', 'like', '%' . $request['search'] . '%')
                ->orWhere('vehicle_type', 'like', '%' . $request['search'] . '%');
            }
        })
        ->orderBy('transportation.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

	 /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        return $this->transportation::with('vendor','transportationAttachments')->findorfail($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {     
        $data = $this->transportation::find($request['id']);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/transportation/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->transportationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'transportation',
                        "file_url" =>  $fileUrl          
                    ]);  
            }
        }
        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }
        return  [
            "isUpdated" => $data->update($request->all()),
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
        $data = $this->transportation::findorfail($request['id']);
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $data->transportationAttachments()->delete();
        $data->delete();
        return [
            "isDeleted" => true,
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
        $data = $this->transportationAttachments::find($request['id']); 
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
}
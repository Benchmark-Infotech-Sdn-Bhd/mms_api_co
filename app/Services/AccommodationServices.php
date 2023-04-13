<?php


namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAttachments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $user = JWTAuth::parseToken()->authenticate();
        $input['created_by'] = $user['id'];      
        $accommodationData = $this->accommodation::create([
            'name' => $input["name"],
            'location' => $input["location"],
            'maximum_pax_per_unit' => $input["maximum_pax_per_unit"],
            'deposit' => $input["deposit"],
            'rent_per_month' => $input["rent_per_month"],
            'vendor_id' => $input["vendor_id"],
            'tnb_bill_account_Number' => $input["tnb_bill_account_Number"],
            'water_bill_account_Number' => $input["water_bill_account_Number"],
            'created_by' => $input['created_by']
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
     * @param $request
     * @return LengthAwarePaginator
     */
    public function list($request)
    {
        return $this->accommodation::with('vendor','accommodationAttachments')
        ->where(function ($query) use ($request) {
            if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
                $query->where('vendor_id', '=', $request['vendor_id']);
            }
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('vendor_id', '=', $request['vendor_id'])
                ->where('name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('location', 'like', '%' . $request['search_param'] . '%');
            }
        })
        ->orderBy('accommodation.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
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
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 
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

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $data->accommodationAttachments()->delete();
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

}



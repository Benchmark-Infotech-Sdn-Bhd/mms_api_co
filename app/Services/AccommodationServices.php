<?php


namespace App\Services;

use App\Models\Accommodation;
use App\Models\AccommodationAttachments;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

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
     * @var Vendor
     */
    private Vendor $vendor;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    public function __construct(Accommodation $accommodation, AccommodationAttachments $accommodationAttachments, Vendor $vendor, Storage $storage, AuthServices $authServices)
    {
        $this->accommodation = $accommodation;
        $this->accommodationAttachments = $accommodationAttachments;
        $this->vendor = $vendor;
        $this->storage = $storage;
        $this->authServices = $authServices;
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

        $vendor = $this->vendor
        ->where('company_id', $user['company_id'])
        ->find($request['vendor_id']);

        if(is_null($vendor)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

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
        $user = JWTAuth::parseToken()->authenticate();
        return $this->accommodation::with('vendor','accommodationAttachments')
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','accommodation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
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
        ->select('accommodation.*')
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
        $user = JWTAuth::parseToken()->authenticate();
        $user['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->accommodation::with(['accommodationAttachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','accommodation.vendor_id')
            ->whereIn('vendors.company_id', $user['company_id']);
        })
        ->select('accommodation.id', 'accommodation.name', 'accommodation.location', 'accommodation.maximum_pax_per_unit', 'accommodation.deposit', 'accommodation.rent_per_month', 'accommodation.vendor_id', 'accommodation.created_by', 'accommodation.modified_by', 'accommodation.created_at', 'accommodation.updated_at', 'accommodation.deleted_at', 'accommodation.tnb_bill_account_Number','accommodation.water_bill_account_Number')
        ->find($request['id']);

    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {    

        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();

        $data = $this->accommodation
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','accommodation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->select('accommodation.id', 'accommodation.name', 'accommodation.location', 'accommodation.maximum_pax_per_unit', 'accommodation.deposit', 'accommodation.rent_per_month', 'accommodation.vendor_id', 'accommodation.created_by', 'accommodation.modified_by', 'accommodation.created_at', 'accommodation.updated_at', 'accommodation.deleted_at', 'accommodation.tnb_bill_account_Number','accommodation.water_bill_account_Number')
        ->find($request['id']);
        
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        
        $input['modified_by'] = $user['id']; 
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/vendor/accommodation/' . $fileName; 
                // if (!$this->storage::disk('linode')->exists($filePath)) {
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $data=$this->accommodationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'accommodation',
                        "file_url" => $fileUrl                
                    ]); 
                // }    
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
        $user = JWTAuth::parseToken()->authenticate();

        $data = $this->accommodation
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','accommodation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->select('accommodation.id', 'accommodation.name', 'accommodation.location', 'accommodation.maximum_pax_per_unit', 'accommodation.deposit', 'accommodation.rent_per_month', 'accommodation.vendor_id', 'accommodation.created_by', 'accommodation.modified_by', 'accommodation.created_at', 'accommodation.updated_at', 'accommodation.deleted_at', 'accommodation.tnb_bill_account_Number','accommodation.water_bill_account_Number')
        ->find($request['id']);
        
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
        $user = JWTAuth::parseToken()->authenticate();
        $data = $this->accommodationAttachments
        ->join('accommodation', 'accommodation.id', 'accommodation_attachments.file_id')
        ->join('vendors', function($query) use($user) {
            $query->on('vendors.id','=','accommodation.vendor_id')
            ->where('vendors.company_id', $user['company_id']);
        })
        ->select('accommodation_attachments.id', 'accommodation_attachments.file_id', 'accommodation_attachments.file_name', 'accommodation_attachments.file_type', 'accommodation_attachments.file_url', 'accommodation_attachments.created_by', 'accommodation_attachments.modified_by', 'accommodation_attachments.created_at', 'accommodation_attachments.updated_at', 'accommodation_attachments.deleted_at')
        ->find($request['id']);

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



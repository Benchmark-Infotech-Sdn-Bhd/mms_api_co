<?php


namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorAttachments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class VendorServices
{
    /**
     * @var vendor
     */
    private Vendor $vendor;
    /**
     * @var vendorAttachments
     */
    private VendorAttachments $vendorAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;

    public function __construct(Vendor $vendor,VendorAttachments $vendorAttachments, Storage $storage)
    {
        $this->vendor = $vendor;
        $this->vendorAttachments = $vendorAttachments;
        $this->storage = $storage;
    }
    /**
     * @param $request
     * @return mixed | void
     */
     public function inputValidation($request)
     {
        if(!($this->vendor->validate($request->all()))){
            return $this->vendor->errors();
        }
     }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->vendor->validateUpdation($request->all()))){
            return $this->vendor->errors();
        }
    }
	 /**
     * Show the form for creating a new Vendor.
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {  
        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $vendorData = $this->vendor::create([
            'name' => $input["name"],
            'type' => $input["type"],
            'email_address' => $input["email_address"],
            'contact_number' => $input["contact_number"],            
            'person_in_charge' => $input["person_in_charge"],
            'pic_contact_number' => $input["pic_contact_number"],
            'address' => $input["address"],
            'state' => $input["state"],
            'city' => $input["city"],
            'postcode' => $input["postcode"],
            'remarks' => $input["remarks"],
            'created_by' => $user['id'],
            'company_id' => $user['company_id'] ?? 0
        ]);   
        $vendorDataId = $vendorData->id;
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/vendor/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->vendorAttachments::create([
                        "file_id" => $vendorDataId,
                        "file_name" => $fileName,
                        "file_type" => 'vendor',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
        return $vendorData;
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @param $request
     * @return LengthAwarePaginator
     */ 
    public function list($request)
    {
        return $this->vendor::with('accommodations', 'insurances', 'transportations')
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('type', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('state', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('city', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('person_in_charge', 'like', '%' . $request['search_param'] . '%');
            }
            if (isset($request['filter']) && !empty($request['filter'])) {
                $query->where('type', '=', $request['filter']);
            }
        })
        ->orderBy('vendors.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {   
        return $this->vendor::with(['vendorAttachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('company_id', $request['company_id'])->find($request['id']);
        // $accommodations = $vendors->accommodations;
        // $insurances = $vendors->insurances;
        // $transportations = $vendors->transportations;
        // $vendors = Vendor::findorfail($id);
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {  
        $input = $request->all();
        $vendors = $this->vendor::where('company_id', $input['company_id'])->find($input['id']);
        if(is_null($vendors)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $input["modified_by"] = $user['id'];
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();               
                $filePath = '/vendor/' . $fileName; 
                // if (!Storage::disk('linode')->exists($filePath)) {
                    $linode = Storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = Storage::disk('linode')->url($filePath);
                    $this->vendorAttachments::create([
                            "file_id" => $input['id'],
                            "file_name" => $fileName,
                            "file_type" => 'vendor',
                            "file_url" => $fileUrl               
                        ]); 
                // }    
            }
        }
        return  [
            "isUpdated" => $vendors->update($input),
            "message" => "Updated Successfully"
        ];
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {   
        $vendors = $this->vendor::where('company_id', $request['company_id'])->find($request['id']);

        if(is_null($vendors)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $vendors->accommodations()->delete();
        $vendors->insurances()->delete();
        $vendors->transportations()->delete();
        $vendors->vendorAttachments()->delete();
        $vendors->delete();
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
        $data = $this->vendorAttachments
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','vendor_attachments.file_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->select('vendor_attachments.id', 'vendor_attachments.file_id', 'vendor_attachments.file_name', 'vendor_attachments.file_type', 'vendor_attachments.file_url', 'vendor_attachments.created_by', 'vendor_attachments.modified_by', 'vendor_attachments.created_at', 'vendor_attachments.updated_at', 'vendor_attachments.deleted_at')
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
    /**
     * Display a listing of the insurance Vendors.
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function insuranceVendorList($request)
    {
        return $this->vendor::where('type', 'Insurance')
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->select('id', 'name', 'type')
        ->orderBy('vendors.created_at','DESC')
        ->get();
    }
    /**
     * Display a listing of the Transportation Vendors.
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function transportationVendorList($request)
    {
        return $this->vendor::where('type', 'Transportation')
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->select('id', 'name')
        ->orderBy('vendors.id','DESC')
        ->get();
    }

}
<?php


namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorAttachments;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class VendorServices
{
    public const DEFAULT_VALUE = 0;
    public const ATTACHMENT_FILE_TYPE = 'vendor';
    public const TYPE_INSURANCE = 'Insurance';
    public const TYPE_TRANSPORTATION = 'Transportation';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

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

    /**
     * VendorServices constructor.
     *
     * @param Vendor $vendor Instance of the Vendor class
     * @param VendorAttachments $vendorAttachments Instance of the VendorAttachments class
     * @param Storage $storage Instance of the Storage class
     *
     * @return void
     */
    public function __construct(
        Vendor              $vendor,
        VendorAttachments   $vendorAttachments,
        Storage             $storage
    )
    {
        $this->vendor = $vendor;
        $this->vendorAttachments = $vendorAttachments;
        $this->storage = $storage;
    }
    /**
     * validate the request data
     *
     * @param $request
     *
     * @return mixed | void
     */
     public function inputValidation($request)
     {
        if(!($this->vendor->validate($request->all()))){
            return $this->vendor->errors();
        }
     }
    /**
     * validate the request data
     *
     * @param $request
     *
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->vendor->validateUpdation($request->all()))){
            return $this->vendor->errors();
        }
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return array Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $user['company_id'];

        return $request;
    }

	 /**
     * Create a new Vendor.
     *
     * @param $request The request data containing the create vendor data
     *
     * @return mixed
     */
    public function create($request): mixed
    {
        $input = $request->all();
        $input = $this->enrichRequestWithUserDetails($input);
        $vendorData = $this->createVendor($input);
        $vendorDataId = $vendorData->id;
        $this->uploadAttachment($request, $vendorDataId);
        return $vendorData;
    }

    /**
     * create vendor.
     *
     * @param array $input
     *              name (string) vendor name
     *              type (string) vendor type
     *              email_address (string) vendor email address
     *              contact_number (int) vendor contact number
     *              person_in_charge (string) in charge name
     *              pic_contact_number (int) in charge contact number
     *              address (string) vendor address
     *              state (string) vendor state
     *              city (string) vendor city
     *              postcode (int) post code
     *              remarks (string) remarks
     *              created_by The ID of the user who created the vendor.
     *
     * @return mixed Returns the created vendor record.
     */
    private function createVendor($input): mixed
    {
        return $this->vendor::create([
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
            'created_by' => $input['created_by'],
            'company_id' => $input['company_id'] ?? self::DEFAULT_VALUE
        ]);
    }

    /**
     * Upload attachment of vendor.
     *
     * @param array $request
     *              attachment (file)
     * @param int $vendorDataId
     *
     * @return void
     */
    private function uploadAttachment($request, $vendorDataId): void
    {
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
                        "file_type" => self::ATTACHMENT_FILE_TYPE,
                        "file_url" =>  $fileUrl
                    ]);
            }
        }
    }

	 /**
     * Display a listing of the Vendors.
     *
     * @param $request  The request data containing the 'company_id', 'search_param', 'filter'
     *
     * @return mixed Returns the paginated list of vendor.
     */
    public function list($request)
    {
        return $this->vendor::with('accommodations', 'insurances', 'transportations')
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->where(function ($query) use ($request) {
            if (!empty($request['search_param'])) {
                $query->where('name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('type', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('state', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('city', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('person_in_charge', 'like', '%' . $request['search_param'] . '%');
            }
            if (!empty($request['filter'])) {
                $query->where('type', '=', $request['filter']);
            }
        })
        ->orderBy('vendors.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
	 /**
     * Show the vendor record.
     *
     * @param $request
     *
     * @return mixed Returns the vendor detail with related attachments
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
     * @param $request  The request data containing update vendor data
     *
     * @return mixed Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function update($request): mixed
    {
        $input = $request->all();
        $vendors = $this->vendor::where('company_id', $input['company_id'])->find($input['id']);
        if(is_null($vendors)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $input = $this->enrichRequestWithUserDetails($input);
        $this->uploadAttachment($request, $input['id']);
        return  [
            "isUpdated" => $vendors->update($input),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $request The request data containing company_id, id
     *
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function delete($request): mixed
    {
        $vendors = $this->vendor::where('company_id', $request['company_id'])->find($request['id']);

        if(is_null($vendors)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $vendors->accommodations()->delete();
        $vendors->insurances()->delete();
        $vendors->transportations()->delete();
        $vendors->vendorAttachments()->delete();
        $vendors->delete();
        return [
            "isDeleted" => true,
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * Delete the attachment
     *
     * @param $request The request data containing company_id and id
     *
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
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
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * Display a listing of the insurance Vendors.
     *
     * @param $request The request data containing company_id
     *
     * @return mixed Returns the list of insurance vendor.
     */
    public function insuranceVendorList($request)
    {
        return $this->vendor::where('type', self::TYPE_INSURANCE)
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->select('id', 'name', 'type')
        ->orderBy('vendors.created_at','DESC')
        ->get();
    }
    /**
     * Display a listing of the Transportation Vendors.
     *
     * @param $request  The request data containing company_id
     *
     * @return mixed Returns the list of Transportation vendor.
     */
    public function transportationVendorList($request)
    {
        return $this->vendor::where('type', self::TYPE_TRANSPORTATION)
        ->whereIn('company_id', $request['company_id'])
        ->whereNull('deleted_at')
        ->select('id', 'name')
        ->orderBy('vendors.id','DESC')
        ->get();
    }

}

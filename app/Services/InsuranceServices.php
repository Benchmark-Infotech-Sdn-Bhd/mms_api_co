<?php


namespace App\Services; 

use App\Models\Insurance;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class InsuranceServices
{
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => 'Unauthorized'];
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

    /**
     * @var Insurance
     */
    private Insurance $insurance;

    /**
     * @var Vendor
     */
    private Vendor $vendor;

    /**
     * InsuranceServices Constructor
     * 
     * @param Insurance $insurance Instance of the Insurance class
     * @param Vendor $vendor Instance of the Vendor class 
     * 
     * @return void
     */
    public function __construct(Insurance $insurance, Vendor $vendor)
    {
        $this->insurance = $insurance;
        $this->vendor = $vendor;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if(!($this->insurance->validate($request->all()))){
            return $this->insurance->errors();
        }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->insurance->validateUpdation($request->all()))){
            return $this->insurance->errors();
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

        return $request;
    }

    /**
     * create levy
     *
     * @param array $request The request data containing the create data
     * 
     * @return mixed
     */
    private function createInsurance($request)
    {
        return $this->insurance::create([
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
            'created_by' => $request["created_by"],
        ]);
    }

	 /**
     * Create the Insurance
     * 
     * @param $request The request data containing the create Insurance data
     * 
     * @return mixed Returns the created Insurance data
     */
    public function create($request): mixed
    {  
        $vendor = $this->vendor
        ->where('company_id', $request['company_id'])
        ->find($request['vendor_id']);

        if(is_null($vendor)){
            return self::ERROR_UNAUTHORIZED;
        }
        $request = $this->enrichRequestWithUserDetails($request);

        return $this->createInsurance($request);
    }
    /**
     * List the Inusurance
     * 
     * @param $request The request data containing the company_id, vendor_id,  search_param key
     * 
     * @return mixed Returns the paginated list of insurance.
     */
    public function list($request): mixed
    {
        return $this->insurance::with('vendor')
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->where(function ($query) use ($request) {
            $this->applyCondition($query,$request);
        })
        ->select('insurance.*')
        ->orderBy('insurance.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Apply condition to the query.
     *
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applyCondition($query, $request)
    {
        $vendorId = $request['vendor_id'] ?? '';
        $search = $request['search_param'] ?? '';
        if (!empty($vendorId)) {
            $query->where('vendor_id', '=', $vendorId);
        }
        if (!empty($search)) {
            $query->where('no_of_worker_from', 'like', '%' . $search . '%')
            ->orWhere('no_of_worker_to', 'like', '%' . $search . '%')
            ->orWhere('fee_per_pax', 'like', '%' . $search . '%');
        }
    }

	 /**
     * Show the insurance detail
     * 
     * @param $request The request data containing the company_id, and id key
     * 
     * @return mixed Returns the insurance data
     */
    public function show($request) : mixed
    {
        return $this->insurance::with('vendor')
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->whereIn('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.id', 'insurance.no_of_worker_from', 'insurance.no_of_worker_to', 'insurance.fee_per_pax', 'insurance.vendor_id', 'insurance.created_by', 'insurance.modified_by', 'insurance.created_at', 'insurance.updated_at', 'insurance.deleted_at')
        ->find($request['id']);
    }
	 /**
     * Update the insurance
     * 
     * @param $request $request The request data containing the updata data
     * 
     * @return mixed Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function update($request): mixed
    {           
        $data = $this->insurance
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.id', 'insurance.no_of_worker_from', 'insurance.no_of_worker_to', 'insurance.fee_per_pax', 'insurance.vendor_id', 'insurance.created_by', 'insurance.modified_by', 'insurance.created_at', 'insurance.updated_at', 'insurance.deleted_at')
        ->find($request['id']);
        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $request = $this->enrichRequestWithUserDetails($request);

        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
	 /**
     * Delethe the insurance
     * 
     * @param $request $request The request data containing the company_id, and id key
     * 
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */    
    public function delete($request) : mixed
    {     
        $data = $this->insurance
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.id', 'insurance.no_of_worker_from', 'insurance.no_of_worker_to', 'insurance.fee_per_pax', 'insurance.vendor_id', 'insurance.created_by', 'insurance.modified_by', 'insurance.created_at', 'insurance.updated_at', 'insurance.deleted_at')
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
}
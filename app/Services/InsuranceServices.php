<?php


namespace App\Services; 

use App\Models\Insurance;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class InsuranceServices
{
    /**
     * @var Insurance
     */
    private Insurance $insurance;

    /**
     * @var Vendor
     */
    private Vendor $vendor;

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
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $vendor = $this->vendor
        ->where('company_id', $request['company_id'])
        ->find($request['vendor_id']);

        if(is_null($vendor)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        $request['created_by'] = $user['id'];
        return $this->insurance::create([
            'no_of_worker_from' => $request["no_of_worker_from"],
            'no_of_worker_to' => $request["no_of_worker_to"],
            'fee_per_pax' => $request["fee_per_pax"],
            'vendor_id' => $request["vendor_id"],
            'created_by' => $request["created_by"],
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->insurance::with('vendor')
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->where(function ($query) use ($request) {
            if (isset($request['vendor_id']) && !empty($request['vendor_id'])) {
                $query->where('vendor_id', '=', $request['vendor_id']);
            }
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('vendor_id', '=', $request['vendor_id'])
                ->where('no_of_worker_from', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('no_of_worker_to', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('fee_per_pax', 'like', '%' . $request['search_param'] . '%');
            }
        })
        ->select('insurance.*')
        ->orderBy('insurance.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        return $this->insurance::with('vendor')
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->whereIn('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.*')
        ->find($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {           
        $data = $this->insurance
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.*')
        ->find($request['id']);
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
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
    public function delete($request) : mixed
    {     
        $data = $this->insurance
        ->join('vendors', function($query) use($request) {
            $query->on('vendors.id','=','insurance.vendor_id')
            ->where('vendors.company_id', $request['company_id']);
        })
        ->select('insurance.*')
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
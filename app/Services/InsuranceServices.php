<?php


namespace App\Services;

use App\Models\Insurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class InsuranceServices
{
    /**
     * @var Insurance
     */
    private Insurance $insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
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
        return $this->insurance::with('vendor')->find($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {           
        $data = $this->insurance::findorfail($request['id']);
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
        $data = $this->insurance::find($request['id']);
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
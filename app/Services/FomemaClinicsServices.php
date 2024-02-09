<?php


namespace App\Services;

use App\Models\FomemaClinics;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class FomemaClinicsServices
{
    /**
     * @var fomemaClinics
     */
    private FomemaClinics $fomemaClinics;

    public function __construct(
        FomemaClinics $fomemaClinics
    )
    {
        $this->fomemaClinics = $fomemaClinics;
    }

    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if (!($this->fomemaClinics->validate($request->all()))) {
            return $this->fomemaClinics->errors();
        }
    }

    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if (!($this->fomemaClinics->validateUpdation($request->all()))) {
            return $this->fomemaClinics->errors();
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
        return $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
            'created_by' => $request["created_by"],
            'modified_by' => $request["created_by"],
            'company_id' => $user['company_id']
        ]);
    }

	/**
     *
     * @param $request
     * @return LengthAwarePaginator
     */ 
    public function list($request)
    {
        return $this->fomemaClinics->whereIn('company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('clinic_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('state', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('city', 'like', '%' . $request['search_param'] . '%');
            }
        })
        ->orderBy('fomema_clinics.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
	
    /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        return $this->fomemaClinics->whereIn('company_id', $request['company_id'])->find($request['id']);        
    }

	/**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {     
        $data = $this->fomemaClinics::find($request['id']);
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        if ($data->company_id != $user['company_id']) {
            return [
                'InvalidUser' => true
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
        $data = $this->fomemaClinics::find($request['id']);
        $user = JWTAuth::parseToken()->authenticate();
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        if ($data->company_id != $user['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
}
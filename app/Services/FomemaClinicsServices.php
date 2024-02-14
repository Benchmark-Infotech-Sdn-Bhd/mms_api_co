<?php

namespace App\Services;

use App\Models\FomemaClinics;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class FomemaClinicsServices
{
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DATA_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";

    public const ERROR_INVALID_USER = ['InvalidUser' => true];

    /**
     * @var FomemaClinics
     */
    private FomemaClinics $fomemaClinics;
    
    /**
     * Constructor method.
     * 
     * @param FomemaClinics $fomemaClinics Instance of the FomemaClinics class.
     * 
     * @return void
     */
    public function __construct(
        FomemaClinics     $fomemaClinics
    )
    {
        $this->fomemaClinics = $fomemaClinics;
    }

    /**
     * Creates the validation rules for create a new fomema clinics.
     *
     * @return array The array containing the validation rules.
     */
    public function inputValidation($request)
    {
        if (!($this->fomemaClinics->validate($request->all()))) {
            return $this->fomemaClinics->errors();
        }
    }

    /**
     * Creates the validation rules for updating the fomema clinics.
     *
     * @return array The array containing the validation rules.
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
        $user = $this->getJwtUserAuthenticate();
        return $this->fomemaClinics::create([
            'clinic_name' => $request["clinic_name"],
            'person_in_charge' => $request["person_in_charge"],
            'pic_contact_number' => $request["pic_contact_number"],
            'address' => $request["address"],
            'state' => $request["state"],
            'city' => $request["city"],
            'postcode' => $request["postcode"],
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
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
        $user = $this->getJwtUserAuthenticate();
        $request['modified_by'] = $user['id'];
        if ($data->company_id != $user['company_id']) {
            return self::ERROR_INVALID_USER;
        }

        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
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
        $user = $this->getJwtUserAuthenticate();
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        if ($data->company_id != $user['company_id']) {
            return self::ERROR_INVALID_USER;
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * get the user of jwt authenticate.
     *
     * @return mixed Returns the user data.
     */
    private function getJwtUserAuthenticate(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }
}
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
     * Creates a new fomema clinics from the given request data.
     *
     * @param array $request The array containing fomema clinics.
     *                      The array should have the following keys:
     *                      - clinic_name: The clinic name of the fomema.
     *                      - person_in_charge: The person incharge of the fomema.
     *                      - pic_contact_number: The pic contact number of the fomema.
     *                      - address: The address of the fomema.
     *                      - state: The state of the fomema.
     *                      - city: The city of the fomema.
     *                      - postcode: The postcode of the fomema.
     *                      - created_by: The ID of the fomema who created the application.
     *                      - modified_by: (int) The updated fomema modified by.
     * 
     * @return fomema clinics The newly created fomema clinics object.
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
     * Returns a paginated list of fomema clinics based on the given search request.
     * 
     * @param array $request The search request parameters and company id.
     * @return mixed Returns the paginated list of fomema clinics.
     */
    public function list($request): mixed
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
     * Show the fomema clinics.
     * 
     * @param array $request The request data containing fomema clinics id, company id
     * @return mixed Returns the fomema clinics.
     */
    public function show($request) : mixed
    {
        return $this->fomemaClinics->whereIn('company_id', $request['company_id'])->find($request['id']);        
    }

	/**
     * Updates the fomema clinics data with the given request.
     * 
     * @param array $request The array containing country data.
     *                      The array should have the following keys:
     *                      - clinic_name: The updated clinic name.
     *                      - person_in_charge: The updated person incharge.
     *                      - pic_contact_number: The updated pic contact number.
     *                      - address: The updated address.
     *                      - state: The updated state.
     *                      - city: The updated city.
     *                      - postcode: The updated postcode. 
     *                      - modified_by: The updated country modified by.
     * @return mixed Returns an array with the following keys:
     * - "InvalidUser": if the company id is not matching with login user company id.
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
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
     * Delete the fomema clinics
     * 
     * @param array $request The array containing fomema id.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "isDeleted": A value returns false if fomemaClinics is null.
     * - "InvalidUser": if the company id is not matching with login user company id.
     * - "isDeleted" (boolean): Indicates whether the data was deleted. Always set to `false`.
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
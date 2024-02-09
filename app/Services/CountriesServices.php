<?php

namespace App\Services; 

use App\Models\Countries;
use App\Services\ValidationServices;
use App\Services\AgentServices;
use Illuminate\Support\Facades\Config;

class CountriesServices
{
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

    public const ERROR_COUNTRY_EXISTS = ['countryExistsError' => true];

    /**
     * @var countries
     */
    private Countries $countries;

    /**
     * @var validationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var agentServices
     */
    private AgentServices $agentServices;

    /**
     * Constructor method.
     * 
     * @param Countries $countries Instance of the Countries class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param AgentServices $agentServices Instance of the AgentServices class.
     */
    public function __construct(
        Countries              $countries,
        ValidationServices     $validationServices,
        AgentServices          $agentServices
    )
    {
        $this->countries = $countries;
        $this->validationServices = $validationServices;
        $this->agentServices = $agentServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $validateCountryExists = $this->validateCountryExists($request);
        if (is_array($validateCountryExists)) {
            return $validateCountryExists;
        }

        return $this->createCountry($request);
    }

    /**
     * @param $request
     * @return array
     */
    public function update($request) : array
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $country = $this->showCompanyCountry($request);
        if(is_null($country)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        
        return $this->updateCountry($country, $request);
    }

    /**
     * @param $request
     * @return array
     */
    public function delete($request) : array
    {
        $validationResult = $this->deleteValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $country = $this->showCompanyCountry($request);
        if(is_null($country)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $country->embassyAttestationFileCosting()->delete();

        return [
            "isDeleted" => $country->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        $validationResult = $this->showValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->showCompanyCountry($request);
    }

    /**
     * @param $companyId
     * @return mixed
     */
    public function dropdown($companyId) : mixed
    {
        return $this->countries->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
                ->whereIn('company_id', $companyId)
                ->select('id','country_name')
                ->orderBy('countries.created_at','DESC')
                ->get();
    }

    /**
     * @param $request
     * @return array
     */
    public function updateCostingStatus($request) : array
    {
        $validationResult = $this->updateCostingStatusValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $country = $this->countries->find($request['id']);
        if(is_null($country)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $country->costing_status = $request['costing_status'];
        return  [
            "isUpdated" => $country->save() == self::DEFAULT_INTEGER_VALUE_ONE,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->countries->whereIn('company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $this->applySearchFilter($query, $request);
        })->select('id','country_name','system_type','costing_status','status')
        ->orderBy('countries.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        $validationResult = $this->updateStatusValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $country = $this->showCompanyCountry($request);
        if(is_null($country)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        return $this->updateCountryStatus($country, $request);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,$this->countries->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function validateCountryExists($request)
    {
        $countryExists = $this->countries
            ->where([
                'country_name' => $request['country_name'],
                'company_id' => $request['company_id']
            ])->first();

        if($countryExists != NULL) {
            return self::ERROR_COUNTRY_EXISTS;
        }

        return true;
    }

    private function createCountry($request)
    {
        return $this->countries->create([
            'country_name' => $request['country_name'] ?? '',
            'system_type' => $request['system_type'] ?? '',
            'costing_status' => "Pending",
            'fee' => (float)$request['fee'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'bond' => (float)$request['bond'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'status' => self::DEFAULT_INTEGER_VALUE_ONE,
            'company_id' => $request['company_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request, $this->countries->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function showCompanyCountry($request)
    {
        return $this->countries->where('company_id', $request['company_id'])->find($request['id']);
    }

    private function updateCountry($country, $request)
    {
        return  [
            "isUpdated" => $country->update([
                'id' => $request['id'],
                'country_name' => $request['country_name'] ?? $country['country_name'],
                'system_type' => $request['system_type'] ?? $country['system_type'],
                'costing_status' => $country['costing_status'],
                'fee' => (float)$request['fee'] ?? $country['fee'],
                'bond' => (float)$request['bond'] ?? $country['bond'],
                'modified_by'   => $request['modified_by'] ?? $country['modified_by'],
                'status' => $country['status']
            ]),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function deleteValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function showValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateCostingStatusValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','costing_status' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|regex:/^[a-zA-Z ]*$/|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('country_name', 'like', "%{$request['search_param']}%");
        }
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateStatusValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function updateCountryStatus($country, $request)
    {
        $country->status = $request['status'];
        $res = $country->save();
        if($res == self::DEFAULT_INTEGER_VALUE_ONE){
            $agents = $this->agentServices
                ->updateStatusBasedOnCountries(['country_id' => $request['id'],
            'status' => $request['status']]);
        }

        return  [
            "isUpdated" => $res == self::DEFAULT_INTEGER_VALUE_ONE,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
}

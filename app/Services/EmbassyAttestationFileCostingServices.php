<?php

namespace App\Services;

use App\Models\EmbassyAttestationFileCosting;
use App\Models\Countries;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\CountriesServices;

class EmbassyAttestationFileCostingServices
{
    public const STATUS_DONE = 'Done';
    public const STATUS_PENDING = 'Pending';
    public const MESSAGE_DATA_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;

    public const ERROR_INVALID_USER = ['InvalidUser' => true];

    /**
     * @var EmbassyAttestationFileCosting
     */
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var CountriesServices
     */
    private CountriesServices $countriesServices;

    /**
     * @var Countries
     */
    private Countries $countries;

    /**
     * Constructor method.
     *
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting Instance of the EmbassyAttestationFileCosting class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param CountriesServices $countriesServices Instance of the CountriesServices class.
     * @param Countries $countries Instance of the Countries class.
     */
    public function __construct(
        EmbassyAttestationFileCosting     $embassyAttestationFileCosting,
        ValidationServices                $validationServices,
        CountriesServices                 $countriesServices,
        Countries                         $countries
    )
    {
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->validationServices = $validationServices;
        $this->countriesServices = $countriesServices;
        $this->countries = $countries;
    }

    /**
     * Creates a new filecosting from the given request data.
     *
     * @param mixed $request The mixed containing filecosting data.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A array returns Invalid if check Country is null.
     * - "countryExistsError": A array returns countryExists if country has already created
     * - "isSubmit": A object indicating if the filecosting was successfully created.
     */
    public function create($request): mixed
    {
        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $countryDetails = $this->showCompanyCountry($request);
        if (is_null($countryDetails)) {
            return self::ERROR_INVALID_USER;
        }

        $filecosting = $this->createEmbassyAttestationFileCosting($request);
        $count = $this->getEmbassyAttestationFileCostingCount($request);
        if ($count == self::DEFAULT_INTEGER_VALUE_ONE) {
            $result = $this->updateCostingStatus($request, self::STATUS_DONE);
        }

        return $filecosting;
    }

    /**
     * Updates the filecosting with the given request.
     *
     * @param array $request The array containing filecosting data.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A array returns Invalid if check Country is null.
     * - "isUpdated": Indicates whether the data was updated. Always set to `false`.
     */
    public function update($request): mixed
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $embassyAttestationFileCosting = $this->showEmbassyAttestationFileCosting($request);
        if (is_null($embassyAttestationFileCosting)) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $countryDetails = $this->showCompanyCountry(['company_id' => $request['company_id'], 'country_id' => $embassyAttestationFileCosting->country_id]);
        if (is_null($countryDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $this->updateEmbassyAttestationFileCosting($embassyAttestationFileCosting, $request);
    }

    /**
     * Delete the filecosting.
     *
     * @param array $request The request data containing filecosting id.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * -"isDeleted": Returns an mixed with 'error' as key and validation error messages as value if filecosting delete has fails. | Returns true if filecosting was deleted successfully.
     */
    public function delete($request) : mixed
    {
        $validationResult = $this->deleteValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $embassyAttestationFileCosting = $this->showEmbassyAttestationFileCosting($request);
        if (is_null($embassyAttestationFileCosting)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $countryDetails = $this->showCompanyCountry(['company_id' => $request['company_id'], 'country_id' => $embassyAttestationFileCosting->country_id]);
        if (is_null($countryDetails)) {
            return self::ERROR_INVALID_USER;
        }

        $res = [
            "isDeleted" => $embassyAttestationFileCosting->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];

        if ($res['isDeleted']) {
            $count = $this->getEmbassyAttestationFileCostingCount(['country_id' => $embassyAttestationFileCosting['country_id']]);
            if ($count == self::DEFAULT_INTEGER_VALUE_ZERO) {
                $result = $this->updateCostingStatus(['country_id' => $embassyAttestationFileCosting['country_id']], self::STATUS_PENDING);
            }
        }

        return $res;
    }

    /**
     * Show the filecosting.
     *
     * @param array $request The request data containing filecosting id
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A array returns Invalid if check Country is null.
     * - Otherwise, returns a filecosting.
     */
    public function show($request) : mixed
    {
        $validationResult = $this->showValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $embassyAttestationDetails = $this->showEmbassyAttestationFileCosting($request);
        if (is_null($embassyAttestationDetails)) {
            return [
                "error" => true,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $countryDetails = $this->showCompanyCountry(['company_id' => $request['company_id'], 'country_id' => $embassyAttestationDetails->country_id]);
        if (is_null($countryDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $embassyAttestationDetails;
    }

    /**
     * List the filecosting.
     *
     * @param array $request The request data containing country id
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A array returns Invalid if check Country is null.
     * - Otherwise, returns a paginated list of filecosting.
     */
    public function list($request) : mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $countryDetails = $this->showCompanyCountry($request);
        if (is_null($countryDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $this->embassyAttestationFileCosting->where('country_id',$request['country_id'])
            ->select('id','title','amount')
            ->orderBy('embassy_attestation_file_costing.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rules))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Show the country.
     *
     * @param array $request The request data containing country id, company id
     * @return mixed Returns the country.
     */
    private function showCompanyCountry($request)
    {
        return $this->countries->where('company_id', $request['company_id'])->find($request['country_id']);
    }

    /**
     * Creates a new embassy attestation fileCosting from the given request data.
     *
     * @param array $request The array containing fileCosting data.
     *                      The array should have the following keys:
     *                      - country_id: The country_id of the fileCosting.
     *                      - title: The title of the fileCosting.
     *                      - amount: The amount of the fileCosting.
     *                      - created_by: The created fileCosting created by.
     *                      - modified_by: (int) The updated fileCosting modified by.
     *
     * @return fileCosting The newly created fileCosting object.
     */
    private function createEmbassyAttestationFileCosting($request)
    {
        return $this->embassyAttestationFileCosting->create([
            'country_id' => (int)$request['country_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'title' => $request['title'] ?? '',
            'amount' => (float)$request['amount'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    /**
     * Returns a count of embassy attestation fileCosting based on the given country id, fileCosting id.
     *
     * @param array $request The request data containing country id, fileCosting id
     * @return array Returns a count of embassy attestation fileCosting.
     */
    private function getEmbassyAttestationFileCostingCount($request)
    {
        return $this->embassyAttestationFileCosting->whereNull('deleted_at')
            ->where('country_id','=',$request['country_id'])->count('id');
    }

    /**
     * Updates the costing status with the given request.
     *
     * @param array $request The array containing country id.
     * @param string $status The status of the countries.
     * @return array country The updated country array.
     */
    private function updateCostingStatus($request, $status)
    {
        return $this->countriesServices->updateCostingStatus([ 'id' => $request['country_id'], 'costing_status' => $status]);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,$this->embassyAttestationFileCosting->rulesForUpdation))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Show the embassy attestation fileCosting.
     *
     * @param array $request The request data containing fileCosting id.
     * @return mixed Returns the embassy attestation fileCosting.
     */
    private function showEmbassyAttestationFileCosting($request)
    {
        return $this->embassyAttestationFileCosting->find($request['id']);
    }

    /**
     * Updates the embassy attestation fileCosting data with the given request.
     *
     * @param object $embassyAttestationFileCosting The embassyAttestationFileCosting object to be updated.
     * @param array $request The array containing fileCosting data.
     *                      The array should have the following keys:
     *                      - id: The updated id.
     *                      - country_id: The updated country id.
     *                      - title: The updated title.
     *                      - amount: The updated amount.
     *                      - modified_by: The updated fileCosting modified by.
     * @return array Returns an array with the following keys:
     * - "isUpdated": Indicates whether the data was updated. Always set to `false`.
     */
    private function updateEmbassyAttestationFileCosting($embassyAttestationFileCosting, $request)
    {
        return [
            "isUpdated" => $embassyAttestationFileCosting->update([
                'id' => $request['id'],
                'country_id' => (int)$request['country_id'] ?? $embassyAttestationFileCosting['country_id'],
                'title' => $request['title'] ?? $embassyAttestationFileCosting['title'],
                'amount' => (float)$request['amount'] ?? $embassyAttestationFileCosting['amount'],
                'modified_by'   => $request['modified_by'] ?? $embassyAttestationFileCosting['modified_by']
            ]),
            "message"=> self::MESSAGE_UPDATED_SUCCESSFULLY
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
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
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
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
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
        if (!($this->validationServices->validate($request,['country_id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }
}

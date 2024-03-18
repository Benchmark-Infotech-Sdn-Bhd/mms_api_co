<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentCallingVisaDispatchServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * Constructor method for creating an instance of the class.
     *
     * @param Workers $workers The Workers instance.
     * @param WorkerVisa $workerVisa The WorkerVisa instance.
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The DirectRecruitmentOnboardingCountryServices instance.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus The DirectRecruitmentCallingVisaStatus instance.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The DirectRecruitmentOnboardingCountry instance.
     *
     * @return void
     */
    public function __construct(
        Workers                                    $workers,
        WorkerVisa                                 $workerVisa,
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices,
        DirectRecruitmentCallingVisaStatus         $directRecruitmentCallingVisaStatus,
        DirectRecruitmentOnboardingCountry         $directRecruitmentOnboardingCountry
    )
    {
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'application_id' => 'required',
            'onboarding_country_id' => 'required',
            'dispatch_method' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Update the data based on the input request.
     *
     * @param array $request The input request containing the update data.
     * @return bool|array Returns true if the update was successful, otherwise returns an array
     * containing the validation error messages.
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        if (!empty($request['workers'])) {
            $workersUpdateResult = $this->updateWorkers($request);
            if (is_array($workersUpdateResult)) {
                return $workersUpdateResult;
            }
        }

        $this->updateGeneralData($request);

        return true;
    }

    /**
     * @param array $request The request data to be validated
     * @return bool|array Returns true if the request is valid, otherwise returns an array with error messages
     */
    private function validateUpdateRequest($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        return true;
    }

    /**
     * Updates the workers based on the given request.
     *
     * @param array $request The request data containing the workers and other relevant information.
     *                      It should have the following keys:
     *                      - workers (array): An array of worker IDs to update.
     *                      - company_id (int): The ID of the company associated with the workers.
     *                      - onboarding_country_id (int): The ID of the country for onboarding.
     *                      - application_id (int): The ID of the application.
     *                      - dispatch_method (string): The dispatch method.
     *                      - dispatch_consignment_number (string|null): The dispatch consignment number.
     *                      - dispatch_acknowledgement_number (string|null): The dispatch acknowledgement number.
     *                      - modified_by (int): The ID of the user who modified the workers.
     *
     * @return bool|array If the workers were updated successfully, returns true.
     *                   If there are any validation errors or invalid users, returns an array
     *                   with the key 'InvalidUser' set to true.
     */
    private function updateWorkers($request): bool|array
    {
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();
        if ($workerCompanyCount != count($request['workers'])) {
            return ['InvalidUser' => true];
        }
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        if (is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
            return ['InvalidUser' => true];
        }
        $this->workerVisa->whereIn('worker_id', $request['workers'])
            ->update(
                ['dispatch_method' => $request['dispatch_method'],
                    'dispatch_consignment_number' => $request['dispatch_consignment_number'] ?? '',
                    'dispatch_acknowledgement_number' => $request['dispatch_acknowledgement_number'] ?? '',
                    'dispatch_submitted_on' => Carbon::now(),
                    'dispatch_status' => 'Processed',
                    'modified_by' => $request['modified_by']]);
        return true;
    }

    /**
     * Update general data.
     *
     * @param array $request The request data.
     *
     * @return void
     */
    private function updateGeneralData($request): void
    {
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);
        $onboardingStatusUpdateInfo['application_id'] = $request['application_id'];
        $onboardingStatusUpdateInfo['country_id'] = $request['onboarding_country_id'];
        $onboardingStatusUpdateInfo['onboarding_status'] = 5; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onboardingStatusUpdateInfo);
    }

    /**
     * Retrieve a list of workers based on the given request parameters.
     *
     * @param array $request An array containing the request parameters.
     *                      The 'company_id' parameter is an array that specifies the company IDs to filter the workers by.
     *                      The 'user' parameter is an array that contains user information.
     *                      The 'user_type' key specifies the type of user.
     *                      The 'reference_id' key specifies the reference ID of the user.
     *                      The 'calling_visa_reference_number' parameter is the calling visa reference number to filter the workers by.
     *                      The 'ksm_reference_number' parameter is the KSM reference number to filter the workers by.
     * @return LengthAwarePaginator The paginated list of workers.
     */
    public function workersList($request)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_visa.generated_status', 'Generated')
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * List data based on the calling visa.
     *
     * @param array $request The request data.
     *
     * @return array|Collection|LengthAwarePaginator The list of data based on the calling visa.
     */
    public function listBasedOnCallingVisa($request)
    {
        // Validate search request
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());

            if ($validator->fails()) {
                return ['error' => $validator->errors()];
            }
        }

        // Build base data query
        $data = $this->buildBaseDataQuery($request);

        // Conditionally add extra selection criteria based on the 'export' request field
        $selectFields = $this->buildSelectFields($request);
        $data = $data->select($selectFields)
            ->selectRaw($this->buildSelectRawString())
            ->groupBy($this->buildGroupByArray());

        // Order and fetch data based on the 'export' request field
        return $this->orderAndFetchData($data, $request);
    }

    /**
     * Builds the base data query for worker search
     *
     * @param array $request The request data containing company_id and other search parameters
     * @return Builder The base data query
     */
    private function buildBaseDataQuery($request)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where($this->getCustomerCondition($request))
            ->where($this->getSearchConditions($request))
            ->where('worker_visa.generated_status', 'Generated')
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where($this->buildWhereArray($request));
    }

    /**
     * Get the search conditions for the query.
     *
     * @param array $request The request data to filter the search.
     * @return Closure
     */
    private function getSearchConditions($request)
    {
        return function ($query) use ($request) {
            if (!empty($request['search'])) {
                $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                    ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
            }
        };
    }

    /**
     * Retrieves the condition to filter customers based on user type.
     *
     * @param array $request The request data containing user information.
     * @return Closure The condition to be applied to the database query.
     */
    private function getCustomerCondition($request)
    {
        return function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        };
    }

    /**
     * Builds and returns an array of conditions for filtering data.
     *
     * @param array $request The request data used to build the conditions.
     *
     * @return array The array of conditions.
     */
    private function buildWhereArray($request): array
    {
        return [
            'directrecruitment_workers.application_id' => $request['application_id'],
            'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
            'workers.cancel_status' => 0
        ];
    }

    /**
     * Build an array of selected fields based on the given request.
     *
     * @param array $request The request data.
     * @return array The array of selected fields.
     */
    private function buildSelectFields($request): array
    {
        $fields = [
            'worker_visa.ksm_reference_number',
            'worker_visa.calling_visa_reference_number',
            'worker_visa.calling_visa_valid_until',
            'worker_visa.dispatch_method',
            'worker_visa.dispatch_status',
            DB::raw('COUNT(workers.id) as workers')
        ];

        if (empty($request['export'])) {
            array_push($fields, 'worker_immigration.immigration_status', DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'));
        }

        return $fields;
    }

    /**
     * Build the select raw string for dispatch reference number.
     *
     * @return string The select raw string for dispatch reference number.
     */
    private function buildSelectRawString(): string
    {
        return "(CASE WHEN (worker_visa.dispatch_method = 'Courier') THEN worker_visa.dispatch_consignment_number WHEN (worker_visa.dispatch_method = 'ByHand') THEN worker_visa.dispatch_acknowledgement_number  ELSE '' END) AS dispatch_reference_number";
    }

    /**
     * Build an array to group by worker visa details.
     *
     * @return array The array containing group by fields.
     */
    private function buildGroupByArray(): array
    {
        $fields = [
            'worker_visa.ksm_reference_number',
            'worker_visa.calling_visa_reference_number',
            'worker_visa.calling_visa_valid_until',
            'worker_visa.dispatch_method',
            'worker_visa.dispatch_status',
            'worker_visa.dispatch_consignment_number',
            'worker_visa.dispatch_acknowledgement_number'
        ];

        if (empty($request['export'])) {
            array_push($fields, 'worker_immigration.immigration_status');
        }

        return $fields;
        
    }

    /**
     * Orders and fetches data based on the provided parameters.
     *
     * @param Builder $data The query builder instance.
     * @param array $request The request parameters.
     * @return Collection|LengthAwarePaginator  The retrieved data.
     */
    private function orderAndFetchData($data, $request)
    {
        $data->orderBy('worker_visa.calling_visa_valid_until', 'desc');

        if (!empty($request['export'])) {
            return $data->get();
        } else {
            return $data->paginate(Config::get('services.paginate_worker_row'));
        }
    }

    /**
     * Show method
     *
     * Retrieves information related to a specific reference number of a calling visa.
     *
     * @param array $request - The request data containing 'calling_visa_reference_number' and 'company_id'
     * @return array - An array containing the following information:
     *      - 'process': The details of the calling visa submission process, including reference number and submission date.
     *      - 'insurance': The details of the worker insurance, including insurance provider, policy details, submission date, and expiry date.
     *      - 'approval': The details of the calling visa approval, including if it has been generated and the valid until date.
     *      - 'immigration': The details of the worker immigration, including total fee, immigration reference number, and payment date.
     *      - 'dispatch': The valid until date of the calling visa.
     *      - 'InvalidUser': True if the company ID from the request does not match the worker details, false otherwise.
     */
    public function show($request)
    {
        $referenceNumber = $request['calling_visa_reference_number'];
        $companyID = $request['company_id'];

        $workerDetails = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['workers.company_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on'], 'workers');
        if (!in_array($workerDetails->company_id, $companyID)) {
            return ['InvalidUser' => true];
        }

        $processCallingVisa = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['calling_visa_reference_number', 'submitted_on']);
        $insurancePurchase = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'vendors.name as insurance_provider_name'], 'worker_insurance_details', 'vendors');
        $callingVisaApproval = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['calling_visa_generated', 'calling_visa_valid_until']);
        $callingVisaImmigration = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date'], 'worker_immigration');
        $callingVisaDispatch = $this->getCallingVisaDetailsByReferenceNumber($referenceNumber, ['calling_visa_valid_until']);

        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval,
            'immigration' => $callingVisaImmigration,
            'dispatch' => $callingVisaDispatch
        ];
    }

    /**
     * Get calling visa details by reference number.
     *
     * @param string $referenceNumber The reference number of the calling visa.
     * @param array $fields The fields to retrieve from the calling visa.
     * @param string|null $joinTable The table to join with.
     * @param string|null $secondJoinTable The second table to join with, if applicable.
     * @return mixed Returns the calling visa details, or null if not found.
     */
    private function getCallingVisaDetailsByReferenceNumber(string $referenceNumber, array $fields, string $joinTable = null, string $secondJoinTable = null): mixed
    {
        $query = $this->workerVisa->where('calling_visa_reference_number', $referenceNumber);

        if ($joinTable) {
            $query = $query->leftJoin($joinTable, $joinTable . '.id', 'worker_visa.worker_id');
        }

        if ($secondJoinTable) {
            $query = $query->leftJoin($secondJoinTable, $secondJoinTable . '.id', $joinTable . '.insurance_provider_id');
        }

        return $query->first($fields);
    }

    /**
     * Check if there is an application associated with the given company ID and onboarding country ID.
     *
     * @param int $companyId The ID of the company to check.
     * @param int $onboardingCountryId The ID of the onboarding country to check.
     * @return mixed|null The application data if found, null otherwise.
     */
    private function checkForApplication(int $companyId, int $onboardingCountryId): mixed
    {
        return $this->directRecruitmentOnboardingCountry
            ->join('directrecruitment_applications', function ($join) use ($companyId) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $companyId);
            })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
            ->find($onboardingCountryId);
    }
}

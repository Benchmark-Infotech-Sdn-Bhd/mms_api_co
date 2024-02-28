<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerImmigration;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentCallingVisaGenerateServices
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
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var WorkerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var WorkerImmigration
     */
    private WorkerImmigration $workerImmigration;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentCallingVisaGenerateServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerImmigration $workerImmigration
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(
        Workers                            $workers,
        WorkerVisa                         $workerVisa,
        DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus,
        WorkerInsuranceDetails             $workerInsuranceDetails,
        WorkerImmigration                  $workerImmigration,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workerInsuranceDetails = $workerInsuranceDetails;
        $this->workerImmigration = $workerImmigration;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
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
     * Updates the status and validates the workers.
     *
     * @param array $request The request data.
     *   - workers (array): An array of workers.
     *   - company_id: The ID of the company.
     *   - onboarding_country_id: The ID of the onboarding country.
     *   - application_id: The ID of the application.
     *   - modified_by: The user who modified the data.
     *
     * @return bool|array Returns true if the status is updated; otherwise, returns an array with 'InvalidUser' key set to true.
     */

    public function generatedStatusUpdate(array $request): bool|array
    {
        $workers = $request['workers'];
        $companyId = $request['company_id'];
        $onboardingCountryId = $request['onboarding_country_id'];
        $applicationId = $request['application_id'];
        $modifiedBy = $request['modified_by'];

        if (!empty($workers)) {
            if (!$this->validateAndUpdateWorkerVisas($workers, $companyId, $onboardingCountryId, $applicationId, $modifiedBy)) {
                return ['InvalidUser' => true];
            }
        }

        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId,
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);

        return true;
    }

    /**
     * Validates and updates worker visas.
     *
     * @param array $workers : The array of worker IDs.
     * @param int $companyId : The ID of the company.
     * @param int $onboardingCountryId : The ID of the onboarding country.
     * @param int $applicationId : The ID of the application.
     * @param string $modifiedBy : The user who modified the worker visas.
     *
     * @return bool : Returns true if the validation and update is successful, false otherwise.
     */
    private function validateAndUpdateWorkerVisas(array $workers, int $companyId, int $onboardingCountryId, int $applicationId, string $modifiedBy): bool
    {
        $workerCompanyCount = $this->workers->whereIn('id', $workers)
            ->where('company_id', $companyId)
            ->count();
        if ($workerCompanyCount != count($workers)) {
            return false;
        }

        if (
            is_null($this->checkForApplication($companyId, $onboardingCountryId))
            || $this->checkForApplication($companyId, $onboardingCountryId)->application_id != $applicationId
        ) {
            return false;
        }

        $this->workerVisa->whereIn('worker_id', $workers)->update(['generated_status' => 'Generated', 'modified_by' => $modifiedBy]);

        return true;
    }

    /**
     * Retrieves a list of workers based on the provided request.
     *
     * @param array $request The request data containing search criteria and company IDs
     * @return LengthAwarePaginator The paginated list of workers
     */
    public function workersList($request): mixed
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
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * List the data based on the calling Visa.
     *
     * @param array $request The request data.
     *                      Example:
     *                      [
     *                          'search' => 'search keyword',
     *                          'filter' => 'filter value',
     *                          ...
     *                      ]
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection The fetched data based on the calling Visa.
     *              otherwise return array contains validation errors
     */
    public function listBasedOnCallingVisa($request)
    {
        $validator = $this->validateSearch($request);
            if(!empty($validator['error'])) {
                return [
                    'error' => $validator['error']
                ];
            }
        $query = $this->buildWorkerDataQuery($request);
        return $this->fetchData($request, $query);
    }

    /**
     * Validate the search request.
     *
     * @param array $request The search request data.
     *
     * @return void | array If validation fails, returns an associative array with an "error" key containing the validation errors.
     *                    If validation passes, returns null.
     */
    private function validateSearch($request)
    {
        if (empty($request['search'])) {
            return;
        }

        $validator = Validator::make($request, $this->searchValidation());

        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
    }

    /**
     * Build the query for fetching worker data based on the given request parameters.
     *
     * @param array $request The request parameters containing company_id, application_id, and onboarding_country_id.
     * @return Builder The query object for fetching worker data.
     */
    private function buildWorkerDataQuery($request)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', '=', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', '=', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', '=', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where('worker_visa.generated_status', '!=', 'Generated')
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
                $this->applyUserTypeFilter($query, $request);
                $this->applyAgentFilter($query, $request);
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0,
                'worker_immigration.immigration_status' => 'Paid'
            ]);
    }

    /**
     * Apply search filter to the query.
     *
     * @param object $query The query object to apply the filter on.
     * @param array $request The request data containing the search keyword.
     * @return void
     */
    private function applySearchFilter($query, $request): void
    {
        if (empty($request['search'])) {
            return;
        }
        $query->where('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
            ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%');
    }

    /**
     * Applies the user type filter to a query.
     *
     * @param mixed $query The query object to apply the filter to.
     * @param array $request The request data containing the user information.
     *  The request array should have the following structure:
     *  [
     *      'user' => [
     *          'user_type' => 'Customer', // The user type to filter on.
     *          'reference_id' => '123' // The reference ID of the user.
     *      ]
     *  ]
     *
     * @return void
     */
    private function applyUserTypeFilter($query, $request): void
    {
        if ($request['user']['user_type'] !== 'Customer') {
            return;
        }
        $query->where('workers.crm_prospect_id', $request['user']['reference_id']);
    }

    /**
     * Apply agent filter to the given query.
     *
     * @param Builder $query The database query builder.
     * @param array $request The request data.
     *
     * @return void
     */
    private function applyAgentFilter($query, $request): void
    {
        if (empty($request['agent_id'])) {
            return;
        }
        $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
    }

    /**
     * Fetch data based on given request and query.
     *
     * @param array $request The request parameters.
     * @param Builder $query The query instance.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Collection The collection of fetched data.
     */
    private function fetchData($request, $query)
    {
        $selectFields = $this->getSelectFields($request);
        $groupByFields = $this->getGroupByFields($request);

        $dataQuery = $query->select($selectFields)
            ->groupBy($groupByFields)
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc');

        if (empty($request['export'])) {
            return $dataQuery->paginate(Config::get('services.paginate_worker_row'));
        } else {
            return $dataQuery->get();
        }
    }

    /**
     * Retrieves the select fields for a query based on the given request.
     *
     * @param array $request The request parameters.
     *
     * @return array The select fields for the query.
     */
    private function getSelectFields($request): array
    {
        $commonFields = [
            'worker_visa.ksm_reference_number',
            'worker_visa.calling_visa_reference_number',
            'worker_visa.generated_status',
            DB::raw('COUNT(workers.id) as workers'),
            'worker_immigration.immigration_reference_number'
        ];

        if (!empty($request['export'])) {
            $exportFields = [
                'worker_visa.calling_visa_valid_until',
                'worker_visa.calling_visa_generated'
            ];
            $selectFields = array_merge($commonFields, $exportFields);
        } else {
            $nonExportFields = [
                'directrecruitment_workers.application_id',
                'directrecruitment_workers.onboarding_country_id',
                'directrecruitment_workers.agent_id',
                'worker_visa.calling_visa_valid_until',
                'worker_visa.calling_visa_generated',
                DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id')
            ];
            $selectFields = array_merge($commonFields, $nonExportFields);
        }

        return $selectFields;
    }

    /**
     * Retrieves the group by fields based on the given request.
     *
     * @param array $request The request data.
     * @return array The group by fields.
     */
    private function getGroupByFields($request): array
    {
        $commonFields = [
            'worker_visa.ksm_reference_number',
            'worker_visa.calling_visa_reference_number',
            'worker_visa.generated_status',
            'worker_immigration.immigration_reference_number'
        ];

        if (!empty($request['export'])) {
            $exportFields = [
                'worker_visa.calling_visa_valid_until',
                'worker_visa.calling_visa_generated'
            ];
            $groupByFields = array_merge($commonFields, $exportFields);
        } else {
            $nonExportFields = [
                'directrecruitment_workers.application_id',
                'directrecruitment_workers.onboarding_country_id',
                'directrecruitment_workers.agent_id',
                'worker_visa.calling_visa_valid_until',
                'worker_visa.calling_visa_generated'
            ];
            $groupByFields = array_merge($commonFields, $nonExportFields);
        }

        return $groupByFields;
    }

    /**
     * Show method
     *
     * Retrieves various information related to a specific request.
     *
     * @param array $request The request data
     * @return array The retrieved information
     */
    public function show($request)
    {
        $visaRefNo = $request['calling_visa_reference_number'];

        $workerCheck = $this->getWorkerVisa($visaRefNo, ['workers.company_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on']);
        if (!in_array($workerCheck->company_id, $request['company_id'])) {
            return [
                'InvalidUser' => true
            ];
        }

        $processCallingVisa = $this->getWorkerVisa($visaRefNo, ['calling_visa_reference_number', 'submitted_on']);

        $insurancePurchase = $this->workerInsuranceDetails->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
            ->leftJoin('vendors', 'vendors.id', 'worker_insurance_details.insurance_provider_id')
            ->where('worker_visa.calling_visa_reference_number', $visaRefNo)
            ->first(['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'vendors.name as insurance_provider_name']);

        $callingVisaApproval = $this->getWorkerVisa($visaRefNo, ['calling_visa_generated', 'calling_visa_valid_until']);

        $callingVisaImmigration = $this->workerImmigration->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_immigration.worker_id')
            ->where('worker_visa.calling_visa_reference_number', $visaRefNo)
            ->first(['worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date']);

        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval,
            'immigration' => $callingVisaImmigration
        ];
    }

    /**
     * Get worker visa details.
     *
     * @param string $visaRefNo The reference number of the worker visa.
     * @param array $attributes The attributes to retrieve from the worker visa.
     *
     * @return mixed|null The worker visa details or null if not found.
     */
    private function getWorkerVisa(string $visaRefNo, array $attributes)
    {
        return $this->workerVisa->leftJoin('workers', 'workers.id', 'worker_visa.worker_id')
            ->where('worker_visa.calling_visa_reference_number', $visaRefNo)
            ->first($attributes);
    }

    /**
     * Check if there is an application associated with the given company and onboarding country.
     *
     * @param int $companyId The ID of the company to check for application.
     * @param int $onboardingCountryId The ID of the onboarding country to check for application.
     *
     * @return mixed|null The matching onboarding country record if found, otherwise null.
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

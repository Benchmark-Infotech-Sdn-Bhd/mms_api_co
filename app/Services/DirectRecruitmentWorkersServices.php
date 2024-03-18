<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerStatus;
use App\Models\DirectrecruitmentWorkers;
use App\Models\KinRelationship;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\WorkerBulkUpload;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\Agent;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkerImport;

class DirectRecruitmentWorkersServices
{
    private Workers $workers;
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    private WorkerStatus $workerStatus;
    private KinRelationship $kinRelationship;
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    private WorkerBulkUpload $workerBulkUpload;
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    private ValidationServices $validationServices;
    private AuthServices $authServices;

    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    private WorkersServices $workersServices;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var Agent
     */
    private Agent $agent;
    private OnboardingCountriesKSMReferenceNumber $OnboardingCountriesKSMReferenceNumber;

    /**
     * DirectRecruitmentWorkersServices constructor.
     * @param Workers $workers
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerStatus $workerStatus
     * @param KinRelationship $kinRelationship
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent
     * @param WorkerBulkUpload $workerBulkUpload
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices ;
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers ;
     * @param WorkersServices $workersServices ;
     * @param DirectrecruitmentApplications $directrecruitmentApplications ;
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     * @param OnboardingCountriesKSMReferenceNumber $OnboardingCountriesKSMReferenceNumber
     * @param Agent $agent
     */
    public function __construct(
        Workers                                    $workers,
        DirectRecruitmentCallingVisaStatus         $directRecruitmentCallingVisaStatus,
        WorkerStatus                               $workerStatus,
        KinRelationship                            $kinRelationship,
        DirectRecruitmentOnboardingAgent           $directRecruitmentOnboardingAgent,
        WorkerBulkUpload                           $workerBulkUpload,
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices,
        ValidationServices                         $validationServices,
        AuthServices                               $authServices,
        DirectrecruitmentWorkers                   $directrecruitmentWorkers,
        WorkersServices                            $workersServices,
        DirectrecruitmentApplications              $directrecruitmentApplications,
        DirectRecruitmentOnboardingCountry         $directRecruitmentOnboardingCountry,
        OnboardingCountriesKSMReferenceNumber      $OnboardingCountriesKSMReferenceNumber,
        Agent                                      $agent
    )
    {
        $this->workers = $workers;
        $this->workerStatus = $workerStatus;
        $this->kinRelationship = $kinRelationship;
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->workerBulkUpload = $workerBulkUpload;
        $this->validationServices = $validationServices;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->authServices = $authServices;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
        $this->workersServices = $workersServices;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->OnboardingCountriesKSMReferenceNumber = $OnboardingCountriesKSMReferenceNumber;
        $this->agent = $agent;
    }

    /**
     * @return array
     */
    public function createWorkerValidation(): array
    {
        return
            [
                'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
                'agent_id' => 'required|regex:/^[0-9]+$/',
                'application_id' => 'required|regex:/^[0-9]+$/'
            ];
    }

    /**
     * @return array
     */
    public function updateWorkerValidation(): array
    {
        return
            [
                'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
                'agent_id' => 'required|regex:/^[0-9]+$/',
                'application_id' => 'required|regex:/^[0-9]+$/'
            ];
    }

    /**
     * Creates a worker based on the given request parameters.
     *
     * @param mixed $request The request parameters for creating a worker.
     * @return array|false|true[] Returns an array with validation errors if the request fails validation,
     *               returns an array with the 'InvalidUser' key set to true if the user is not valid,
     *               returns an array with the 'ksmError' key set to true if the KSM reference number is not valid,
     *               returns an array with the 'workerCountError' key set to true if the worker count is greater than or equal to the onboarding country quota,
     *               returns an array with the 'ksmCountError' key set to true if the worker count for the KSM reference number is greater than or equal to the approved count,
     *               returns an array with the 'agentQuotaError' key set to true if the agent worker count is greater than or equal to the agent quota,
     *               returns an array with the 'validate' key set to an array of validation errors if data creation fails validation,
     *               returns true if the worker is created successfully, returns false otherwise.
     * @throws Exception
     */
    public function create($request): array|bool
    {
        $params = $this->getParamsFromRequest($request);

        $validator = Validator::make($request->toArray(), $this->createWorkerValidation());
        if ($validator->fails()) {
            return [
                'validate' => $validator->errors()
            ];
        }
        $isValidUser = $this->isValidUser($params);
        if (!$isValidUser) {
            return [
                'InvalidUser' => true
            ];
        }

        if (!$this->validateKsmReferenceNumber($params, $request['ksm_reference_number'])) {
            return [
                'ksmError' => true
            ];
        }

        $approvedCount = $this->OnboardingCountriesKSMReferenceNumber->where('application_id', $request['application_id'])
            ->where('onboarding_country_id', $request['onboarding_country_id'])
            ->where('ksm_reference_number', $request['ksm_reference_number'])
            ->sum('quota');

        $workerCount = $this->getWorkerCount($request);

        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry
            ->findOrFail($request['onboarding_country_id']);

        if ($workerCount >= $onboardingCountryDetails->quota) {
            return [
                'workerCountError' => true
            ];
        }

        $StatusDirectRecruitmentWorker = Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS');
        $workerCountForKsmReference = $this->getWorkerCountForKsmReference($request, $StatusDirectRecruitmentWorker);

        if (isset($approvedCount) && ($workerCountForKsmReference >= $approvedCount)) {
            return [
                'ksmCountError' => true
            ];
        }

        $agentDetails = $this->directRecruitmentOnboardingAgent->findOrFail($request['agent_id']);
        $agentWorkerCount = $this->getAgentWorkerCount($request);

        if ($agentWorkerCount >= $agentDetails->quota) {
            return ['agentQuotaError' => true];
        }

        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $request['crm_prospect_id'] = $applicationDetails->crm_prospect_id;
        $data = $this->workersServices->create($request);

        if (isset($data['validate'])) {
            return [
                'validate' => $data['validate']
            ];
        } else if (isset($data['id'])) {

            $this->workers->where('id', $data['id'])
                ->update([
                    'module_type' => Config::get('services.WORKER_MODULE_TYPE')[0]
                ]);

            $commonKeysData = [
                "application_id" => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
                'created_by' => $params['created_by'] ?? 0,
                'modified_by' => $params['created_by'] ?? 0
            ];

            $this->directrecruitmentWorkers->create(array_merge(["worker_id" => $data['id'], 'agent_id' => $request['agent_id']], $commonKeysData));

            $checkCallingVisa = $this->directRecruitmentCallingVisaStatus
                ->where('application_id', $request['application_id'])
                ->where('onboarding_country_id', $request['onboarding_country_id'])
                ->where('agent_id', $request['agent_id'])->get()->toArray();

            if ($this->isEmptyArray($checkCallingVisa)) {
                $this->directRecruitmentCallingVisaStatus->create(
                    array_merge(['agent_id' => $request['agent_id'], 'item' => 'Calling Visa Status', 'updated_on' => Carbon::now(), 'status' => 1], $commonKeysData)
                );
            }

            $checkWorkerStatus = $this->workerStatus
                ->where('application_id', $request['application_id'])
                ->where('onboarding_country_id', $request['onboarding_country_id'])
                ->get()->toArray();

            if (!$this->isEmptyArray($checkWorkerStatus)) {
                $this->workerStatus->where([
                    'application_id' => $request['application_id'],
                    'onboarding_country_id' => $request['onboarding_country_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);
            } else {
                $this->workerStatus->create(
                    array_merge(['item' => 'Worker Biodata', 'updated_on' => Carbon::now(), 'status' => 1], $commonKeysData)
                );
            }

            $onBoardingStatus = [
                'onboarding_status' => 4,
                'application_id' => $request['application_id'],
                'country_id' => $request['onboarding_country_id']
            ]; //Agent Added
            $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

            return true;

        }
        return false;
    }

    /**
     * Check if an array is empty.
     *
     * @param array $data The array to be checked.
     * @return bool Returns true if the array is empty, false otherwise.
     */
    private function isEmptyArray($data): bool
    {
        return isset($data) && count($data) == 0;
    }

    /**
     * Retrieves parameters from the request object.
     *
     * @param $request - The request object containing the parameters.
     * @return mixed The parameters retrieved from the request.
     */
    private function getParamsFromRequest($request): mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];
        return $params;
    }

    /**
     * Determines if the user is valid.
     *
     * @param array $requestParams The parameters from the request.
     *     - string $requestParams['company_id'] The company ID.
     *     - string $requestParams['onboarding_country_id'] The onboarding country ID.
     *     - string $requestParams['application_id'] The application ID.
     * @return bool Returns true if the user is valid, otherwise false.
     * @throws Exception
     */
    private function isValidUser($requestParams): bool
    {
        $onboardingCountryCheck = $this->checkForApplication($requestParams['company_id'], $requestParams['onboarding_country_id']);
        return !is_null($onboardingCountryCheck) && $onboardingCountryCheck->application_id == $requestParams['application_id'];
    }

    /**
     * Validates if the given KSM reference number exists in the KSM reference number list.
     *
     * @param array $params The parameters for retrieving the KSM reference number list.
     * @param string $ksmReferenceNumber The KSM reference number to be validated.
     * @return bool Returns true if the KSM reference number exists, false otherwise.
     */
    private function validateKsmReferenceNumber($params, $ksmReferenceNumber): bool
    {
        $ksmReferenceNumbersResult = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);

        foreach ($ksmReferenceNumbersResult as $record) {
            if ($record['ksm_reference_number'] === $ksmReferenceNumber) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the count of direct recruitment workers based on the given request parameters.
     *
     * @param mixed $request The request parameters.
     * @return int The count of direct recruitment workers.
     */
    private function getWorkerCount($request)
    {
        return $this->directrecruitmentWorkers
            ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
            ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
            ->where($this->getWorkersQueryParams($request))
            ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
            ->count('directrecruitment_workers.worker_id');
    }

    /**
     * Get the query parameters for retrieving workers.
     *
     * @param array $request The request data.
     * @return array The query parameters for retrieving workers.
     */
    private function getWorkersQueryParams($request)
    {
        return [
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0],
        ];
    }

    /**
     * Get the count of workers for a specific KSM reference number and status of direct recruitment workers.
     *
     * @param array $request The request data containing the application ID and KSM reference number.
     * @param array $statusDirectRecruitmentWorker The status of direct recruitment workers.
     * @return int The count of workers matching the criteria.
     */
    private function getWorkerCountForKsmReference($request, $statusDirectRecruitmentWorker)
    {
        return $this->directrecruitmentWorkers
            ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
            ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
            ->where([
                ['directrecruitment_workers.application_id', $request['application_id']],
                ['workers.cancel_status', 0],
                ['worker_visa.ksm_reference_number', $request['ksm_reference_number']],
            ])
            ->whereIn('workers.directrecruitment_status', $statusDirectRecruitmentWorker)
            ->count('directrecruitment_workers.worker_id');
    }

    /**
     * Get the number of worker counts for a specific agent.
     *
     * @param array $request The request data containing the following keys:
     *  - application_id: The ID of the application.
     *  - onboarding_country_id: The ID of the onboarding country.
     *  - agent_id: The ID of the agent.
     *
     * @return int The number of worker counts.
     */
    private function getAgentWorkerCount($request)
    {
        return $this->directrecruitmentWorkers
            ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
            ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
            ->where([
                ['directrecruitment_workers.application_id', $request['application_id']],
                ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
                ['directrecruitment_workers.agent_id', $request['agent_id']],
                ['workers.cancel_status', 0]
            ])
            ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
            ->count('directrecruitment_workers.worker_id');
    }

    /**
     * Retrieve a list of workers based on the given request
     *
     * @param $request - The request parameters
     *      - search_param: The parameter to search for (optional)
     *      - application_id: The application ID (required)
     *      - onboarding_country_id: The onboarding country ID (required)
     *      - stage_filter: The filter to apply (optional)
     *      - agent_id: The agent ID (optional)
     *      - status: The status to filter by (optional)
     * @return mixed The list of workers
     */
    public function list($request)
    {
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $data = $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
            ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
            ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
            ->where('directrecruitment_workers.application_id', $request['application_id'])
            ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($user) {
                if ($user['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['stage_filter']) && $request['stage_filter'] == 'calling_visa') {
                    $query->where('worker_visa.status', 'Processed');
                }

                if (isset($request['stage_filter']) && $request['stage_filter'] == 'arrival') {
                    $query->where('worker_arrival.arrival_status', 'Not Arrived');
                }

                if (isset($request['stage_filter']) && $request['stage_filter'] == 'post_arrival') {
                    $query->where('worker_arrival.arrival_status', 'Arrived');
                }

                if (isset($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }

                if (!empty($request['search_param'])) {
                    $query->where('workers.name', 'like', "%{$request['search_param']}%")
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search_param'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search_param'] . '%');
                }

            })->select('workers.id', 'workers.name', 'directrecruitment_workers.agent_id', 'workers.date_of_birth', 'workers.gender', 'workers.passport_number', 'workers.passport_valid_until', 'worker_visa.ksm_reference_number', 'worker_bio_medical.bio_medical_valid_until', 'worker_visa.approval_status as visa_status', 'workers.cancel_status as cancellation_status', 'workers.created_at', 'workers.directrecruitment_status', 'workers.replace_worker_id')
            ->selectRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status WHEN (workers.directrecruitment_status = 'Expired') THEN workers.directrecruitment_status WHEN (workers.cancel_status = 1) THEN 'Cancelled'
        WHEN (workers.cancel_status = 2) THEN 'Cancelled'
		ELSE worker_visa.approval_status END) as status");
        if (!empty($request['status'])) {
            $data = $data->whereRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status
            WHEN (workers.cancel_status = 1) THEN 'Cancelled'
            WHEN (workers.cancel_status = 2) THEN 'Cancelled'
            ELSE worker_visa.approval_status END) = '" . $request['status'] . "'");
        }
        return $data->distinct()
            ->orderBy('workers.created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Exports data based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - search_param: The search parameter. (optional)
     *
     * @return array|\Illuminate\Support\Collection Returns the exported data.
     *               If the 'search_param' validation fails, an array with validation errors is returned.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the export process.
     */
    public function export($request)
    {
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $query = $this->buildQuery($request, $user);
        return $query->distinct()->orderBy('workers.created_at', 'DESC')->get();
    }

    /**
     * Builds and returns the query to fetch worker data based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - application_id: The application ID. (required)
     *                      - onboarding_country_id: The onboarding country ID. (required)
     *                      - company_id: The company ID. (required)
     *
     * @param array $user The authenticated user data.
     *
     * @return \App\Models\Workers The built query to fetch worker data.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the query building process.
     */
    private function buildQuery($request, $user)
    {
        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->join('worker_kin', 'workers.id', '=', 'worker_kin.worker_id')
            ->join('kin_relationship', 'kin_relationship.id', '=', 'worker_kin.kin_relationship_id')
            ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
            ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
            ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
            ->where('directrecruitment_workers.application_id', $request['application_id'])
            ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($user) {
                if ($user['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
                }
            })
            ->where(function ($query) use ($request) {
                // Apply all filtering related to `stage_filter` and `status` here
                // So, the code becomes cleaner and easier to understand what's going on
                $this->applyFiltering($query, $request);
            })
            ->select('workers.id', 'workers.name', 'workers.date_of_birth', 'workers.gender', 'workers.passport_number', 'workers.passport_valid_until', 'workers.address', 'workers.state', 'worker_kin.kin_name', 'kin_relationship.name as kin_relationship_name', 'worker_kin.kin_contact_number', 'worker_visa.ksm_reference_number', 'worker_bio_medical.bio_medical_reference_number', 'worker_bio_medical.bio_medical_valid_until', 'workers.created_at');
    }

    /**
     * Apply filtering to the given query based on the provided request parameters.
     *
     * @param Builder $query The query to apply filtering on.
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - stage_filter: The stage filter. (optional)
     *                      - search_param: The search parameter. (optional)
     *                      - status: The status filter. (optional)
     *
     * @throws Exception If an error occurs during the filtering process.
     */
    private function applyFiltering($query, $request)
    {
        // All conditionals are placed here to improve code clarity
        if (isset($request['stage_filter'])) {
            if ($request['stage_filter'] == 'calling_visa') {
                $query->where('worker_visa.status', 'Processed');
            }
            if ($request['stage_filter'] == 'arrival') {
                $query->where('worker_arrival.arrival_status', 'Not Arrived');
            }
            if ($request['stage_filter'] == 'post_arrival') {
                $query->where('worker_arrival.arrival_status', 'Arrived');
            }
        }

        if (!empty($request['search_param'])) {
            $query->where('workers.name', 'like', "%{$request['search_param']}%")
                ->orWhere('workers.passport_number', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search_param'] . '%');
        }

        if (isset($request['status'])) {
            $query->where('workers.status', $request['status']);
        }
    }

    /**
     * Retrieves a dropdown list of workers based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are expected:
     *                      - application_id: The application ID.
     *                      - onboarding_country_id: The onboarding country ID.
     *                      - agent_id: The agent ID.
     *                      - worker_id: The worker ID. (optional)
     *
     * @return mixed Returns a collection of workers.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     */
    public function dropdown($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
            ->where('workers.status', 1)
            ->where('directrecruitment_workers.application_id', $request['application_id'])
            ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
            ->where('directrecruitment_workers.agent_id', $request['agent_id'])
            ->where('worker_visa.status', 'Pending')
            ->whereIn('workers.company_id', $request['company_id'])
            ->whereNull('replaced_for')
            ->where(function ($query) use ($user) {
                if ($user['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
                }
            })
            ->where(function ($query) use ($request) {
                if ($request['worker_id']) {
                    $query->where('workers.id', '!=', $request['worker_id']);
                }
            })
            ->select('workers.id', 'workers.name')
            ->orderBy('workers.created_at', 'DESC')->get();
    }

    /**
     * Update a worker based on the provided request parameters.
     *
     * @param $request - The request object containing the parameters.
     *                                        Available parameters:
     *                                        - id (int): The ID of the worker to update.
     *                                        - onboarding_country_id (int, optional): The ID of the onboarding country.
     *                                        - application_id (int, optional): The ID of the application.
     *                                        - agent_id (int, optional): The ID of the agent.
     *                                        - ksm_reference_number (string, optional): The KSM reference number.
     *
     * @return array|true|true[] Returns true if the update is successful.
     *               If the request parameters fail validation, an array with validation errors is returned.
     *               If the worker does not belong to the user's company, an array with the 'InvalidUser' flag is returned.
     *               If the onboarding country or application ID does not match the worker's, an array with the 'InvalidUser' flag is returned.
     *               If the KSM reference number is not found in the list of KSM reference numbers, an array with the 'ksmError' flag is returned.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     * @throws Exception If an error occurs during the update process.
     */
    public function update($request)
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $validator = Validator::make($request->toArray(), $this->updateWorkerValidation());
        if ($validator->fails()) {
            return [
                'validate' => $validator->errors()
            ];
        }

        $workerCheck = $this->directrecruitmentWorkers
            ->leftJoin('workers', 'workers.id', 'directrecruitment_workers.worker_id')
            ->where('directrecruitment_workers.worker_id', $request['id'])
            ->select('workers.company_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.application_id')
            ->first();

        if ($params['company_id'] != $workerCheck->company_id) {
            return [
                'InvalidUser' => true
            ];
        } else if ($workerCheck->onboarding_country_id != $params['onboarding_country_id']) {
            return [
                'InvalidUser' => true
            ];
        } else if ($workerCheck->application_id != $params['application_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        $ksmReferenceNumbersResult = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);

        $ksmReferenceNumbers = array();
        foreach ($ksmReferenceNumbersResult as $key => $ksmReferenceNumber) {
            $ksmReferenceNumbers[$key] = $ksmReferenceNumber['ksm_reference_number'];
        }

        if (!empty($ksmReferenceNumbers)) {
            if (!in_array($request['ksm_reference_number'], $ksmReferenceNumbers)) {
                return [
                    'ksmError' => true
                ];
            }
        }

        $data = $this->workersServices->update($request);

        if (isset($data['validate'])) {
            return [
                'validate' => $data['validate']
            ];
        }

        $directrecruitmentWorkers = $this->directrecruitmentWorkers->where([
            ['application_id', $request['application_id']],
            ['worker_id', $request['id']]
        ])->first(['id', 'application_id', 'onboarding_country_id', 'agent_id', 'worker_id', 'created_by', 'modified_by', 'created_at', 'updated_at']);

        if (!empty($directrecruitmentWorkers)) {
            $directrecruitmentWorkers->update([
                'onboarding_country_id' => $request['onboarding_country_id'] ?? $directrecruitmentWorkers->onboarding_country_id,
                'agent_id' => $request['agent_id'] ?? $directrecruitmentWorkers->agent_id,
                'modified_by' => $params['modified_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }

        $this->workerStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['modified_by']]);

        return true;
    }

    /**
     * @return mixed
     */
    public function kinRelationship(): mixed
    {
        return $this->kinRelationship->where('status', 1)
            ->select('id', 'name')
            ->orderBy('id', 'ASC')->get();
    }

    /**
     * Retrieves the onboarding agent data based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - company_id: The ID of the company.
     *                      - application_id: The ID of the application.
     *                      - onboarding_country_id: The ID of the onboarding country.
     *
     * @return Collection Returns a collection of onboarding agent data.
     *
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function onboardingAgent($request)
    {
        return $this->agent
            ->leftJoin('directrecruitment_onboarding_agent', 'directrecruitment_onboarding_agent.agent_id', 'agent.id')
            ->leftJoin('onboarding_attestation', 'onboarding_attestation.onboarding_agent_id', 'directrecruitment_onboarding_agent.id')
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('onboarding_attestation.application_id', $request['application_id'])
            ->where('onboarding_attestation.onboarding_country_id', $request['onboarding_country_id'])
            ->where('onboarding_attestation.status', 'Collected')
            ->select('onboarding_attestation.onboarding_agent_id as id', 'agent.agent_name')
            ->distinct('agent.id', 'agent.agent_name')
            ->get();
    }

    /**
     * Replaces a worker with another worker.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - id: The ID of the worker to be replaced.
     *                      - replace_worker_id: The ID of the worker to be replaced with.
     *
     * @return array Returns an array with the following keys:
     *               - isUpdated: A boolean indicating whether the worker was updated successfully.
     *               - message: A string message indicating the status of the update.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the update process.
     */
    public function replaceWorker($request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $worker = $this->workers
            ->where('id', $request['id'])
            ->where('company_id', $user['company_id'])
            ->update([
                'replace_worker_id' => $request['replace_worker_id'],
                'replace_by' => $user['id'],
                'replace_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        if ($worker) {
            $this->updateWorkerReplaceDetails($request);
        }

        return [
            "isUpdated" => $worker,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Updates the replacement details for a worker.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - replace_worker_id: The ID of the worker to be replaced.
     *                      - id: The ID of the worker who will replace the other worker.
     *
     * @throws Exception If an error occurs during the update process.
     */
    private function updateWorkerReplaceDetails($request)
    {
        $this->workers
            ->where('id', $request['replace_worker_id'])
            ->update([
                'replaced_for' => $request['id']
            ]);
    }

    /**
     * Retrieves a single worker based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - id: The ID of the worker.
     *
     * @return mixed Returns the worker data.
     *               If the validation fails, an array with validation errors is returned.
     *
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function show($request)
    {
        if (!($this->validationServices->validate($request, ['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->workers->with('directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails', 'workerFomemaAttachments')
            ->whereIn('company_id', $request['company_id'])
            ->where('id', $request['id'])
            ->first();
    }

    /**
     * Imports data from a file based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - onboarding_country_id: The ID of the onboarding country. (required)
     *                      - agent_id: The ID of the agent. (optional)
     *                      - application_id: The ID of the application. (required)
     *
     * @param $file - The file to import.
     *
     * @return bool|array Returns true indicating a successful import.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the import process.
     */
    public function import($request, $file): array|bool
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $applicationCheck = $this->checkForApplication($params['company_id'], $request['onboarding_country_id']);

        if (is_null($applicationCheck)) {
            return [
                'InvalidUser' => true
            ];
        } else if ($applicationCheck->application_id != $params['application_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        $workerBulkUpload = $this->workerBulkUpload->create([
                'onboarding_country_id' => $request['onboarding_country_id'] ?? '',
                'agent_id' => $request['agent_id'] ?? NULL,
                'application_id' => $request['application_id'] ?? '',
                'name' => 'Worker Bulk Upload',
                'type' => 'Worker bulk upload',
                'module_type' => 'WorkerBioData',
                'company_id' => $user['company_id'],
                'created_by' => $params['created_by'],
                'modified_by' => $params['created_by'],
                'user_type' => $user['user_type']
            ]
        );
        $rows = Excel::toArray(new WorkerImport($params, $workerBulkUpload), $file);
        $this->workerBulkUpload->where('id', $workerBulkUpload->id)->update(['actual_row_count' => count($rows[0])]);
        Excel::import(new WorkerImport($params, $workerBulkUpload), $file);
        return true;
    }

    /**
     * Retrieves a list of worker statuses based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - company_id: The company ID.
     *                      - application_id: The application ID.
     *                      - onboarding_country_id: The onboarding country ID.
     *
     * @return LengthAwarePaginator Returns a paginated list of worker statuses.
     *                                                     The list contains the following fields:
     *                                                     - id: The worker status ID.
     *                                                     - item: The worker status item.
     *                                                     - updated_on: The date when the worker status was last updated.
     *                                                     - status: The worker status.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function workerStatusList($request): mixed
    {
        return $this->workerStatus
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('worker_status.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('worker_status.id', 'worker_status.item', 'worker_status.updated_on', 'worker_status.status')
            ->where([
                'worker_status.application_id' => $request['application_id'],
                'worker_status.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('worker_status.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Updates the status of a worker based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - company_id: The company ID.
     *                      - id: The ID of the worker.
     *                      - status: The new status of the worker.
     *
     * @return array Returns an array with the following keys:
     *               - isUpdated: A boolean indicating whether the status was updated or not.
     *               - message: A message indicating the result of the update operation.
     *
     * @throws Exception If an error occurs during the update process.
     */
    public function updateStatus($request): array
    {
        $worker = $this->workers
            ->where('company_id', $request['company_id'])
            ->where('id', $request['id'])
            ->update(['status' => $request['status']]);
        return [
            "isUpdated" => $worker,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Retrieves the import history based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are supported:
     *                      - company_id: The company ID. (optional)
     *
     * @return LengthAwarePaginator Returns a paginator for the import history.
     *
     * @throws JWTException If an error occurs while authenticating the JWT token.
     *
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function importHistory($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workerBulkUpload
            ->select('id', 'actual_row_count', 'total_success', 'total_failure', 'process_status', 'created_at')
            ->where('module_type', 'WorkerBioData')
            ->where('process_status', 'Processed')
            ->whereNotNull('failure_case_url')
            ->whereIn('company_id', $request['company_id'])
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Exports the failure case URL based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - company_id: The company ID.
     *                      - bulk_upload_id: The bulk upload ID.
     *
     * @return array Returns an array with the following keys:
     *               - InvalidUser: If the worker bulk upload is not found.
     *               - queueError: If the worker bulk uploads process status is not 'Processed'
     *                             or if the failure case URL is null.
     *               - file_url: The failure case URL.
     *
     * @throws Exception If an error occurs during the export process.
     */
    public function failureExport($request): array
    {
        $workerBulkUpload = $this->workerBulkUpload
            ->where('company_id', $request['company_id'])
            ->where('id', $request['bulk_upload_id'])
            ->first();
        if (is_null($workerBulkUpload)) {
            return [
                'InvalidUser' => true
            ];
        }
        if ($workerBulkUpload->process_status != 'Processed' || is_null($workerBulkUpload->failure_case_url)) {
            return [
                'queueError' => true
            ];
        }
        return [
            'file_url' => $workerBulkUpload->failure_case_url
        ];
    }

    /**
     * Fetches dropdown data based on the provided request parameters.
     *
     * @param array $request The request parameters.
     *                      The following key-value pairs are required:
     *                      - company_id: The company ID.
     *                      - onboarding_country_id: The onboarding country ID.
     *                      - agent_id: The agent ID.
     *
     * @return Collection Returns the fetched dropdown data as a collection.
     *
     * @throws Exception If an error occurs during the data fetching process.
     */
    public function ksmDropDownBasedOnOnboardingAgent($request)
    {
        return $this->directRecruitmentOnboardingAgent
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request['company_id']);
            })->where('directrecruitment_onboarding_agent.onboarding_country_id', $request['onboarding_country_id'])
            ->where('directrecruitment_onboarding_agent.id', $request['agent_id'])
            ->select('directrecruitment_onboarding_agent.id', 'directrecruitment_onboarding_agent.ksm_reference_number')
            ->get();
    }

    /**
     * Checks if an application exists for a given company and onboarding country.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId The ID of the onboarding country.
     *
     * @return mixed Returns the application data if it exists.
     *               Otherwise, returns null.
     *
     * @throws Exception If an error occurs during the database query.
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

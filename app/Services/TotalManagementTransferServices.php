<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerEmployment;
use App\Models\CRMProspect;
use App\Models\TotalManagementProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\EContractProject;
use App\Models\EContractApplications;
use App\Models\TotalManagementApplications;
use App\Models\CRMProspectService;

class TotalManagementTransferServices
{
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_PROJECT_EXIST = ['projectExist' => true];
    public const ERROR_FROM_EXISTING = ['fromExistingError' => true];
    public const ERROR_OTHER_COMPANY = ['otherCompanyError' => true];
    public const ERROR_QUOTA = ['quotaError' => true];
    public const ERROR_QUOTA_FROM_EXISTING = ['quotaFromExistingError' => true];
    public const ERROR_FROM_EXISTING_WORKER = ['fromExistingWorkerError' => true];
    public const ERROR_FOMNEXT_QUOTA = ['fomnextQuotaError' => true];
    public const ERROR_CLIENT_QUOTA = ['clientQuotaError' => true];

    public const CUSTOMER = 'Customer';
    public const WORKER_STATUS_ASSIGNED = 'Assigned';
    public const WORKER_STATUS_ONBENCH = 'On-Bench';
    public const FROM_EXISTING = 1;
    public const NOT_FROM_EXISTING = 0;
    public const DIRECT_RECRUITMENT_SERVICE_ID = 1;
    public const TOTAL_MANAGEMENT_SERVICE_ID = 3;
    public const DEFAULT_TRANSFER_FLAG = 0;
    public const ACTIVE_TRANSFER_FLAG = 1;
    public const STATUS_ACTIVE = 1;
    public const FOMNEXT_PROSPECT_ID = 0;

    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var workerEmployment
     */
    private WorkerEmployment $workerEmployment;
    /**
     * @var totalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var crmProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var totalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * @var authServices
     */
    private AuthServices $authServices;
    /**
     * @var eContractProject
     */
    private EContractProject $eContractProject;
    /**
     * @var eContractApplications
     */
    private EContractApplications $eContractApplications;
    /**
     * @var crmProspectService
     */
    private CRMProspectService $crmProspectService;

    /**
     * Constructor method
     *
     * @param Workers $workers Instance of the Workers class
     * @param WorkerEmployment $workerEmployment Instance of the WorkerEmployment class
     * @param CRMProspect $crmProspect Instance of the CRMProspect class
     * @param TotalManagementProject $totalManagementProject Instance of the TotalManagementProject class
     * @param AuthServices $authServices Instance of the AuthServices class
     * @param EContractProject $eContractProject Instance of the EContractProject class
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class
     *
     * @return void
     */
    public function __construct(
        Workers                     $workers,
        WorkerEmployment            $workerEmployment,
        CRMProspect                 $crmProspect,
        TotalManagementProject      $totalManagementProject,
        AuthServices                $authServices,
        EContractProject            $eContractProject,
        EContractApplications       $eContractApplications,
        TotalManagementApplications $totalManagementApplications,
        CRMProspectService          $crmProspectService
    )
    {
        $this->workers = $workers;
        $this->workerEmployment = $workerEmployment;
        $this->crmProspect = $crmProspect;
        $this->totalManagementProject = $totalManagementProject;
        $this->authServices = $authServices;
        $this->eContractProject = $eContractProject;
        $this->eContractApplications = $eContractApplications;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
    }

    /**
     * Validates the input data for submitting a new validation.
     *
     * @return array The validation rules for the input data.
     */
    public function submitValidation(): array
    {
        return [
            'worker_id' => 'required',
            'current_project_id' => 'required',
            'new_project_id' => 'required',
            'accommodation_provider_id' => 'required|regex:/^[0-9]*$/',
            'accommodation_unit_id' => 'required|regex:/^[0-9]*$/',
            'last_working_day' => 'required|date|date_format:Y-m-d',
            'new_joining_date' => 'required|date|date_format:Y-m-d',
            'service_type' => 'required'
        ];
    }

    /**
     * Fetches the employment details of a worker.
     *
     * @param array $request The request data containing the worker ID.
     * @return mixed Returns the employment details of the worker.
     */
    public function workerEmploymentDetail($request): mixed
    {
        return $this->workers
            ->leftJoin('crm_prospects', 'crm_prospects.id', 'workers.crm_prospect_id')
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->where('workers.id', $request['worker_id'])
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->select('workers.id', 'crm_prospects.id as company_id', 'crm_prospects.company_name', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id', 'worker_employment.department', 'worker_employment.sub_department', 'worker_employment.work_start_date', 'worker_employment.work_end_date', 'worker_employment.service_type', 'worker_employment.transfer_flag')
            ->get();
    }

    /**
     * get the company list based on user data
     *
     * @param $userData
     *        service_type (string) type of service
     *        crm_prospect_id (int) ID of crm prospect
     *        prospect_service_id (int) service ID of prospect
     *        from_existing (int) from existing
     *
     * @return mixed Returns the paginated list of companies.
     */
    public function companyList($userData): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $companyIds = $this->authServices->getCompanyIds($user);
        return $this->crmProspect
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
            ->leftJoin('sectors', 'sectors.id', 'crm_prospect_services.sector_id')
            ->filterByStatusAndDeletion(self::STATUS_ACTIVE)
            ->applyServiceFilter($userData)
            ->filterByCompanyId($companyIds)
            ->applyUserFilter($user)
            ->applySearchFilter($userData)
            ->applySelectionFilter($userData)
            ->selectProspectData()
            ->orderBy('crm_prospects.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Apply service filter to the query builder based on user data
     *
     * @param array $userData The user data for filtering the services
     *        from_existing (int) Flag indicating if it is from an existing service
     *
     * @return $this Returns the query builder object with the applied service filter
     */
    private function applyServiceFilter($userData)
    {
        if (isset($userData['from_existing']) && $userData['from_existing'] == self::FROM_EXISTING) {
            $this->where('crm_prospect_services.from_existing', self::FROM_EXISTING)
                ->where('crm_prospect_services.service_id', '=', self::TOTAL_MANAGEMENT_SERVICE_ID);
        } else {
            $this->where('crm_prospect_services.service_id', '!=', self::DIRECT_RECRUITMENT_SERVICE_ID)
                ->where('crm_prospect_services.from_existing', '!=', self::FROM_EXISTING);
        }

        return $this;
    }

    /**
     * Apply user filter to the query.
     *
     * @param array $user The user data.
     *
     * @return $this The modified instance of the class.
     */
    private function applyUserFilter($user)
    {
        if ($user['user_type'] == self::CUSTOMER) {
            $this->where('crm_prospects.id', '=', $user['reference_id']);
        }

        return $this;
    }

    /**
     * Apply search filter to the query.
     *
     * @param array $userData The user data containing search keyword.
     *
     * @return $this The modified instance of the class.
     */
    private function applySearchFilter($userData)
    {
        $search = $userData['search'] ?? '';
        if (!empty($search)) {
            $this->where('crm_prospects.company_name', 'like', '%' . $search . '%');
        }

        return $this;
    }

    /**
     * Apply selection filter to the query.
     *
     * @param array $userData The user data containing filter information.
     *
     * @return $this The modified instance of the class.
     */
    private function applySelectionFilter($userData)
    {
        $filter = $userData['filter'] ?? '';
        if (!empty($filter)) {
            $this->where('crm_prospect_services.service_id', $filter);
        }

        return $this;
    }

    /**
     * Select prospect data from the query.
     *
     * @return $this The modified instance of the class.
     */
    private function selectProspectData()
    {
        return $this->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospect_services.service_id', 'sectors.sector_name', 'crm_prospect_services.from_existing')
            ->selectRaw("(CASE WHEN (crm_prospect_services.service_id = 1) THEN 'Direct Recruitment' WHEN (crm_prospect_services.service_id = 2) THEN 'e-Contract' ELSE 'Total Management' END) as service_type, crm_prospect_services.id as prospect_service_id")
            ->distinct('crm_prospect_services.id');
    }

    /**
     * Apply status and deletion filters to the query.
     *
     * @param mixed $status The status value to filter by.
     *
     * @return $this The modified instance of the class.
     */
    private function filterByStatusAndDeletion($status)
    {
        return $this->where('crm_prospects.status', $status)
            ->whereNull('crm_prospect_services.deleted_at');
    }

    /**
     * Apply filter to the query by company ID.
     *
     * @param array|int $companyIds The company IDs to filter by.
     *
     * @return $this The modified instance of the class.
     */
    private function filterByCompanyId($companyIds)
    {
        return $this->whereIn('crm_prospects.company_id', $companyIds);
    }

    /**
     * Get the list of projects based on the service type.
     *
     * @param array $request The request data containing the service type.
     *
     * @return mixed Either the Total Management project list or the eContract project list.
     */
    public function projectList($request)
    {
        $serviceType = $request['service_type'];
        $totalManagementServiceType = Config::get('services')['WORKER_MODULE_TYPE'][1];
        $eContractServiceType = Config::get('services')['WORKER_MODULE_TYPE'][2];

        if ($serviceType == $totalManagementServiceType) {
            return $this->getTotalManagementProjectList($request);
        }

        if ($serviceType == $eContractServiceType) {
            return $this->geteContractProjectList($request);
        }
    }

    /**
     * Get the total management project list
     *
     * @param array $request The request data
     *
     * @return Collection Returns the list of total management projects
     */
    private function getTotalManagementProjectList($request)
    {
        return $this->totalManagementProject
            ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'total_management_applications.service_id')
            ->where('crm_prospect_services.crm_prospect_id', $request['crm_prospect_id'])
            ->where('crm_prospect_services.id', $request['prospect_service_id'])
            ->where(function ($query) use ($request) {
                $this->applyFromExistingFilter($query, $request);
            })
            ->select('total_management_project.id', 'total_management_project.name')
            ->distinct('total_management_project.id')
            ->orderBy('total_management_project.id', 'desc')
            ->get();
    }

    /**
     * Apply the "from existing" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the filter values
     *
     * @return void
     */
    private function applyFromExistingFilter($query, $request)
    {
        if (isset($request['from_existing']) && $request['from_existing'] == self::FROM_EXISTING) {
            $query->where('crm_prospect_services.from_existing', self::FROM_EXISTING);
        } else {
            $query->where('crm_prospect_services.from_existing', self::NOT_FROM_EXISTING);
        }
    }

    /**
     * Get the list of eContract projects based on the given request parameters.
     *
     * @param array $request The request parameters containing 'crm_prospect_id' and 'prospect_service_id'
     *
     * @return Illuminate\Database\Eloquent\Collection The list of eContract projects matching the given request
     */
    private function geteContractProjectList($request)
    {
        return $this->eContractProject
            ->leftJoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'e-contract_applications.service_id')
            ->where('crm_prospect_services.crm_prospect_id', $request['crm_prospect_id'])
            ->where('crm_prospect_services.id', $request['prospect_service_id'])
            ->select('e-contract_project.id', 'e-contract_project.name')
            ->distinct('e-contract_project.id')
            ->orderBy('e-contract_project.id', 'desc')
            ->get();
    }

    /**
     * Submits a request for processing.
     *
     * @param mixed $request The request data
     *
     * @return array|bool An array of validation errors or boolean based on the processing result
     */
    public function submit($request): array|bool
    {
        $validationResult = $this->validateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $workerValidity = $this->checkWorkerValidity($request);
        if ($workerValidity !== true) {
            return $workerValidity;
        }

        return $this->processRequestBasedOnServiceType($request);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->submitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return array Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * Check the validity of the worker.
     *
     * @param array $request The request data.
     * @return array|bool Returns an array with an error message if the worker is unauthorized or if the project already exists.
     *                   Returns true if the worker is valid.
     */
    private function checkWorkerValidity($request): array|bool
    {
        $workerData = $this->getWorker($request);
        if (is_null($workerData)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $workerEmployment = $this->getWorkerEmploymentCount($request);
        if ($workerEmployment > 0) {
            return self::ERROR_PROJECT_EXIST;
        }

        return true;
    }

    /**
     * Process the request based on the service type.
     *
     * @param array $request The request data containing the service type.
     * @return array|bool If the service type is e-contract service and the process is successful, returns the result of processEContractService.
     *                   If the service type is total management service and the process is successful, returns the result of processTotalManagementService.
     *                   Otherwise, updates the worker transfer detail and worker employment detail and returns true.
     */
    private function processRequestBasedOnServiceType($request): array|bool
    {
        if ($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]) {
            $processEContractService = $this->processEContractService($request);
            if ($processEContractService) {
                return $processEContractService;
            }
        } else if ($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]) {
            $processTotalManagementService = $this->processTotalManagementService($request);
            if ($processTotalManagementService) {
                return $processTotalManagementService;
            }
        }

        $this->updateWorkerTransferDetail($request);
        $this->updateWorkerEmploymentDetail($request);
        return true;
    }

    /**
     * Get the worker based on the given request data.
     *
     * @param array $request The request data containing the company ID and worker ID.
     * @return mixed Returns the worker matching the given company ID and worker ID,
     *               or null if no matching worker is found.
     */
    private function getWorker($request)
    {
        return $this->workers::whereIn('company_id', $request['company_id'])->find($request['worker_id']);
    }

    /**
     * Get the count of employment for a worker.
     *
     * @param array $request The request data containing the worker ID, new project ID, and service type.
     * @return int Returns the count of employment for the worker based on the specified criteria.
     */
    private function getWorkerEmploymentCount($request)
    {
        return $this->workerEmployment->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['new_project_id']],
            ['service_type', $request['service_type']]
        ])
            ->where('transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('remove_date')
            ->count();
    }

    /**
     * Process the e-contract service for the given request.
     *
     * @param array $request The request data for the e-contract service.
     *
     * @return true[] Returns the appropriate error code if an error occurs during processing.
     *
     * @see getWorkerDetails()
     * @see isFromExisting()
     * @see notFromExistingAndChangeInProspectId()
     * @see getApplicationDetails()
     * @see isQuotaExceeded()
     */
    private function processEContractService($request)
    {
        $workerDetail = $this->getWorkerDetails($request);
        if ($this->isFromExisting($request)) {
            return self::ERROR_FROM_EXISTING;
        }
        if ($this->notFromExistingAndChangeInProspectId($request, $workerDetail)) {
            return self::ERROR_OTHER_COMPANY;
        }
        $applicationDetails = $this->getApplicationDetails($request);
        if (is_null($applicationDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }
        if ($this->isQuotaExceeded($request, $applicationDetails)) {
            return self::ERROR_QUOTA;
        }
    }

    /**
     * Get the details of a worker.
     *
     * @param array $request The request data containing the worker_id to fetch the details of the worker.
     * @return Model Returns the worker details as an instance of the worker model.
     * @throws ModelNotFoundException Throws an exception if the worker with the specified worker_id is not found.
     */
    private function getWorkerDetails($request)
    {
        return $this->workers->findOrFail($request['worker_id']);
    }

    /**
     * Check if the given request is from an existing source.
     *
     * @param array $request The request data containing the 'from_existing' key.
     * @return bool Returns true if the value of 'from_existing' key in the request array is equal to the constant value FROM_EXISTING.
     */
    private function isFromExisting($request)
    {
        return $request['from_existing'] == self::FROM_EXISTING;
    }

    /**
     * Check if request is not from existing, and there is a change in prospect ID.
     *
     * @param array $request The request data containing the 'from_existing' and 'new_prospect_id' keys.
     * @param object $workerDetail The worker details object containing the 'crm_prospect_id' property.
     * @return bool Returns true if the request is not from existing and there is a change in prospect ID.
     *              Returns false otherwise.
     */
    private function notFromExistingAndChangeInProspectId($request, $workerDetail)
    {
        return $request['from_existing'] == self::NOT_FROM_EXISTING && $workerDetail->crm_prospect_id != self::FOMNEXT_PROSPECT_ID && $workerDetail->crm_prospect_id != $request['new_prospect_id'];
    }

    /**
     * Get the application details for the given request data.
     *
     * @param array $request The request data containing the 'new_project_id' and 'company_id' fields.
     * @return mixed Returns the application details if found, otherwise null.
     */
    private function getApplicationDetails($request)
    {
        $projectDetails = $this->eContractProject->findOrFail($request['new_project_id']);
        return $this->eContractApplications::whereIn('company_id', $request['company_id'])->find($projectDetails->application_id);
    }

    /**
     * Check if the quota for the given application details is exceeded.
     *
     * @param array $request The request data.
     * @param object $applicationDetails The application details object.
     * @return bool Returns true if the quota is exceeded, otherwise false.
     */
    private function isQuotaExceeded($request, $applicationDetails)
    {
        $projectIds = $this->getProjectIds($request);
        $assignedWorkerCount = $this->getAssignedWorkerCount($projectIds);
        $assignedWorkerCount++;
        return $assignedWorkerCount > $applicationDetails->quota_requested;
    }

    /**
     * Get the project IDs for the given request.
     *
     * @param array $request The request data containing the 'new_project_id' key.
     * @return array Returns an array of project IDs associated with the given 'new_project_id'.
     */
    private function getProjectIds($request)
    {
        $projectDetails = $this->eContractProject->findOrFail($request['new_project_id']);
        $projectIds = $this->eContractProject->where('application_id', $projectDetails->application_id)
            ->select('id')
            ->get()
            ->toArray();
        return array_column($projectIds, 'id');
    }

    /**
     * Get the count of workers assigned to the given project IDs.
     *
     * @param array $projectIds The IDs of the projects to look for assigned workers.
     * @return int The count of workers assigned to the given project IDs.
     */
    private function getAssignedWorkerCount($projectIds)
    {
        return $this->workers
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->whereIn('worker_employment.project_id', $projectIds)
            ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[2])
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type')
            ->distinct('workers.id')->count('workers.id');
    }

    /**
     * Process the total management service for the given request.
     *
     * @param array $request The request data for the total management service.
     *
     * @return array Returns the appropriate error code if an error occurs during processing.
     *
     * @see getTotalManagementProjectDetails()
     * @see getTotalManagementApplicationDetails()
     * @see getTotalManagementServiceDetails()
     * @see processFromExisting()
     * @see processNotFromExisting()
     */
    private function processTotalManagementService($request)
    {
        $projectDetails = $this->getTotalManagementProjectDetails($request);
        $applicationDetails = $this->getTotalManagementApplicationDetails($request,$projectDetails);

        if (is_null($applicationDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $serviceDetails = $this->getTotalManagementServiceDetails($applicationDetails);

        if ($serviceDetails->from_existing == self::FROM_EXISTING) {
            return $this->processFromExisting($request, $projectDetails, $applicationDetails, $serviceDetails);
        } 
        if ($serviceDetails->from_existing == self::NOT_FROM_EXISTING) {
            return $this->processNotFromExisting($request, $projectDetails, $applicationDetails, $serviceDetails);
        }
    }

    /**
     * Get the details of a total management project detail.
     *
     * @param array $request The request data containing the new_project_id to fetch the details of the project.
     * @return Model Returns the project details as an instance of the project model.
     * @throws ModelNotFoundException Throws an exception if the project with the specified new_project_id is not found.
     */
    private function getTotalManagementProjectDetails($request)
    {
        return $this->totalManagementProject->findOrFail($request['new_project_id']);
    }

    /**
     * Get the details of a total management application detail.
     *
     * @param array $request The request data containing the company_id to fetch the details of the application.
     * @param object $projectDetails The project details object.
     * @return mixed Returns the application matching the given company ID and application ID,
     *               or null if no matching application is found.
     */
    private function getTotalManagementApplicationDetails($request,$projectDetails)
    {
        return $this->totalManagementApplications::whereIn('company_id', $request['company_id'])->find($projectDetails->application_id);
    }

    /**
     * Get the details of a total management service detail.
     *
     * @param object $applicationDetails The application details object.
     * 
     * @return Model Returns the CRM Prospect Service as an instance of the crmProspectService model.
     * @throws ModelNotFoundException Throws an exception if the crmProspectService with the specified service_id is not found.
     */
    private function getTotalManagementServiceDetails($applicationDetails)
    {
        return $this->crmProspectService->findOrFail($applicationDetails->service_id);
    }

    /**
     * Process the from existing
     *
     * @param array $request The request data containing the 'worker_id' and 'from_existing'.
     * @param object $projectDetails The project details object.
     * @param object $applicationDetails The application details object.
     * @param object $serviceDetails The service details object.
     * 
     * @return array Returns the appropriate error code if an error occurs during processing.
     * 
     * @see getWorkerDetails()
     * @see processFromExistingWorker()
     * 
     */
    private function processFromExisting($request, $projectDetails, $applicationDetails, $serviceDetails)
    {
        $workerDetail = $this->getWorkerDetails($request);

        if ($request['from_existing'] == self::NOT_FROM_EXISTING) {
            return self::ERROR_QUOTA_FROM_EXISTING;
        } 
        if ($request['from_existing'] == self::FROM_EXISTING) {
            return $this->processFromExistingWorker($request, $workerDetail, $projectDetails, $applicationDetails);
        }
    }

    /**
     * Process the not from existing
     *
     * @param array $request The request data containing the 'worker_id' and 'from_existing'.
     * @param object $projectDetails The project details object.
     * @param object $applicationDetails The application details object.
     * @param object $serviceDetails The service details object.
     * 
     * @return array Returns the appropriate error code if an error occurs during processing.
     * 
     * @see getWorkerCount()
     * @see getWorkerDetails()
     * @see processNotFromExistingWorker()
     * 
     */
    private function processNotFromExisting($request, $projectDetails, $applicationDetails, $serviceDetails)
    {
        $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
        $workerDetail = $this->getWorkerDetails($request);

        if ($request['from_existing'] == self::FROM_EXISTING) {
            return self::ERROR_FROM_EXISTING_WORKER;
        } 
        if ($request['from_existing'] == self::NOT_FROM_EXISTING) {
            return $this->processNotFromExistingWorker($request, $workerDetail, $workerCountArray, $serviceDetails);
        }
    }

    /**
     * Process From Existing Worker
     *
     * @param array $request The request data containing the 'new_prospect_id' and 'current_project_id'.
     * @param object $projectDetails The project details object.
     * @param object $applicationDetails The application details object.
     * @param object $workerDetail The worker details object.
     * 
     * @return array Returns the appropriate error code if an error occurs during processing.
     * 
     * @see getWorkerCount()
     * 
     */
    private function processFromExistingWorker($request, $workerDetail, $projectDetails, $applicationDetails)
    {
        if ($workerDetail->crm_prospect_id != $request['new_prospect_id']) {
            return self::ERROR_OTHER_COMPANY;
        }

        $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
        $currentProjectDetails = $this->totalManagementProject->findOrFail($request['current_project_id']);

        if ($currentProjectDetails->application_id != $projectDetails->application_id) {
            $workerCountArray['clientWorkersCount']++;
        }

        if ($workerCountArray['clientWorkersCount'] > $applicationDetails->quota_applied) {
            return self::ERROR_QUOTA;
        }
    }

    /**
     * Process Not From Existing Worker
     *
     * @param array $request The request data containing the 'new_prospect_id' and 'current_project_id'.
     * @param object $workerDetail The worker details object.
     * @param array $workerCountArray The worker count array.
     * @param object $serviceDetails The service details object.
     * 
     * @return array Returns the appropriate error code if an error occurs during processing.
     * 
     * @see processClientWorker()
     * 
     */
    private function processNotFromExistingWorker($request, $workerDetail, $workerCountArray, $serviceDetails)
    {
        if ($workerDetail->crm_prospect_id == self::FOMNEXT_PROSPECT_ID) {
            $workerCountArray['fomnextWorkersCount']++;

            if ($workerCountArray['fomnextWorkersCount'] > $serviceDetails->fomnext_quota) {
                return self::ERROR_FOMNEXT_QUOTA;
            }
        } 
        
        if ($workerDetail->crm_prospect_id != self::FOMNEXT_PROSPECT_ID) {
            return $this->processClientWorker($request, $workerDetail, $workerCountArray, $serviceDetails);
        }
    }
    
    /**
     * Process Client Worker
     *
     * @param array $request The request data containing the 'new_prospect_id' and 'current_project_id'.
     * @param object $workerDetail The worker details object.
     * @param array $workerCountArray The worker count array.
     * @param object $serviceDetails The service details object.
     * 
     * @return array Returns the appropriate error code if an error occurs during processing.
     * 
     */
    private function processClientWorker($request, $workerDetail, $workerCountArray, $serviceDetails)
    {
        if ($workerDetail->crm_prospect_id != $request['new_prospect_id']) {
            return self::ERROR_OTHER_COMPANY;
        }

        $workerCountArray['clientWorkersCount']++;

        if ($workerCountArray['clientWorkersCount'] > $serviceDetails->client_quota) {
            return self::ERROR_CLIENT_QUOTA;
        }
    }

    /**
     * Update the worker transfer record based on provided request data
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              service_type (string) type of the service
     *              modified_by (int) modified user ID
     *
     * @return void
     * 
     * @see updateWorker()
     * @see getWorkerDetails()
     * @see isEcontractServiceType()
     * @see updateEcontractServiceType()
     * @see isTotalManagementServiceType()
     * @see updateTotalManagementServiceType()
     * 
     */
    private function updateWorkerTransferDetail($request)
    {
        $this->updateWorker($request);

        $worker = $this->getWorkerDetails($request);
        
        if ($this->isEcontractServiceType($request)) {
            $this->updateEcontractServiceType($worker);
        } 
        
        if ($this->isTotalManagementServiceType($request)) {
            $this->updateTotalManagementServiceType($worker);
        }
    
        $worker->module_type = $request['service_type'];
        $worker->updated_at = Carbon::now();
        $worker->modified_by = $request['modified_by'];
        $worker->save();
    }
    
    /**
     * Update the worker propect detail.
     *
     * @param array $request The request data containing the 'worker_id', 'new_prospect_id' and modified_by.
     *
     */
    private function updateWorker($request)
    {
        $this->workers->where([
            'id' => $request['worker_id'],
        ])->update([
            'crm_prospect_id' => $request['new_prospect_id'],
            'updated_at' => Carbon::now(),
            'modified_by' => $request['modified_by']
        ]);
    }

    /**
     * Check if the given request is e-contract service type.
     *
     * @param array $request The request data containing the 'service_type' key.
     * @return bool Returns true if the value of 'service_type' key in the request array is equal to the constant value Config::get('services.WORKER_MODULE_TYPE')[2].
     */
    private function isEcontractServiceType($request)
    {
        return isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2];
    }

    /**
     * Check if the given request is total management service type.
     *
     * @param array $request The request data containing the 'service_type' key.
     * @return bool Returns true if the value of 'service_type' key in the request array is equal to the constant value Config::get('services.WORKER_MODULE_TYPE')[1].
     */
    private function isTotalManagementServiceType($request)
    {
        return isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1];
    }

    /**
     * Update the worker e-contract detail.
     *
     * @param object $worker The worker object.
     *
     */
    private function updateEcontractServiceType($worker)
    {
        $worker->crm_prospect_id = self::FOMNEXT_PROSPECT_ID;
        $worker->econtract_status = self::WORKER_STATUS_ASSIGNED;

        if (in_array($worker->total_management_status, Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))) {
            $worker->total_management_status = self::WORKER_STATUS_ONBENCH;
        }
    }

    /**
     * Update the worker total management detail.
     *
     * @param object $worker The worker object.
     *
     */
    private function updateTotalManagementServiceType($worker)
    {
        $worker->total_management_status = self::WORKER_STATUS_ASSIGNED;

        if (in_array($worker->econtract_status, Config::get('services.ECONTRACT_WORKER_STATUS'))) {
            $worker->econtract_status = self::WORKER_STATUS_ONBENCH;
        }
    }

    /**
     * Update the worker employment record based on provided request data
     *
     * @param array $request
     *              current_project_id (int) current project ID of the worker
     *              worker_id (int) ID of the worker
     *              last_working_day (date) last working date of the worker
     *              modified_by (int) modified user ID
     *              new_project_id (int) new transfer project ID
     *              accommodation_provider_id (int) ID of the accommodation
     *              accommodation_unit_id (int) ID of the accommodation unit
     *              department (string) department of worker
     *              sub_department (string) sub department of worker
     *              new_joining_date (date) date of worker joining date
     *              service_type (string) type of the service
     *
     * @return void
     * 
     * @see updateCurrentWorkerEmployment()
     * @see createNewWorkerEmployment()
     * 
     */
    private function updateWorkerEmploymentDetail($request)
    {
        $this->updateCurrentWorkerEmployment($request);
        $this->createNewWorkerEmployment($request);
    }

    /**
     * Update the worker current employment record based on provided request data
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              current_project_id (int) current project ID
     *              last_working_day (date) last working day
     *              modified_by (int) modified user ID
     *
     * @return void
     * 
     */
    private function updateCurrentWorkerEmployment($request)
    {
        $this->workerEmployment->where([
            'project_id' => $request['current_project_id'],
            'worker_id' => $request['worker_id']
        ])->update([
            'work_end_date' => $request['last_working_day'],
            'transfer_flag' => self::ACTIVE_TRANSFER_FLAG,
            'updated_at' => Carbon::now(),
            'modified_by' => $request['modified_by']
        ]);
    }

    /**
     * Create the worker employment record based on provided request data
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              new_project_id (int) new project ID
     *              accommodation_provider_id (int) ID of the accommodation
     *              accommodation_unit_id (int) ID of the accommodation unit
     *              department (string) department
     *              sub_department (string) sub department
     *              new_joining_date (date) joining date
     *              service_type (string) type of the service
     *              modified_by (int) modified user ID
     *
     * @return void
     * 
     */
    private function createNewWorkerEmployment($request)
    {
        $this->workerEmployment->create([
            'worker_id' => $request['worker_id'],
            'project_id' => $request['new_project_id'],
            'accommodation_provider_id' => $request['accommodation_provider_id'] ?? null,
            'accommodation_unit_id' => $request['accommodation_unit_id'] ?? null,
            'department' => $request['department'] ?? null,
            'sub_department' => $request['sub_department'] ?? null,
            'work_start_date' => $request['new_joining_date'],
            'service_type' => $request['service_type'],
            'transfer_flag' => self::DEFAULT_TRANSFER_FLAG,
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);
    }

    /**
     * Retrive the worker count based on request data
     *
     * @param $applicationId , $prospectId
     * @return array Returns an array with client woker count and fomnext worker count
     */
    public function getWorkerCount($applicationId, $prospectId): array
    {
        $projectIds = $this->getTotalManagementProjectIds($applicationId);
        $clientWorkersCount = $this->getClientWorkersCount($prospectId, $projectIds);
        $fomnextWorkersCount = $this->getFomnextWorkersCount($projectIds);

        return [
            'clientWorkersCount' => $clientWorkersCount,
            'fomnextWorkersCount' => $fomnextWorkersCount
        ];
    }

    /**
     * Retrieve total management project ID's.
     *
     * @param $applicationId
     * 
     * @return array Returns the total management project ID's
     */
    private function getTotalManagementProjectIds($applicationId)
    {
        $projectIds = $this->totalManagementProject->where('application_id', $applicationId)
                            ->select('id')
                            ->get()
                            ->toArray();
        return array_column($projectIds, 'id');
    }

    /**
     * Retrieve client worker count.
     *
     * @param $prospectId
     * @param $projectIds
     * @return mixed Returns the client worker count
     */
    private function getClientWorkersCount($prospectId, $projectIds)
    {
        return $this->workers
            ->leftJoin('worker_employment', function ($query) {
                $query->on('worker_employment.worker_id', '=', 'workers.id')
                    ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
                    ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                    ->whereNull('worker_employment.remove_date')
                    ->whereNull('worker_employment.work_end_date')
                    ->whereNull('worker_employment.event_type');
            })
            ->where('workers.crm_prospect_id', $prospectId)
            ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
            ->whereIn('worker_employment.project_id', $projectIds)
            ->distinct('workers.id')
            ->count('workers.id');
    }

    /**
     * Retrieve formnext worker count.
     *
     * @param $projectIds
     * @return mixed Returns formnext workers count
     */
    private function getFomnextWorkersCount($projectIds)
    {
        return $this->workers
            ->leftJoin('worker_employment', function ($query) {
                $query->on('worker_employment.worker_id', '=', 'workers.id')
                    ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
                    ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                    ->whereNull('worker_employment.remove_date')
                    ->whereNull('worker_employment.work_end_date')
                    ->whereNull('worker_employment.event_type');
            })
            ->where('workers.crm_prospect_id', self::FOMNEXT_PROSPECT_ID)
            ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
            ->where('worker_employment.project_id', $projectIds)
            ->distinct('workers.id')
            ->count('workers.id');
    }
}
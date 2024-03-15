<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\Vendor;
use App\Models\Accommodation;
use App\Models\WorkerEmployment;
use App\Models\TotalManagementApplications;
use App\Models\CRMProspectService;
use App\Models\DirectrecruitmentApplications;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\TotalManagementProject;
use App\Services\AuthServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TotalManagementWorkerServices
{
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_QUOTA = ['quotaError' => true];
    public const ERROR_FOMNEXT_QUOTA = ['fomnextQuotaError' => true];
    public const ERROR_CLIENT_QUOTA = ['clientQuotaError' => true];

    public const DEFAULT_TRANSFER_FLAG = 0;
    public const CUSTOMER = 'Customer';
    public const WORKER_STATUS_ONBENCH = 'On-Bench';
    public const WORKER_STATUS_ASSIGNED = 'Assigned';
    public const PLKS_STATUS_APPROVED = 'Approved';
    public const FOMNEXT_PROSPECT_ID = 0;
    public const FROM_EXISTING = 1;
    public const NOT_FROM_EXISTING = 0;
    public const DEFAULT_VALUE = 0;
    public const STATUS_ACTIVE = 1;


    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var Vendor
     */
    private Vendor $vendor;
    /**
     * @var Accommodation
     */
    private Accommodation $accommodation;
    /**
     * @var WorkerEmployment
     */
    private WorkerEmployment $workerEmployment;
    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * 
     * TotalManagementWorkerServices constructor method
     * 
     * @param Workers $workers Instance of the Workers class
     * @param Vendor $vendor Instance of the Vendor class
     * @param Accommodation $accommodation Instance of the Accommodation class
     * @param WorkerEmployment $workerEmployment Instance of the WorkerEmployment class
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class
     * @param TotalManagementProject $totalManagementProject Instance of the TotalManagementProject class
     * @param AuthServices $authServices Instance of the AuthServices class
     * 
     * @return void
     * 
     */
    public function __construct(
        Workers                       $workers, 
        Vendor                        $vendor, 
        Accommodation                 $accommodation, 
        WorkerEmployment              $workerEmployment, 
        TotalManagementApplications   $totalManagementApplications, 
        CRMProspectService            $crmProspectService, 
        DirectrecruitmentApplications $directrecruitmentApplications, 
        TotalManagementProject        $totalManagementProject, 
        AuthServices                  $authServices
    )
    {
        $this->workers = $workers;
        $this->vendor = $vendor;
        $this->accommodation = $accommodation;
        $this->workerEmployment = $workerEmployment;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->totalManagementProject = $totalManagementProject;
        $this->authServices = $authServices;
    }
    /**
     * validate the search request data
     * 
     * @return array The array containing the validation rules.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * validate the create request data
     * 
     * @return array The array containing the validation rules.
     */
    public function createValidation(): array
    {
        return [
            'department' => 'regex:/^[a-zA-Z ]*$/',
            'sub_department' => 'regex:/^[a-zA-Z ]*$/',
            'accommodation_provider_id' => 'required|regex:/^[0-9]*$/',
            'accommodation_unit_id' => 'required|regex:/^[0-9]*$/',
            'work_start_date' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }

    /**
     * validate the remove request data
     * 
     * @return array The array containing the validation rules.
     */
    public function removeValidation(): array
    {
        return [
            'project_id' => 'required',
            'worker_id' => 'required',
            'remove_date' => 'required',
            'last_working_day' => 'required|date|date_format:Y-m-d',
        ];
    }

    /**
     * list the total management workers
     * 
     * @param $request
     *        project_id (int) ID of the project
     *        search (string) search parameter
     *        filter (int) status of worker
     *        company_filter (int) ID of the company
     * 
     * 
     * @return mixed Returns The paginated list of total management workers
     * 
     * @see applyCondition()
     * @see applyReferenceFilter()
     * @see applySearchFilter()
     * @see applyStatusFilter()
     * @see applyCompanyFilter()
     * @see listSelectColumns()
     * 
     */
    public function list($request): mixed
    {
        $validationResult = $this->validateSearchRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $data = $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'total_management_applications.service_id')
            ->leftJoin('vendors as vendor_transport', 'vendor_transport.id', 'total_management_project.transportation_provider_id')
            ->leftJoin('vendors', 'vendors.id', 'worker_employment.accommodation_provider_id');
        $data = $this->applyCondition($request,$data);
        $data = $this->applyReferenceFilter($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->applyStatusFilter($request,$data);
        $data = $this->applyCompanyFilter($request,$data);
        $data = $this->listSelectColumns($data)
                        ->distinct('workers.id')
                        ->orderBy('workers.id','DESC')
                        ->paginate(Config::get('services.paginate_worker_row'));
        return $data;
    }

    /**
     * Validate the worker list request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateSearchRequest($request): array|bool
    {
        if(!empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        project_id (int) ID of the project
     *        company_id (array) ID of the user company
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->where('total_management_project.id', $request['project_id'])
            ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date')
            ->whereIn('workers.company_id', $request['company_id']);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data for filtering the company
     *        search (string) search parameter
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if (!empty($search)) {
                $query->where('workers.name', 'like', '%' . $search . '%');
                $query->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $search . '%');
                $query->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $search . '%');
                $query->orWhere('worker_employment.department', 'like', '%' . $search . '%');
            }
        });
    }

    /**
     * Apply reference filter to the query builder based on user data
     *
     * @param array $request The user data for filtering the company
     *        reference_id (int) Id of the company
     *
     * @return $data Returns the query builder object with the applied reference filter
     */
    private function applyReferenceFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == self::CUSTOMER) {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        });
    }

    /**
     * Apply status filter to the query builder based on user data
     *
     * @param array $request The user data for filtering the worker
     *        filter (string) status of the filter
     *
     * @return $data Returns the query builder object with the applied status filter
     */
    private function applyStatusFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $filter = $request['filter'] ?? '';
            if((!empty($filter)) || $filter == self::DEFAULT_VALUE) {
                $query->where('workers.status', $filter);
            }
        });
    }

    /**
     * Apply company filter to the query builder based on user data
     *
     * @param array $request The user data for filtering the worker
     *        company_filter (int) ID of the company
     *
     * @return $data Returns the query builder object with the applied company filter
     */
    private function applyCompanyFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $companyFilter = $request['company_filter'] ?? '';
            if ((!empty($companyFilter)) || $companyFilter == self::DEFAULT_VALUE) {
                $query->where('workers.crm_prospect_id', $companyFilter);
            }
        });
    }

    /**
     * Select worker data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'vendors.name as accommodation_provider', 'vendor_transport.name as transportation_provider', 'worker_employment.department', 'workers.status', 'workers.total_management_status', 'worker_employment.status as worker_assign_status', 'worker_employment.remove_date', 'worker_employment.remarks', 'crm_prospect_services.from_existing', 'total_management_project.application_id');
    }

    /**
     * list the on-bench workers
     * 
     * @param $request
     *        prospect_id (int) ID of the prospect
     *        application_id (int) ID of the application
     *        search (string) search parameter
     *        company_filter (int) ID of the company
     *        ksm_reference_number (string) KSM reference number of worker
     * 
     * 
     * @return mixed Returns The paginated list of on-bench workers
     * 
     * @see workerListApplyCondition()
     * @see applyReferenceFilter()
     * @see workerListApplyFromExistingFilter()
     * @see workerListApplySearchFilter()
     * @see applyCompanyFilter()
     * @see workerListApplyKsmReferenceNumberFilter()
     * @see workerListSelectColumns()
     * 
     */
    public function workerListForAssignWorker($request): mixed
    {
        $validationResult = $this->validateSearchRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request['company_ids'] = array($request['prospect_id'], self::FOMNEXT_PROSPECT_ID);
        $applicationDetails = $this->getTotalManagementApplicationById($request['application_id']);
        $serviceDetails = $this->getApplicationServiceDetails($applicationDetails->service_id);
        
        if (isset($serviceDetails->from_existing) && $serviceDetails->from_existing == self::FROM_EXISTING) {
            $request['from_existing'] = $serviceDetails->from_existing;
        } else {
            $request['from_existing'] = self::NOT_FROM_EXISTING;
        }

        $data = $this->workers
                    ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id');
        $data = $this->workerListApplyCondition($request,$data);
        $data = $this->applyReferenceFilter($request,$data);  
        $data = $this->workerListApplyFromExistingFilter($request,$data);
        $data = $this->workerListApplySearchFilter($request,$data);
        $data = $this->applyCompanyFilter($request,$data);
        $data = $this->workerListApplyKsmReferenceNumberFilter($request,$data);
        $data = $this->workerListSelectColumns($data)
                     ->distinct()
                     ->orderBy('workers.created_at','DESC')
                     ->paginate(Config::get('services.paginate_worker_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        company_ids (int) ID of the prospect
     *        company_id (array) ID of the user company
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function workerListApplyCondition($request,$data)
    {
        return $data->where('workers.econtract_status', self::WORKER_STATUS_ONBENCH)
                    ->where('workers.total_management_status', self::WORKER_STATUS_ONBENCH)
                    ->whereIn('workers.crm_prospect_id', $request['company_ids'])
                    ->whereIn('workers.company_id', $request['company_id']);
    }

    /**
     * Apply from existing filter to the query builder based on user data
     *
     * @param array $request The user data
     *        from_existing (int) from existing flag
     *        prospect_id (int) ID of the prospect
     *
     * @return $data Returns the query builder object with the applied from existing filter
     */
    private function workerListApplyFromExistingFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            if(isset($request['from_existing']) && $request['from_existing'] == self::FROM_EXISTING){
                $query->where([
                    ['workers.crm_prospect_id', $request['prospect_id']],
                    ['workers.plks_status', self::PLKS_STATUS_APPROVED]
                ]);
            }else {
                    $query->where('workers.module_type', '<>', Config::get('services.WORKER_MODULE_TYPE')[0])->orWhereNull('workers.module_type');
            }
        });
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search (string) search parameter
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function workerListApplySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if (!empty($search)) {
                $query->where('workers.name', 'like', '%'.$search.'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$search.'%')
                ->orWhere('workers.passport_number', 'like', '%'.$search.'%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$search.'%');
            }
        });
    }
    
    /**
     * Apply ksm reference number filter to the query builder based on user data
     *
     * @param array $request The user data
     *        ksm_reference_number (int) ksm reference number
     *
     * @return $data Returns the query builder object with the applied ksm reference number filter
     */
    private function workerListApplyKsmReferenceNumberFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $ksmReferenceNumber = $request['ksm_reference_number'] ?? '';
            if(!empty($ksmReferenceNumber)) {
                $query->where('worker_visa.ksm_reference_number', $ksmReferenceNumber);
            }
        });
    }
    
    /**
     * Select worker data from the query.
     *
     * @return $this The modified instance of the class.
     */
    private function workerListSelectColumns($data)
    {
        return $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'workers.crm_prospect_id as company_id', 'workers.econtract_status', 'workers.total_management_status', 'workers.plks_status', 'workers.module_type', 'workers.created_at');
    } 

    /**
     * Get the details of a total management application detail.
     *
     * @param $id The application id
     * 
     * @return Model Returns the total management application details as an instance of the totalManagementApplications model.
     * @throws ModelNotFoundException Throws an exception if the total management application with the specified id is not found.
     */
    private function getTotalManagementApplicationById($id)
    {
        return $this->totalManagementApplications->findOrFail($id);
    }

    /**
     * list the accommodation Provider
     * 
     * @param $request
     *        company_id (array) ID of the company
     * 
     * 
     * @return mixed Returns an accommodation Provider
     */
    public function accommodationProviderDropDown($request): mixed
    {
        return $this->vendor->where('type', 'Accommodation')
                ->whereIn('company_id', $request['company_id'])
                ->select('id', 'name')
                ->get();
    }
    /**
     * list the accommodation unit
     * 
     * @param $request
     *        id (int) ID of the accommodation vendor
     *        company_id (array) ID of the company
     * 
     * @return mixed Returns an accommodation unit
     */
    public function accommodationUnitDropDown($request): mixed
    {
        return $this->accommodation
        ->join('vendors', function ($join) use ($request) {
            $join->on('vendors.id', '=', 'accommodation.vendor_id')
                 ->whereIn('vendors.company_id', $request['company_id']);
        })
        ->where('accommodation.vendor_id', $request['id'])->select('accommodation.id', 'accommodation.name')->get();
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
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * process the assign worker submit
     * 
     * @param $request  The request data containing assign worker details
     * 
     * @return array|bool Returns true if the assign worker is submitted successfully, otherwise returns an array with error details
     *                    Returns self::ERROR_UNAUTHORIZED if the user access invalid application
     *                    Returns self::ERROR_QUOTA if the quota exceed the applied quota
     *                    Returns self::ERROR_CLIENT_QUOTA if the quota exceed the client quota
     *                    Returns self::ERROR_FOMNEXT_QUOTA if the quota exceed the fomnext quota
     * 
     * @see validateAssignWorkerRequest()
     * @see getTotalManagementProjectDetails()
     * @see getTotalManagementApplicationDetails()
     * @see getApplicationServiceDetails()
     * @see getWorkerCount()
     * @see processAssignWorkerFromExisting()
     * @see processAssignWorkerNotFromExisting()
     * @see processAssignWorkers()
     * 
     */
    public function assignWorker($request): array|bool
    {
        $request = $this->enrichRequestWithUserDetails($request);

        $validationResult = $this->validateAssignWorkerRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        if(isset($request['workers']) && !empty($request['workers'])) 
        {
            $projectDetails = $this->getTotalManagementProjectDetails($request);
            $request['application_id'] = $projectDetails->application_id ?? self::DEFAULT_VALUE;
            $applicationDetails = $this->getTotalManagementApplicationDetails($request);
            if(is_null($applicationDetails)){
                return self::ERROR_UNAUTHORIZED;
            }
            $serviceDetails = $this->getApplicationServiceDetails($applicationDetails->service_id);
            $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
            
            if($serviceDetails->from_existing == self::FROM_EXISTING) {
                $this->processAssignWorkerFromExisting($request, $workerCountArray, $applicationDetails);
            } else {
                $this->processAssignWorkerNotFromExisting($request, $workerCountArray, $applicationDetails, $serviceDetails);
            }
            $this->processAssignWorkers($request);
        }
        return true;
    }

    /**
     * Get the details of a total management project.
     *
     * @param array $request The request data containing the project_id to fetch the details of the project.
     * 
     * @return Model Returns the total management project details as an instance of the totalManagementProject model.
     * @throws ModelNotFoundException Throws an exception if the total management project with the specified project_id is not found.
     */
    private function getTotalManagementProjectDetails($request)
    {
        return $this->totalManagementProject->findOrFail($request['project_id']);
    }

    /**
     * Validate the assign worker request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAssignWorkerRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Process the assign worker from existing
     *
     * @param array $request The request data containing the 'worker_id'.
     * @param array $workerCountArray The worker count array.
     * @param object $applicationDetails The application details object.
     * 
     * @return mixed|void Returns the appropriate error code if an error occurs during processing. otherwise null
     * 
     */
    private function processAssignWorkerFromExisting($request, $workerCountArray, $applicationDetails)
    {
        $workerCountArray['clientWorkersCount'] += count($request['workers']);
        if($workerCountArray['clientWorkersCount'] > $applicationDetails->quota_applied) {
            return self::ERROR_QUOTA;
        }
    }

    /**
     * Process the assign worker not from existing
     *
     * @param array $request The request data containing the 'worker_id'.
     * @param array $workerCountArray The worker count array.
     * @param object $applicationDetails The application details object.
     * @param object $serviceDetails The service details object.
     * 
     * @return mixed|void Returns the appropriate error code if an error occurs during processing. otherwise null
     * 
     * @see getCompanyWorker()
     * 
     */
    private function processAssignWorkerNotFromExisting($request, $workerCountArray, $applicationDetails, $serviceDetails)
    {
        $fomnextWorkerCount = $this->getCompanyWorker($request,self::FOMNEXT_PROSPECT_ID);

        $clientWorkerCount = $this->getCompanyWorker($request,$applicationDetails->crm_prospect_id);

        $workerCountArray['fomnextWorkersCount'] += $fomnextWorkerCount;
        if($workerCountArray['fomnextWorkersCount'] > $serviceDetails->fomnext_quota) {
            return self::ERROR_FOMNEXT_QUOTA;
        }
                
        $workerCountArray['clientWorkersCount'] += $clientWorkerCount;
        if($workerCountArray['clientWorkersCount'] > $serviceDetails->client_quota) {
            return self::ERROR_CLIENT_QUOTA;
        }
    }

    /**
     * Retrive the company worker count.
     *
     * @param $request
     *        workers (array) ID of the worker
     * @param int $prospectId ID of the prospect
     * 
     * @return int Returns the count
     */
    private function getCompanyWorker($request,$prospectId)
    {
        return $this->workers->whereIn('id', $request['workers'])
            ->where('crm_prospect_id', $prospectId)
            ->count();
    }

    /**
     * process the assign worker on provided request data
     *
     * @param array $request
     *              worker_id (array) ID of the worker
     *              project_id (int) ID of the project
     *              department (string) department
     *              sub_department (string) department
     *              accommodation_provider_id (int) ID of the accommodation provider
     *              accommodation_unit_id (int) ID of the accommodation unit
     *              work_start_date (date) start date of the project
     *              created_by (int) ID of the user who created the record
     * 
     * @return void
     * 
     * @see processAssignWorkersUpdate()
     * 
     */
    private function processAssignWorkers($request){
        foreach ($request['workers'] as $workerId) {
            $this->workerEmployment->create([
                'worker_id' => $workerId,
                'project_id' => $request['project_id'],
                'department' => $request['department'],
                'sub_department' => $request['sub_department'],
                'accommodation_provider_id' => $request['accommodation_provider_id'],
                'accommodation_unit_id' => $request['accommodation_unit_id'],
                'work_start_date' => $request['work_start_date'],
                'service_type' => Config::get('services.WORKER_MODULE_TYPE')[1],
                'created_by' => $request['created_by'],
                'modified_by' => $request['created_by']
            ]);
        }
        $this->processAssignWorkersUpdate($request);    
    }

    /**
     * Update the worker status based on provided request data
     *
     * @param array $request
     *              workers (int) ID of the worker
     *              created_by (int) modified user ID
     *
     * @return void
     * 
     */
    private function processAssignWorkersUpdate($request)
    {
        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'module_type' => Config::get('services.WORKER_MODULE_TYPE')[1],
                'total_management_status' => self::WORKER_STATUS_ASSIGNED,
                'modified_by' => $request['created_by']
            ]);
    }

    /**
     * get the Balanced Quota
     * 
     * @param $request
     *        company_id (array) ID of the company
     *        application_id (int) ID of the application
     * 
     * @return array Returns the Balanced Quota
     * 
     * 
     * @see getTotalManagementApplicationDetails()
     * @see getApplicationServiceDetails()
     * @see getWorkerCount()
     * 
     */
    public function getBalancedQuota($request): array
    {
        $applicationDetails = $this->getTotalManagementApplicationDetails($request);
        if(is_null($applicationDetails)){
            return self::ERROR_UNAUTHORIZED;
        }
        $serviceDetails = $this->getApplicationServiceDetails($applicationDetails->service_id);
        $workerCount = $this->getWorkerCount($request['application_id'], $applicationDetails->crm_prospect_id);

        if($serviceDetails->from_existing == self::NOT_FROM_EXISTING) {
            return [
                'clientQuota' => $serviceDetails->client_quota,
                'clientBalancedQuota' => $serviceDetails->client_quota - $workerCount['clientWorkersCount'],
                'fomnextQuota' => $serviceDetails->fomnext_quota,
                'fomnextBalancedQuota' => $serviceDetails->fomnext_quota - $workerCount['fomnextWorkersCount']
            ];
        } else {
            return [
                'serviceQuota' => $serviceDetails->service_quota,
                'balancedServiceQuota' => $serviceDetails->service_quota - $workerCount['clientWorkersCount']
            ];
        }
    }

    /**
     * Get the details of a total management application detail.
     *
     * @param array $request The request data containing the company_id to fetch the details of the application.
     * @return Model Returns the total management application details as an instance of the totalManagementApplications model.
     * @throws ModelNotFoundException Throws an exception if the total management application with the specified application_id is not found.
     */
    private function getTotalManagementApplicationDetails($request)
    {
        return $this->totalManagementApplications::whereIn('company_id', $request['company_id'])->find($request['application_id']);
    }

    /**
     * Get the details of a application service detail.
     *
     * @param int $id The Id of the service.
     * @return Model Returns the crm prospect service as an instance of the crmProspectService model.
     * @throws ModelNotFoundException Throws an exception if the crm prospect service with the specified id is not found.
     */
    private function getApplicationServiceDetails($id)
    {
        return $this->crmProspectService->findOrFail($id);
    }

    /**
     * get the company detail
     * 
     * @param $request
     *        application_id (int) ID of the application
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the company detail
     */
    public function getCompany($request): mixed
    {
        return $this->totalManagementApplications
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
                    ->where('total_management_applications.id', $request['application_id'])
                    ->whereIn('total_management_applications.company_id', $request['company_id'])
                    ->select('crm_prospects.id', 'crm_prospects.company_name')
                    ->get();
    }
    /**
     * list the KSM Reference number
     * 
     * @param $request
     *        application_id (int) ID of the application
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the KSM Reference number
     */
    public function ksmRefereneceNUmberDropDown($request): mixed
    {
        $companyId = $this->getCompanyIds($request);
        $ksmReferenceNumbers = $this->directrecruitmentApplications
        ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_applications.id')
        ->whereIn('directrecruitment_applications.crm_prospect_id', $companyId)
        ->whereNotNull('directrecruitment_application_approval.ksm_reference_number')
        ->select('directrecruitment_applications.id as directrecruitment_application_id', 'directrecruitment_application_approval.ksm_reference_number')
        ->get();
        return $ksmReferenceNumbers;
    }

    /**
     * Get the company IDs for the given request.
     *
     * @param array $request The request data containing the 'application_id' and 'company_id'.
     * @return array Returns an array of company IDs associated with the given 'application_id'.
     */
    private function getCompanyIds($request)
    {
        $companyId = $this->totalManagementApplications
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
                    ->where('total_management_applications.id', $request['application_id'])
                    ->whereIn('total_management_applications.company_id', $request['company_id'])
                    ->select('crm_prospects.id')
                    ->get()->toArray();
        return array_column($companyId, 'id');
    }

    /**
     * get the sector and valid until detail
     * 
     * @param $request
     *        prospect_id (int) ID of the prospect
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the sector and valid until detail
     */
    public function getSectorAndValidUntil($request): mixed
    {
        return $this->directrecruitmentApplications
                    ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_applications.id')
                    ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
                    ->where('directrecruitment_applications.crm_prospect_id', $request['prospect_id'])
                    ->where('directrecruitment_application_approval.ksm_reference_number', $request['ksm_reference_number'])
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id'])
                    ->select('crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'directrecruitment_application_approval.valid_until')
                    ->get();
    }
    /**
     * list the assigned workers of project
     * 
     * @param $request
     *        project_id (int) ID of the project
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the assigned workers of project
     */
    public function getAssignedWorker($request): mixed
    {
        return $this->workerEmployment
            ->leftjoin('workers', 'workers.id', 'worker_employment.worker_id')
            ->where('worker_employment.project_id', $request['project_id'])
            ->where('worker_employment.status', self::STATUS_ACTIVE)
            ->where('service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
            ->whereIn('workers.company_id', $request['company_id'])
            ->select('worker_employment.id','worker_employment.worker_id','workers.name','workers.passport_number')
            ->get();
    }   

    /**
     * Process the remove worker from the project
     * 
     * @param $request The request data containing remove worker details
     * 
     * @return array|bool Returns true if the remove worker is submitted successfully, otherwise returns an array with error details
     *                    Returns self::ERROR_UNAUTHORIZED if the user access invalid project
     * 
     * 
     * @see validateRemoveRequest()
     * @see getProjectWorker()
     * @see processRemoveWorker()
     * 
     */
    public function removeWorker($request): array|bool
    {
        $request = $this->enrichRequestWithUserDetails($request);

        $validationResult = $this->validateRemoveRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $workerDetails = $this->getProjectWorker($request);
        if($workerDetails == self::DEFAULT_VALUE){
            return self::ERROR_UNAUTHORIZED;
        }

        $this->processRemoveWorker($request);

        return true;
    }

    /**
     * Validate the remove worker request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateRemoveRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->removeValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * check the worker is exist or not in the provided project.
     *
     * @param $request
     *        company_id (array) ID of the user company
     *        worker_id ID of the worker
     *        project_id ID of the project
     * 
     * @return int Returns the count
     */
    private function getProjectWorker($request)
    {
        return $this->workerEmployment
            ->join('workers', function ($join) use ($request) {
                $join->on('workers.id', '=', 'worker_employment.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
            })
            ->where("worker_id", $request['worker_id'])
            ->where("project_id", $request['project_id'])
            ->where("service_type", Config::get('services.WORKER_MODULE_TYPE')[1])
            ->count();
    }
    /**
     * process the remove worker on provided request data
     *
     * @param array $request
     *              worker_id (array) ID of the worker
     *              project_id (int) ID of the project
     *              last_working_day (date) last working date of the project
     *              remove_date (date) removed date
     *              remarks (string) remarks text
     *              modified_by (int) ID of the user who modified the record
     * 
     * @return void
     * 
     * @see updateCurrentWorkerEmployment()
     * @see updateWorkerStatus()
     * 
     */
    private function processRemoveWorker($request){
        $this->updateCurrentWorkerEmployment($request);
        $this->updateWorkerStatus($request);
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
        $this->workerEmployment
            ->where("worker_id", $request['worker_id'])
            ->where("project_id", $request['project_id'])
            ->where("service_type", Config::get('services.WORKER_MODULE_TYPE')[1])
            ->update([
                'status' => self::DEFAULT_VALUE,
                'work_end_date' => $request['last_working_day'],
                'remove_date' => $request['remove_date'],
                'remarks' => $request['remarks']
            ]);
    }

    /**
     * Update the worker status based on provided request data
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              modified_by (int) modified user ID
     *
     * @return void
     * 
     */
    private function updateWorkerStatus($request)
    {
        $this->workers
            ->where('id', $request['worker_id'])
            ->update([
                'total_management_status' => self::WORKER_STATUS_ONBENCH,
                'modified_by' => $request['modified_by']
            ]);
    }

    /**
     * get the worker count
     * 
     * @param $applicationId, $prospectId
     * 
     * @return array Returns the worker count
     * 
     * @see getTotalManagementProjectIds()
     * @see getProjectWorkersCount()
     * 
     */
    public function getWorkerCount($applicationId, $prospectId): array
    {
        $projectIds = $this->getTotalManagementProjectIds($applicationId);
        $clientWorkersCount = $this->getProjectWorkersCount($prospectId, $projectIds);
        $fomnextWorkersCount = $this->getProjectWorkersCount(self::FOMNEXT_PROSPECT_ID, $projectIds);

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
     * Retrieve project worker count based on request data
     *
     * @param $prospectId
     * @param $projectIds
     * @return int Returns the project worker count
     */
    private function getProjectWorkersCount($prospectId, $projectIds)
    {
        return $this->workers
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id')
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
}
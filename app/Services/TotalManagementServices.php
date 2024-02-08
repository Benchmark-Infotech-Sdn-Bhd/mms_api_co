<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\Services;
use App\Models\Sectors;
use App\Models\TotalManagementApplications;
use App\Models\TotalManagementApplicationAttachments;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Services\AuthServices;

class TotalManagementServices
{
    public const ERROR_QUOTA = ['quotaError' => true];
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];

    public const DEFAULT_TRANSFER_FLAG = 0;
    public const CUSTOMER = 'Customer';
    public const TOTAL_MANAGEMENT_SERVICE_ID = 3;
    public const FILE_TYPE = 'proposal';
    public const PENDING_PROPOSAL = 'Pending Proposal';
    public const PROPOSAL_SUBMITTED = 'Proposal Submitted';
    public const DEFAULT_VALUE = 0;
    public const NOT_FROM_EXISTING = 0;

    /**
     * @var crmProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var crmProspectService
     */
    private CRMProspectService $crmProspectService;
    /**
     * @var crmProspectAttachment
     */
    private CRMProspectAttachment $crmProspectAttachment;
    /**
     * @var services
     */
    private Services $services;
    /**
     * @var sectors
     */
    private Sectors $sectors;
    /**
     * @var totalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var totalManagementApplicationAttachments
     */
    private TotalManagementApplicationAttachments $totalManagementApplicationAttachments;
    /**
     * @var directrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var directRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var storage
     */
    private Storage $storage;
    /**
     * @var authServices
     */
    private AuthServices $authServices;
    
    /**
     * TotalManagementServices Constructor method.
     *
     * @param CRMProspect $crmProspect Instance of the CRMProspect class
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class
     * @param CRMProspectAttachment $crmProspectAttachment Instance of the CRMProspectAttachment class
     * @param Services $services Instance of the Services class
     * @param Sectors $sectors Instance of the Sectors class
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class
     * @param TotalManagementApplicationAttachments $totalManagementApplicationAttachments  Instance of the TotalManagementApplicationAttachments class
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry  Instance of the DirectRecruitmentOnboardingCountry class
     * @param Storage $storage Instance of the Storage class
     * @param AuthServices $authServices Instance of the AuthServices class
     * 
     * @return void
     * 
     */
    public function __construct(
        CRMProspect                             $crmProspect, 
        CRMProspectService                      $crmProspectService, 
        CRMProspectAttachment                   $crmProspectAttachment, 
        Services                                $services, 
        Sectors                                 $sectors, 
        TotalManagementApplications             $totalManagementApplications, 
        TotalManagementApplicationAttachments   $totalManagementApplicationAttachments, 
        DirectrecruitmentApplications           $directrecruitmentApplications, 
        DirectRecruitmentOnboardingCountry      $directRecruitmentOnboardingCountry, 
        Storage                                 $storage, 
        AuthServices                            $authServices
    )
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->totalManagementApplicationAttachments = $totalManagementApplicationAttachments;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->storage = $storage;
        $this->authServices = $authServices;
    }
    /**
     * Validates the search request.
     *
     * @return array The validation rules for the input data
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * Validates the service request.
     *
     * @return array The validation rules for the input data
     */
    public function addServiceValidation(): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required',
            'contact_number' => 'required',
            'email' => 'required',
            'pic_name' => 'required',
            'from_existing' => 'required',
            'client_quota' => 'regex:/^[0-9]+$/|max:3',
            'fomnext_quota' => 'regex:/^[0-9]+$/|max:3',
            'initial_quota' => 'regex:/^[0-9]+$/',
            'service_quota' => 'regex:/^[0-9]+$/|max:3'
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListSearchRequest($request): array|bool
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
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAddServicehRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->addServiceValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateSubmitProposalRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->totalManagementApplications->rulesForSubmission());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Returns a paginated list of list based on the given search request.
     *
     * @param $request
     *        company_id (array) ID of the user company
     *        search (string) search parameter
     * 
     * @return mixed Returns The paginated list of application listing
     * 
     * @see applyCondition()
     * @see applyReferenceFilter()
     * @see applySearchFilter()
     * @see ListSelectColumns()
     * 
     */
    public function applicationListing($request): mixed
    {
        $validationResult = $this->validateListSearchRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $data = $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->leftJoin('total_management_project', 'total_management_project.application_id', 'total_management_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        });
        $data = $this->applyCondition($request,$data);
        $data = $this->applyReferenceFilter($request,$data);  
        $data = $this->applySearchFilter($request,$data);
        $data = $this->ListSelectColumns($data)
                    ->orderBy('total_management_applications.id', 'desc')
                    ->paginate(Config::get('services.paginate_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        company_id (array) ID of the user company
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->whereIn('crm_prospects.company_id', $request['company_id'])
		->where('crm_prospect_services.service_id', self::TOTAL_MANAGEMENT_SERVICE_ID)
        ->whereNull('crm_prospect_services.deleted_at');
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
                $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
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
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if(!empty($search)) {
                $query->where('crm_prospects.company_name', 'like', '%'.$search.'%');
            }
        });
    }
	
	/**
     * Select data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->selectRaw('total_management_applications.id, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, crm_prospect_services.from_existing, total_management_applications.status, total_management_applications.quota_applied, count(distinct total_management_project.id) as projects, count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
        ->groupBy('total_management_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.from_existing', 'total_management_applications.status', 'total_management_applications.quota_applied');
    }

    /**
     * Creates a new Prospect Service from the given request data.
     *
     * @param mixed $request The data of the prospect to be created. It should contain the following keys:
     *                       - id: (int) The ID of the prospect service.
     *                       - service_id: (string) The service id of the prospect.
     *                       - service_name: (string) The service name of the prospect.
     *                       - sector_id: (int) The sector id of the prospect.
     *                       - sector_name: (string) The sector name of the prospect.
     *                       - status: (string) The status of prospect.
     *                       - from_existing: (int) The from existing of prospect.
     *                       - client_quota: (int) The client quota of prospect.
     *                       - fomnext_quota: (int) The fomnext quota of prospect.
     *                       - initial_quota: (int) The initial quota of prospect.
     *                        - service_quota: (int) The service quota of prospect.
     *
     * @return mixed The created prospect service object. Check the documentation for the specific type of prospect service object.
     */
    private function createProspectService($request): mixed
    {
        return $this->crmProspectService->create([
            'crm_prospect_id'   => $request['id'],
            'service_id'        => $request['service_id'],
            'service_name'      => $request['service_name'],
            'sector_id'         => $request['sector'] ?? self::DEFAULT_VALUE,
            'sector_name'       => $request['sector_name'] ?? '',
            'status'            => $request['status'] ?? self::DEFAULT_VALUE,
            'from_existing'     => $request['from_existing'] ?? self::DEFAULT_VALUE,
            'client_quota'      => $request['client_quota'] ?? self::DEFAULT_VALUE,
            'fomnext_quota'     => $request['fomnext_quota'] ?? self::DEFAULT_VALUE,
            'initial_quota'     => $request['initial_quota'] ?? self::DEFAULT_VALUE,
            'service_quota'     => $request['service_quota'] ?? self::DEFAULT_VALUE,
        ]);
    }
    /**
     * create total management application.
     *
     * This method create the total managment application based on the provided userRole object and request data.
     *
     * @param array $request An array containing the request data, including the new id,from_existing,created_by,company_id fields.
     * @param $prospectService - The prospectService object representing the prospect service.
     * * @return void
     */
    private function createTotalManagementApplications($request,$prospectService)
    {
        $this->totalManagementApplications::create([
            'crm_prospect_id' => $request['id'],
            'service_id' => $prospectService->id,
            'quota_applied' => ($request['from_existing'] == self::NOT_FROM_EXISTING) ? ($prospectService->client_quota + $prospectService->fomnext_quota) : $prospectService->service_quota,
            'person_incharge' => '',
            'cost_quoted' => self::DEFAULT_VALUE,
            'status' => self::PENDING_PROPOSAL,
            'remarks' => '',
            'created_by' => $request["created_by"] ?? self::DEFAULT_VALUE,
            'company_id' => $request['company_id']
        ]);
    }
    /**
     * Creates a new service.
     *
     * This method creates a new service :
    
     *
     * @param array $request The request data containing the service and application detail.
     *
     * @return bool Returns true if the service was successfully created.
     *              Returns self::ERROR_QUOTA if service quota exceed the initial quota
     * 
     * @see validateAddServicehRequest()
     * @see createProspectService()
     * @see createTotalManagementApplications()
     * 
     */
    public function addService($request): bool|array
    {
        $validationResult = $this->validateAddServicehRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        if (isset($request['initial_quota']) && !empty($request['initial_quota'])) {
            if($request['initial_quota'] < $request['service_quota']) {
                return self::ERROR_QUOTA;
            }
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['company_id'] = $user['company_id'];

        $service = $this->services->findOrFail(Config::get('services.TOTAL_MANAGEMENT_SERVICE'));
        $request['service_id'] = $service->id;
        $request['service_name'] = $service->service_name;

        if(isset($request['sector']) && !empty($request['sector'])) {
            $sector = $this->sectors->findOrFail($request['sector']);
            $request['sector_name'] = $sector->sector_name ?? '';
        }

        $prospectService = $this->createProspectService($request);

        $this->createTotalManagementApplications($request,$prospectService);

        return true;
    }
    /**
     * Returns a quota for given request.
     *
     * @param $request
     *        company_id (array) ID of the user company
     *        prospect_id (int) ID of the prospect 
     * 
     * @return int Returns the quota count
     */
    public function getQuota($request): int
    {
        $directrecruitmentApplicationIds = $this->directrecruitmentApplications->whereIn('company_id', $request['company_id'])->where('crm_prospect_id', $request['prospect_id'])
                                            ->select('id')
                                            ->get()
                                            ->toArray();
        $applicationIds = array_column($directrecruitmentApplicationIds, 'id');
        $initialQuota = $this->directRecruitmentOnboardingCountry->whereIn('application_id', $applicationIds)->sum('utilised_quota');
        return $initialQuota;
    }
    /**
     * Retrieves a proposal based on the provided request.
     * 
     * @param $request
     *        id (int) ID of the application
     *        comapny_id (array) ID of the user company
     * 
     * @return mixed Returns the Application record with attachments
     */
    public function showProposal($request) : mixed
    {
        return $this->totalManagementApplications
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->where('total_management_applications.id', $request['id'])
        ->whereIn('total_management_applications.company_id', $request['company_id'])
        ->with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->select('total_management_applications.id', 'total_management_applications.quota_applied', 'total_management_applications.person_incharge', 'total_management_applications.cost_quoted', 'total_management_applications.remarks', 'crm_prospect_services.sector_name')->get();
    }
    /**
     * Submit the Proposal.
     * 
     * @param $request The request object containing the proposal data
     * 
     * @return bool|array Returns true if the Proposal submit is successful. Returns an error array if validation fails or any error occurs during the Proposal Submit process.
     *                    Returns self::ERROR_QUOTA if requested quota exceed the total quota
     *                    Returns self::ERROR_UNAUTHORIZED if user access invalid application
     * 
     * @see validateSubmitProposalRequest()
     * @see getApplicationDetails()
     * @see updateApplicationDetail()
     * @see uploadProposalAttachments()
     * 
     */
    public function submitProposal($request): bool|array
    {
        $validationResult = $this->validateSubmitProposalRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $applicationDetails = $this->getApplicationDetails($request->toArray());

        if(is_null($applicationDetails)){
            return self::ERROR_UNAUTHORIZED;
        }

        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        if($serviceDetails->from_existing == self::NOT_FROM_EXISTING) {
            $totalQuota = $serviceDetails->client_quota + $serviceDetails->fomnext_quota;
            if($totalQuota < $request['quota_requested']) {
                return self::ERROR_QUOTA;
            }
        }
        
        $params = $request->all();
        $params['modified_by'] = $user['id'];

        $this->updateApplicationDetail($applicationDetails, $params);

        $this->uploadProposalAttachments($request);

        return true;
    }
    /**
     * Update application based on the provided request.
     *
     * @param mixed $applicationDetails
     * @param $params
     *        quota_requested (int) requested quota
     *        person_incharge (string) person incharge
     *        cost_quoted (float) cost quoted
     *        remarks (string) remarks of application
     *        modified_by (int) ID of the user who modified the application
     */
    private function updateApplicationDetail($applicationDetails, $params)
    {
        $applicationDetails->quota_applied = $params['quota_requested'] ?? $applicationDetails->quota_applied;
        $applicationDetails->person_incharge = $params['person_incharge'] ?? $applicationDetails->person_incharge;
        $applicationDetails->cost_quoted = $params['cost_quoted'] ?? $applicationDetails->cost_quoted;
        $applicationDetails->status = self::PROPOSAL_SUBMITTED;
        $applicationDetails->remarks = $params['remarks'] ?? $applicationDetails->remarks;
        $applicationDetails->modified_by = $params['modified_by'];
        $applicationDetails->save();
    }
    /**
     * Upload attachments for the proposal.
     *
     * @param $request The request object containing the upload attachment data
     */
    private function uploadProposalAttachments($request)
    {
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/totalManagement/proposal/' . $fileName;
                
                $linode = Storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                
                $fileUrl = $linode->url($filePath);

                $this->totalManagementApplicationAttachments::create([
                    'file_id' => $request->id,
                    'file_name' => $fileName,
                    'file_type' => self::FILE_TYPE,
                    'file_url' => $fileUrl,
                ]);
            }
        }
    }
    /**
     * allocateQuota for application.
     * 
     * @param $request The request object containing the allocate Quota data
     * 
     * @return array|bool Returns true if the allocateQuota is successful. Returns an error array if validation fails or any error occurs during the allocateQuota process.
     *                    Returns self::ERROR_QUOTA if service quota exceed the initial quota
     *                    Returns self::ERROR_UNAUTHORIZED if user access invalid application
     * 
     * @see updateProspectService()
     * @see getApplicationDetails()
     * @see updateApplicationQuota()
     * 
     */
    public function allocateQuota($request): array|bool
    {
        if (isset($request['initial_quota']) && $request['initial_quota'] < $request['service_quota']) {
            return self::ERROR_QUOTA;
        }
    
        $prospectService = $this->getCrmProspectService($request);
        if (is_null($prospectService)) {
            return self::ERROR_UNAUTHORIZED;
        }
    
        $this->updateProspectService($prospectService, $request);
    
        $applicationDetails = $this->getApplicationDetails($request);
        if (is_null($applicationDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }
    
        $this->updateApplicationQuota($applicationDetails, $prospectService, $request);
    
        return true;
    }
    /**
     * Get CRM Prospect Service based on the provided request.
     *
     * @param array $request
     *              prospect_service_id (int) prospect service ID
     *              company_id (array) ID of the user company
     * 
     * @return mixed Returns prospect service record
     */
    private function getCrmProspectService(array $request)
    {
        return $this->crmProspectService->join('crm_prospects', function ($join) use ($request) {
            $join->on('crm_prospects.id', '=', 'crm_prospect_services.crm_prospect_id')
                ->whereIn('crm_prospects.company_id', $request['company_id']);
        })->select('crm_prospect_services.id', 'crm_prospect_services.crm_prospect_id', 'crm_prospect_services.service_id', 'crm_prospect_services.service_name', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.contract_type', 'crm_prospect_services.status', 'crm_prospect_services.from_existing', 'crm_prospect_services.client_quota', 'crm_prospect_services.fomnext_quota', 'crm_prospect_services.initial_quota', 'crm_prospect_services.service_quota', 'crm_prospect_services.air_ticket_deposit', 'crm_prospect_services.created_at', 'crm_prospect_services.updated_at', 'crm_prospect_services.deleted_at')->find($request['prospect_service_id']);
    }

    /**
     * Update Prospect Service details based on the provided request.
     *
     * @param mixed $prospectService
     * @param array $request
     *              from_existing (int) from existing
     *              client_quota (int) client quota
     *              fomnext_quota (int) fomnext quota
     *              initial_quota (int) initial quota
     *              service_quota (int) service quota
     */
    private function updateProspectService($prospectService, array $request)
    {
        $prospectService->from_existing = $request['from_existing'] ?? self::NOT_FROM_EXISTING;
        $prospectService->client_quota = $request['client_quota'] ?? $prospectService->client_quota;
        $prospectService->fomnext_quota = $request['fomnext_quota'] ?? $prospectService->fomnext_quota;
        $prospectService->initial_quota = $request['initial_quota'] ?? $prospectService->initial_quota;
        $prospectService->service_quota = $request['service_quota'] ?? $prospectService->service_quota;
        $prospectService->save();
    }

    /**
     * Get Application Details based on the provided request.
     *
     * @param array $request
     *              id (int) ID of the application
     *              company_id (array) ID of the user company
     * 
     * @return mixed Returns an application record
     */
    private function getApplicationDetails(array $request)
    {
        return $this->totalManagementApplications->whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Update Application Quota based on the provided Prospect Service and request.
     *
     * @param mixed $applicationDetails
     * @param mixed $prospectService
     * @param array $request
     */
    private function updateApplicationQuota($applicationDetails, $prospectService, array $request)
    {
        $applicationDetails->quota_applied = ($request['from_existing'] == self::NOT_FROM_EXISTING) ?
            ($prospectService->client_quota + $prospectService->fomnext_quota) :
            $prospectService->service_quota;

        $applicationDetails->save();
    }
    /**
     * Retrieves a service details based on the provided request.
     *
     * @param $request
     *        prospect_service_id (int) prospect service ID
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the prospect service record
     */
    public function showService($request) : mixed
    {
        return $this->crmProspectService
        ->join('crm_prospects', function ($join) use ($request) {
            $join->on('crm_prospects.id', '=', 'crm_prospect_services.crm_prospect_id')
                 ->whereIn('crm_prospects.company_id', $request['company_id']);
        })
        ->select('crm_prospect_services.id', 'crm_prospect_services.crm_prospect_id', 'crm_prospect_services.service_id', 'crm_prospect_services.service_name', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.contract_type', 'crm_prospect_services.status', 'crm_prospect_services.from_existing', 'crm_prospect_services.client_quota', 'crm_prospect_services.fomnext_quota', 'crm_prospect_services.initial_quota', 'crm_prospect_services.service_quota', 'crm_prospect_services.air_ticket_deposit', 'crm_prospect_services.created_at', 'crm_prospect_services.updated_at', 'crm_prospect_services.deleted_at')
        ->find($request['prospect_service_id']);
    }
}
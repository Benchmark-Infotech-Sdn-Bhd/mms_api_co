<?php

namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\Services;
use App\Models\Sectors;
use App\Models\DirectRecruitmentApplicationStatus;
use App\Models\TotalManagementApplications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class DirectRecruitmentServices
{
    public const DEFAULT_VALUE = 0;
	public const SERVICE_ID_DEFAULT = 1;
	public const SERVICE_NAME_DR = 'Direct Recruitment';
	public const CONTRACT_TYPE_DEFAULT = 'No Contract';
	public const CUSTOMER = 'Customer';

    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

    public const ATTACHMENT_FILE_TYPE_PROSPECT = 'prospect';
    public const ATTACHMENT_FILE_TYPE_PROPOSAL = 'proposal';
    public const TEXT_DOCUMENT_CHECKLIST = 'Document Checklist';
    public const TEXT_TOTAL_MANAGEMENT = 'Total Management';
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 'Pending';
    public const STATUS_SUBMITTED = 'Submitted';
    public const DIRECT_RECRUITMENT_SERVICE_ID = 1;
    public const TOTAL_MANAGEMENT_SERVICE_ID = 3;
    public const FROM_EXISTING = 1;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectrecruitmentApplicationAttachments
     */
    private DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentApplicationChecklistServices
     */
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;
    /**
     * @var CRMProspectAttachment
     */
    private CRMProspectAttachment $crmProspectAttachment;
    /**
     * @var Services
     */
    private Services $services;
    /**
     * @var Sectors
     */
    private Sectors $sectors;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * @var DirectRecruitmentApplicationStatus
     */
    private DirectRecruitmentApplicationStatus $directRecruitmentApplicationStatus;
    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;

    /**
     * DirectRecruitmentServices constructor.
     * 
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class
     * @param DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments Instance of the DirectrecruitmentApplicationAttachments class
     * @param Storage $storage Instance of the Storage class
     * @param CRMProspect $crmProspect Instance of the CRMProspect class
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class
     * @param CRMProspectAttachment $crmProspectAttachment Instance of the CRMProspectAttachment class
     * @param Services $services Instance of the Services class
     * @param Sectors $sectors Instance of the Sectors class
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices Instance of the DirectRecruitmentApplicationChecklistServices class
     * @param ApplicationSummaryServices $applicationSummaryServices Instance of the ApplicationSummaryServices class
     * @param DirectRecruitmentApplicationStatus $directRecruitmentApplicationStatus Instance of the DirectRecruitmentApplicationStatus class
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class
     * 
     * @return void
     * 
     */
    public function __construct(
        DirectrecruitmentApplications                   $directrecruitmentApplications, 
        DirectrecruitmentApplicationAttachments         $directrecruitmentApplicationAttachments, 
        Storage                                         $storage, 
        CRMProspect                                     $crmProspect, 
        CRMProspectService                              $crmProspectService, 
        CRMProspectAttachment                           $crmProspectAttachment, 
        Services                                        $services, 
        Sectors                                         $sectors,
        DirectRecruitmentApplicationChecklistServices   $directRecruitmentApplicationChecklistServices, 
        ApplicationSummaryServices                      $applicationSummaryServices, 
        DirectRecruitmentApplicationStatus              $directRecruitmentApplicationStatus, 
        TotalManagementApplications                     $totalManagementApplications
    )
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directrecruitmentApplicationAttachments = $directrecruitmentApplicationAttachments;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->directRecruitmentApplicationStatus = $directRecruitmentApplicationStatus;
        $this->totalManagementApplications = $totalManagementApplications;
    }       
    /**
     * validate the add service request data
     * 
     * @return array The array containing the validation rules.
     */
    public function addServiceValidation(): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required',
            'contact_number' => 'required',
            'email' => 'required',
            'pic_name' => 'required',
            'sector' => 'required',
            'contract_type' => 'required',
            'service_id' => 'required'
        ];
    }
    /**
     * validate the proposal submission request data
     * 
     * @return array The array containing the validation rules.
     */
    public function proposalSubmissionValidation(): array
    {
        return [
            'id' => 'required',
            'quota_applied' => 'required|regex:/^[0-9]+$/|max:3',
            'cost_quoted' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'person_incharge' => 'required',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'

        ];
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
     * Validate the add service request data.
     *
     * @param object $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAddServicehRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->addServiceValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        return true;
    }

    /**
     * Validate the List application request data.
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
     * Validate the submit proposal request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateSubmitProposalRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->proposalSubmissionValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Retrieve crm prospect record.
     *
     * @param array $request
     *              id (int) ID of the crm prospect
     * 
     * @return mixed Returns the project record
     */
    private function getCrmProspect($request)
    {
        return $this->crmProspect->find($request['id']);
    }

    /**
     * Retrieve Directrecruitment Application record.
     *
     * @param array $request
     *              id (int) ID of the application
     * 
     * @return mixed Returns the Directrecruitment Application record
     */
    private function getDirectrecruitmentApplications($request)
    {
        return $this->directrecruitmentApplications::find($request['id']);
    }

    /**
     * Retrieve the active service count.
     *
     * @param int $crmProspectId id of the crm prospect
     * 
     * @return int Returns the active service count
     */
    private function getActiveServiceCount($crmProspectId)
    {
        return $this->crmProspectService->where('crm_prospect_id', $crmProspectId)
        ->where('status', self::STATUS_ACTIVE)
        ->where('service_id', self::DIRECT_RECRUITMENT_SERVICE_ID)
        ->count('id');
    }                          

    /**
     * Creates a new Prospect Service from the given request data.
     *
     * @param mixed $request The data of the prospect to be created. It should contain the following keys:
     *                       - id: (int) The ID of the prospect service.
     *                       - service_id: (string) The service id of the prospect.
     *                       - service_name: (string) The service name of the prospect.
     *                       - sector: (int) The sector id of the prospect.
     *                       - sector_name: (string) The sector name of the prospect.
     *                       - contract_type: (string) The contract type of the prospect.
     *                       - status: (string) The status of prospect.
     *
     * @return mixed The created prospect service object.
     */
    private function createProspectService($request): mixed
    {
        $service = $this->services->findOrFail($request['service_id']);
        $sector = $this->sectors->findOrFail($request['sector']);

        return $this->crmProspectService->create([
            'crm_prospect_id'   => $request['id'],
            'service_id'        => $request['service_id'] ?? self::SERVICE_ID_DEFAULT,
            'service_name'      => $service->service_name ?? self::SERVICE_NAME_DR,
            'sector_id'         => $request['sector'] ?? self::DEFAULT_VALUE,
            'sector_name'       => $sector->sector_name,
            'contract_type'     => $service->id == self::SERVICE_ID_DEFAULT ? $request['contract_type'] : self::CONTRACT_TYPE_DEFAULT,
            'status'            => $request['status'] ?? self::DEFAULT_VALUE
        ]);
    }

    /**
     * Upload attachment of CRM Prospect.
     *
     * @param array $request
     *              attachment (file) 
     * @param int $prospectServiceId Id of the crm prospect
     * 
     * @return void
     */
    private function uploadCrmProspectFiles($request, $prospectServiceId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/crm/prospect/' . $request['sector_type']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->crmProspectAttachment->create([
                    "file_id" => $request['id'],
                    "prospect_service_id" => $prospectServiceId,
                    "file_name" => $fileName,
                    "file_type" => self::ATTACHMENT_FILE_TYPE_PROSPECT,
                    "file_url" =>  $fileUrl          
                ]);  
            }
        }
    }

    /**
     * Creates a new Prospect Service from the given request data.
     *
     * @param mixed $request The data of the prospect to be created. It should contain the following keys:
     *                       - id: (int) The ID of the crm prospect.
     *                       - created_by: (int) The ID of the user who created the Directrecruitment Applications.
     *                       - company_id: (int) The user company ID
     * @param int $prospectServiceId Id of the prospect service
     *
     * @return void
     */
    private function CreateDirectrecruitmentApplications($request, $prospectServiceId): void
    {
        $this->directrecruitmentApplications::create([
            'crm_prospect_id' => $request['id'],
            'service_id' => $prospectServiceId,
            'quota_applied' => self::DEFAULT_VALUE,
            'person_incharge' => '',
            'cost_quoted' => self::DEFAULT_VALUE,
            'status' => Config::get('services.PENDING_PROPOSAL'),
            'remarks' => '',
            'created_by' => $request["created_by"] ?? self::DEFAULT_VALUE,
            'company_id' => $request['company_id'] ?? self::DEFAULT_VALUE
        ]);
    }

    /**
     * Apply the "customer" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the user reference ID
     *
     * @return void
     */
    private function applyListCustomerFilter($query, $request)
    {
        if ($request['user']['user_type'] == self::CUSTOMER) {
            $query->where('directrecruitment_applications.crm_prospect_id', '=', $request['user']['reference_id']);
        }
    }
	
	/**
     * Apply the "search" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the search param
     *
     * @return void
     */
    private function applyListSearchFilter($query, $request)
    {
        if(!empty($request['search'])) {
            $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
        }
    }
	
	/**
     * Apply the "status" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the filter param
     *
     * @return void
     */
    private function applyListStatusFilter($query, $request)
    {
        if(!empty($request['filter'])) {
            $query->where('directrecruitment_applications.status', $request['filter']);
        }
    }
	
	/**
     * Apply the "contract type" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the contract_type param
     *
     * @return void
     */
    private function applyListContractTypeFilter($query, $request)
    {
        if(!empty($request['contract_type'])) {
            $query->where('crm_prospect_services.contract_type', $request['contract_type']);
        }
    }

    /**
     * Upload attachment of applicatin.
     *
     * @param array $request
     *              attachment (file)
     * @return void
     */
    private function uploadDirectRecruitmentFiles($request): void
    {
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/proposal/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directrecruitmentApplicationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => self::ATTACHMENT_FILE_TYPE_PROPOSAL,
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
    }
	
	
	/**
     * Update the application checklist based on the provided request.
     *
     * @param $request
     *        id (int) id of the application
	 * @param object $user
     * 
     * @return void
     * 
     */
    private function updateApplicationChecklist($request,$user)
    {
        $this->directRecruitmentApplicationChecklistServices->create(
                ['application_id' => $request['id'],
                'item_name' => self::TEXT_DOCUMENT_CHECKLIST,
                'application_checklist_status' => self::STATUS_PENDING,
                'created_by' => $user['id']]
            );
    }
	
	/**
     * update the CrmProspectService based on the provided request.
     *
     * @param $serviceId Id of the serviceData
     * 
     * @return void
     */
    private function updateCrmProspectService($serviceId)
	{
	    $serviceData = $this->crmProspectService->findOrFail($serviceId);
        $serviceData->status = self::STATUS_ACTIVE;
        $serviceData->save();
    }
	
	/**
     * update the application summary based on the provided request.
     *
     * @param $request
	 * @param $user
	 *
     * @return void
     */
    private function updateApplicationSummaryStatus($request,$user)
	{
	    $input['application_id'] = $request['id'];
        $input['created_by'] = $user['id'];
        $input['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[1];
        $input['status'] = self::STATUS_SUBMITTED;
        $this->applicationSummaryServices->updateStatus($input);
    }

    /**
     * Apply the "condition" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the company_id param
     *
     * @return void
     */
    private function applyConditionTotalManagementListFilter($query, $request)
    {
	    $query->whereIn('crm_prospects.company_id', $request['company_id'])
		    ->where('crm_prospect_services.service_id', self::TOTAL_MANAGEMENT_SERVICE_ID)
			->where('crm_prospect_services.from_existing', self::FROM_EXISTING)
			->where('crm_prospect_services.deleted_at', NULL);
    }
	
	/**
     * Apply the "search" filter to the query
     *
     * @param object $query The query builder instance
     * @param array $request The request data containing the user reference_id param
     *
     * @return void
     */
    private function applySearchTotalManagementListFilter($query, $request)
    {
	    if ($request['user']['user_type'] == self::CUSTOMER) {
            $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
        }
	    if(!empty($request['search'])) {
            $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Validate the total management list request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateTotalManagementListRequest($request): array|bool
    {
	    $search = $request['search'] ?? '';
        if(!empty($search)){
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
     * Create a new service
     * 
     * @param $request the request data containing the new service data
     * 
     * @return bool|array Returns true if the service was successfully created.
     *                    Returns InvalidUser if the company is not mapped with user company
     */
    public function addService($request): bool|array
    {
        $validationResult = $this->validateAddServicehRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $crmCompany = $this->getCrmProspect($request);
        if($crmCompany['company_id'] != $request['company_id']) {
            return [
                'InvalidUser' => true 
            ];
        }

        $prospectService = $this->createProspectService($request);

        $prospectServiceId = $prospectService->id ?? '';
        if(!empty($prospectServiceId)){
            $this->uploadCrmProspectFiles($request, $prospectServiceId);
            $this->CreateDirectrecruitmentApplications($request, $prospectServiceId);
        }
        
        return true;
    }
    /**
     * Returns a paginated list of application based on the given search request.
     * 
     * @param $request
     * 
     * @return mixed Returns The paginated list of application listing
     */
    public function applicationListing($request): mixed
    {
        $validationResult = $this->validateListSearchRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->directrecruitmentApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
        ->leftJoin('direct_recruitment_application_status', 'direct_recruitment_application_status.id', 'directrecruitment_applications.status')
        ->where('crm_prospect_services.service_id', self::DIRECT_RECRUITMENT_SERVICE_ID)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->whereIn('directrecruitment_applications.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $this->applyListCustomerFilter($query, $request);
            $this->applyListSearchFilter($query, $request);
            $this->applyListStatusFilter($query, $request);
            $this->applyListContractTypeFilter($query, $request);
        })
        ->select('directrecruitment_applications.id', 'directrecruitment_applications.approval_flag', 'crm_prospects.id as prospect_id', 'crm_prospect_services.id as prospect_service_id','crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospect_services.contract_type as type', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.service_name', 'directrecruitment_applications.quota_applied as applied_quota', 'direct_recruitment_application_status.status_name as status', 'crm_prospect_services.status as service_status', 'directrecruitment_applications.onboarding_flag')
        ->withCount(['fwcms'])
        ->with(['fwcms' => function ($query) {
            $query->leftJoin('application_interviews', 'application_interviews.ksm_reference_number', 'fwcms.ksm_reference_number')
            ->leftJoin('levy', 'levy.ksm_reference_number', 'fwcms.ksm_reference_number')
            ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.ksm_reference_number', 'levy.new_ksm_reference_number')
            ->select('fwcms.application_id')
            ->selectRaw("(CASE WHEN(levy.id IS NULL) THEN fwcms.ksm_reference_number WHEN(levy.id IS NOT NULL) THEN levy.new_ksm_reference_number ELSE fwcms.ksm_reference_number END) as ksm_reference_number, (CASE WHEN(directrecruitment_application_approval.id IS NOT NULL) THEN 'Approval Completed' WHEN(directrecruitment_application_approval.id IS NULL AND levy.id IS NOT NULL) THEN 'Levy Paid' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Scheduled') THEN 'Interview Scheduled' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Completed') THEN 'Interview Completed' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Approved') THEN 'Interview Approved' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Approved') THEN 'FWCMS Approved' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Rejected') THEN 'FWCMS Rejected' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Query') THEN 'FWCMS Query' ELSE 'FWCMS Submitted' END) as status");
        }])
        ->distinct('directrecruitment_applications.id')
        ->orderBy('directrecruitment_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }   

     /**
     * Retrieves a proposal based on the provided request.
     * 
     * @param  array $request the request data containing the below params
     *        company_id (array) user company Id's
     *        id (int) id of the application
     * 
     * @return mixed Returns the Application record with attachments
     */
    public function showProposal($request) : mixed
    {
        return $this->directrecruitmentApplications::with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('id', $request['id'])
        ->whereIn('company_id', $request['company_id'])
        ->first();
    }
    /**
     * Submit the Proposal
     * 
     * @param $request The request object containing the proposal data
     * 
     * @return array Returns an if submitted, array with two keys: 'isUpdated' and 'message' otherwise Returns an error array if validation fails or any error occurs during the Proposal Submit process.
     *                    Returns InvalidUser if application not mapped with user
     *                    Returns activeServiceerror if service is active in previous process 
     *               
     */
    public function submitProposal($request): array
    { 
        $validationResult = $this->validateSubmitProposalRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 

        $data = $this->getDirectrecruitmentApplications($request);
        if(is_null($data) || $data->company_id != $user['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        $activeServiceCount = $this->getActiveServiceCount($data->crm_prospect_id);
        if($activeServiceCount > self::DEFAULT_VALUE) {
            return [
                'activeServiceerror' => true
            ];
        }
        if($data->status != Config::get('services.APPROVAL_COMPLETED')){
            $input['status'] = Config::get('services.PROPOSAL_SUBMITTED');
        }
        
        $this->uploadDirectRecruitmentFiles($request);

        $res = $data->update($input);

        if($res){
            $this->updateApplicationChecklist($request,$user);
        }

        $this->updateCrmProspectService($data->service_id);
        $this->updateApplicationSummaryStatus($request,$user);

        return  [
            "isUpdated" => $res,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Delete the attachment
     * 
     * @param $request the request data containing the company_id and id
     * 
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->directrecruitmentApplicationAttachments::join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_application_attachments.file_id', '=', 'directrecruitment_applications.id')
                 ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })->select('directrecruitment_application_attachments.id', 'directrecruitment_application_attachments.file_id', 'directrecruitment_application_attachments.file_name', 'directrecruitment_application_attachments.file_type', 'directrecruitment_application_attachments.file_url', 'directrecruitment_application_attachments.created_by', 'directrecruitment_application_attachments.modified_by', 'directrecruitment_application_attachments.created_at', 'directrecruitment_application_attachments.updated_at', 'directrecruitment_application_attachments.deleted_at')
        ->find($request['id']);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * Update the status
     * 
     * @param $request the request data containing the id and status
     * 
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function updateStatus($request) : array
    {
        $directrecruitmentApplications = $this->directrecruitmentApplications->find($request['id']);
        if(is_null($directrecruitmentApplications)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        if($directrecruitmentApplications->status != Config::get('services.APPROVAL_COMPLETED')){
            $directrecruitmentApplications->status = $request['status'];
        }
        return [
            "isUpdated" => $directrecruitmentApplications->save() == 1,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
    /**
     * list the status name of application
     * 
     * @return mixed Returns the application status
     */
    public function dropDownFilter() : mixed
    {
        return $this->directRecruitmentApplicationStatus->where('status', self::STATUS_ACTIVE)
                    ->select('id', 'status_name')
                    ->get();
    }
    /**
     * Returns a paginated list of total management application based on the given search request.
     * 
     * @param $request the request data containing the search param and company_id
     * 
     * @return mixed Returns a paginated list of application
     */
    public function totalManagementListing($request): mixed
    {
        $validationResult = $this->validateTotalManagementListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->leftJoin('total_management_project', 'total_management_project.application_id', 'total_management_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', self::TEXT_TOTAL_MANAGEMENT)
            ->where('worker_employment.transfer_flag', self::DEFAULT_VALUE)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        })
        ->where(function ($query) use ($request) {
            $this->applyConditionTotalManagementListFilter($query, $request);
            $this->applySearchTotalManagementListFilter($query, $request);
        })
        ->selectRaw('total_management_applications.id, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, crm_prospect_services.from_existing, total_management_applications.status, count(distinct total_management_project.id) as projects, count(distinct workers.id) as workers')
        ->groupBy('total_management_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.from_existing', 'total_management_applications.status')
        ->orderBy('total_management_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }        
}
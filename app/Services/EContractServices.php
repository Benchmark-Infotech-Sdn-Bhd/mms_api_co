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
use App\Models\EContractApplications;
use App\Models\EContractApplicationAttachments;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentOnboardingCountry;

class EContractServices
{
    public const SERVICE_TYPE = 'e-Contract';
    public const CUSTOMER = 'Customer';
    public const PROPOSAL = 'proposal';
    public const PROPOSAL_SUBMITTED = 'Proposal Submitted';
    public const PROSPECT_SERVICE = 'prospect service';
    public const PENDING_PROPOSAL = 'Pending Proposal';
    public const EMPLOYMENT_TRANSFER_FLAG = 0;
    public const PROSPECT_SERVICES_ID = 2;
    public const APPLICATION_COST_QUOTED = 0;
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const UNAUTHORIZED_ERROR = 'Unauthorized';

    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => self::UNAUTHORIZED_ERROR];

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
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;

    /**
     * @var EContractApplicationAttachments
     */
    private EContractApplicationAttachments $eContractApplicationAttachments;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * Constructor method.
     * 
     * @param CRMProspect $crmProspect Instance of the CRMProspect class.
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class.
     * @param CRMProspectAttachment $crmProspectAttachment Instance of the CRMProspectAttachment class.
     * @param Services $services Instance of the Services class.
     * @param Sectors $sectors Instance of the Sectors class.
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class.
     * @param EContractApplicationAttachments $eContractApplicationAttachments Instance of the EContractApplicationAttachments class.
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry Instance of the DirectRecruitmentOnboardingCountry class.
     * @param Storage $storage Instance of the Storage class.
     */
    public function __construct(
        CRMProspect                            $crmProspect, 
        CRMProspectService                     $crmProspectService, 
        CRMProspectAttachment                  $crmProspectAttachment, 
        Services                               $services, 
        Sectors                                $sectors, 
        EContractApplications                  $eContractApplications, 
        EContractApplicationAttachments        $eContractApplicationAttachments, 
        DirectrecruitmentApplications          $directrecruitmentApplications, 
        DirectRecruitmentOnboardingCountry     $directRecruitmentOnboardingCountry, 
        Storage                                $storage
    )
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->eContractApplications = $eContractApplications;
        $this->eContractApplicationAttachments = $eContractApplicationAttachments;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->storage = $storage;
    }

    /**
     * Creates the validation rules for application search.
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
     * Creates the validation rules for creating a new service.
     *
     * @return array The array containing the validation rules.
     */
    public function addServiceValidation(): array
    {
        return [
            'prospect_id' => 'required',
            'company_name' => 'required',
            'sector_id' => 'required',
            'sector_name' => 'required',
            'fomnext_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'air_ticket_deposit' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
        ];
    }

    /**
     * Creates the validation rules for allocate the quota.
     *
     * @return array The array containing the validation rules.
     */
    public function allocateQuotaValidation(): array
    {
        return [
            'prospect_service_id' => 'required',
            'fomnext_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'air_ticket_deposit' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
        ];
    }

    /**
     * Returns a paginated list of application based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid. Otherwise, returns a paginated list of application.
     */
    public function applicationListing($request): mixed
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $validationResult = $this->applicationListValidateRequest($request);
            if (is_array($validationResult)) {
                return $validationResult;
            }
        }

        return $this->eContractApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'e-contract_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        ->leftJoin('e-contract_project', 'e-contract_project.application_id', 'e-contract_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $this->applyWorkerEmploymentTableFilter($query);
        })
        ->leftJoin('workers', function($query) {
            $this->applyWorkersTableFilter($query);
        })
        ->where(function ($query) use ($request) {
            $this->applyServiceFilter($query, $request);
        })
        ->where(function ($query) use ($request) {
            $this->applyUserFilter($query, $request);
        })
        ->where(function ($query) use ($request) {
            $this->applySearchFilter($query, $request);
        })
        ->selectRaw('`e-contract_applications`.`id`, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, `e-contract_applications`.`status`, `e-contract_applications`.`quota_requested`, count(distinct `e-contract_project`.`id`) as projects, count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
        ->groupBy('e-contract_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'e-contract_applications.status', 'e-contract_applications.quota_requested')
        ->orderBy('e-contract_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    
    /**
     * Apply the "worker employment" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     *
     * @return void
     */
    private function applyWorkerEmploymentTableFilter($query)
    {
        $query->on('worker_employment.project_id','=','e-contract_project.id')
            ->where('worker_employment.service_type', self::SERVICE_TYPE)
            ->where('worker_employment.transfer_flag', self::EMPLOYMENT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date');
    }
    
    /**
     * Apply the "workers" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     *
     * @return void
     */
    private function applyWorkersTableFilter($query)
    {
        $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
    }
    
    /**
     * Apply the "crm prospect service" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyServiceFilter($query, $request)
    {
        $query->where('crm_prospect_services.service_id', self::PROSPECT_SERVICES_ID)
            ->where('crm_prospect_services.deleted_at', NULL)
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
    }
    
    /**
     * Apply the "user" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the user reference id
     *
     * @return void
     */
    private function applyUserFilter($query, $request)
    {
        if ($request['user']['user_type'] == self::CUSTOMER) {
            $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
        }
    }
    
    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     */
    private function applySearchFilter($query, $request)
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Creates a new service from the given request data.
     * 
     * @param array $request The array containing service data.
     *                      The array should have the following keys:
     *                      - prospect_id: The prospect id of the service.
     *                      - service_id: The service id of the service.
     *                      - service_name: The service name of the service.
     *                      - sector_id: The sector id of the service.
     *                      - sector_name: The sector name of the service.
     *                      - status: The status of the service.
     *                      - fomnext_quota: The fomnext quota of the service.
     *                      - air_ticket_deposit: The ticket deposit of the service.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if prospect company is null.
     * - "isSubmit": A boolean indicating if the crm prospect service, e-contract applications was successfully created.
     */
    public function addService($request): bool|array
    {
        $validationResult = $this->addServiceValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $prospectCompany = $this->getCrmProspectDetails($request);
        if (is_null($prospectCompany)) {
            return self::ERROR_UNAUTHORIZED;
        }
        
        $service = $this->services->find($request['service_id']);

        $prospectService = $this->createCrmProspect($service, $request);

        $this->updateCrmProspectServiceAttachments($request, $prospectService->id);

        $this->createEContractApplications($request, $prospectService->id);

        return true;
    }

    /**
     * Updates the proposal data with the given request.
     * 
     * @param array $request The array containing application data.
     *                      The array should have the following keys:
     *                      - quota_requested: (string) The updated application quota requested.
     *                      - person_incharge: (string) The updated application person incharge.
     *                      - cost_quoted: (int) The updated application cost quoted.
     *                      - remarks: (string) The updated application remarks.
     *                      - modified_by: (int) The updated application modified by.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if application details is null.
     * - "isSubmit": A boolean indicating if the application details was successfully updated.
     */
    public function submitProposal($request): bool|array
    {
        $validationResult = $this->submitProposalValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        
        $applicationDetails = $this->showEContractApplications($request);
        if (is_null($applicationDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }
        
        $this->updateEContractApplications($applicationDetails, $request);

        $this->updateSubmitProposalAttachments($request, $request['id']);

        return true;
    }

    /**
     * Show the e-contract applications with related prospect services.
     * 
     * @param array $request The request data containing e-contract applications id,  company_id
     * @return mixed Returns the e-contract applications details with related prospect services.
     */
    public function showProposal($request): mixed
    {
        return $this->eContractApplications
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        ->where('e-contract_applications.id', $request['id'])
        ->whereIn('e-contract_applications.company_id', $request['company_id'])
        ->with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->select('e-contract_applications.id', 'e-contract_applications.quota_requested', 'e-contract_applications.person_incharge', 'e-contract_applications.cost_quoted', 'e-contract_applications.remarks', 'crm_prospect_services.sector_name')->get();
    }

    /**
     * Updates the quota details with the given request.
     * 
     * @param array $request The array containing quota details.
     *                       The array should have the following keys:
     *                      - fomnext_quota: (string) The updated fomnext quota.
     *                      - air_ticket_deposit: (string) The updated air ticket deposit.
     *                      - cost_quoted: (int) The updated application cost quoted.
     *                      - remarks: (string) The updated application remarks.
     *                      - modified_by: (int) The updated application modified by.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if service details is null.
     * - "isAllocate": A boolean indicating if the quota details was successfully updated.
     */
    public function allocateQuota($request): bool|array
    {
        $validationResult = $this->allocateQuotaValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $serviceDetails = $this->showCrmProspect($request);
        if (is_null($serviceDetails)) {
            return self::ERROR_UNAUTHORIZED;
        } 
        
        $this->updateCrmProspectServiceDetails($serviceDetails, $request);
        
        $this->updateEContractApplicationsFomnextQuota($request);

        return true;
    }

    /**
     * Show the crm prospect service with related crm prospects.
     * 
     * @param array $request The request data containing crm prospect services id,  company_id
     * @return mixed Returns the crm prospect service details with related crm prospects.
     */
    public function showService($request): mixed
    {
        return $this->showCrmProspect($request);
    }
    
    /**
     * Upload attachment of crm prospect service.
     *
     * @param array $request The request data containing prospect service attachments
     * @param int $prospectServiceId The attachments was upload against the prospect service Id
     */
    public function updateCrmProspectServiceAttachments($request, $prospectServiceId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/crm/prospect/' . $request['service_name'] . '/' . $request['sector_name']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $linode->url($filePath);
                $this->crmProspectAttachment->create([
                    "file_id" => $request['prospect_id'],
                    "prospect_service_id" => $prospectServiceId,
                    "file_name" => $fileName,
                    "file_type" => self::PROSPECT_SERVICE,
                    "file_url" =>  $fileUrl          
                ]);  
            }
        }
    }
    
    /**
     * Creates a new applications object and persists it in the database.
     *
     * @param array $request The array containing applications data.
     *                      The array should have the following keys:
     *                      - prospect_id: The prospec id of the application.
     *                      - fomnext_quota: The fomnext quota of the application.
     *                      - created_by: The ID of the user who created the application.
     *                      - company_id: The ID of the company the branch belongs to.
     *
     * @param int $prospectServiceId The application was created against the prospect service Id
     */
    public function createEContractApplications($request, $prospectServiceId): void
    {
        $this->eContractApplications::create([
            'crm_prospect_id' => $request['prospect_id'],
            'service_id' => $prospectServiceId,
            'quota_requested' => $request['fomnext_quota'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'person_incharge' => '',
            'cost_quoted' => self::APPLICATION_COST_QUOTED,
            'status' => self::PENDING_PROPOSAL,
            'remarks' => '',
            'created_by' => $request["created_by"] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by' => $request["created_by"] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'company_id' => $request['company_id']
        ]);
    }

    /**
     * Upload attachment of submit proposal.
     *
     * @param array $request The request data containing proposal attachments
     * @param int $applicationId The attachments was upload against the application Id
     */
    public function updateSubmitProposalAttachments($request, $applicationId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/proposal/' . $applicationId . '/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $linode->url($filePath);
                $this->eContractApplicationAttachments::create([
                    "file_id" => $applicationId,
                    "file_name" => $fileName,
                    "file_type" => self::PROPOSAL,
                    "file_url" =>  $fileUrl, 
                    "created_by" => $request['modified_by'],
                    "modified_by" => $request['modified_by']
                ]);  
            }
        }
    }

    /**
     * Updates the application quota details with the given request.
     * 
     * @param array $request Array with 'id' and 'fomnext quota' keys
     */
    public function updateEContractApplicationsFomnextQuota($request): void
    {
        $applicationDetails = $this->eContractApplications->findOrFail($request['id']);
        $applicationDetails->quota_requested = $request['fomnext_quota'] ?? $serviceDetails->fomnext_quota;
        $applicationDetails->save();
    }
    
    /**
     * Show the crm prospect service with related prospect.
     * 
     * @param array $request The request data containing crm prospect service id, company id
     * @return mixed Returns the crm prospect service with related prospect.
     */
    public function showCrmProspect($request): mixed
    {
        return $this->crmProspectService
        ->join('crm_prospects', function($query) use($request) {
            $query->on('crm_prospects.id','=','crm_prospect_services.crm_prospect_id')
            ->whereIn('crm_prospects.company_id', $request['company_id']);
        })
        ->select('crm_prospect_services.id', 'crm_prospect_services.crm_prospect_id', 'crm_prospect_services.service_id', 'crm_prospect_services.service_name', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.contract_type', 'crm_prospect_services.status', 'crm_prospect_services.from_existing', 'crm_prospect_services.client_quota', 'crm_prospect_services.fomnext_quota', 'crm_prospect_services.initial_quota', 'crm_prospect_services.service_quota', 'crm_prospect_services.air_ticket_deposit', 'crm_prospect_services.created_at', 'crm_prospect_services.updated_at', 'crm_prospect_services.deleted_at')
        ->find($request['prospect_service_id']);
    }
    
    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function applicationListValidateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->searchValidation());
        if ($validator->fails()) {
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function addServiceValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->addServiceValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }
    
    /**
     * Get the crm prospect based on the given request data.
     *
     * @param array $request The request data containing the company ID and prospect ID.
     * @return mixed Returns the crm prospect matching the given company ID and prospect ID,
     *               or null if no matching crm prospect is found.
     */
    private function getCrmProspectDetails($request): mixed
    {
        return $this->crmProspect->where('company_id', $request['company_id'])
        ->find($request['prospect_id']);
    }
    
    /**
     * Creates a new service from the given request data.
     * 
     * @param array $request The array containing service data.
     *                      The array should have the following keys:
     *                      - prospect_id: The prospect id of the service.
     *                      - service_id: The service id of the service.
     *                      - service_name: The service name of the service.
     *                      - sector_id: The sector id of the service.
     *                      - sector_name: The sector name of the service.
     *                      - status: The status of the service.
     *                      - fomnext_quota: The fomnext quota of the service.
     *                      - air_ticket_deposit: The ticket deposit of the service.
     * 
     * @param object $service The service was created against the prospect service Id
     * 
     * @return Project The newly created project service.
     */
    private function createCrmProspect($service, $request): mixed
    {
        $prospectService = $this->crmProspectService->create([
            'crm_prospect_id'    => $request['prospect_id'],
            'service_id'         => $service->id,
            'service_name'       => $service->service_name,
            'sector_id'          => $request['sector_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'sector_name'        => $request['sector_name'] ?? '',
            'status'             => $request['status'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'fomnext_quota'      => $request['fomnext_quota'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'air_ticket_deposit' => $request['air_ticket_deposit'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
        ]);

        return $prospectService;
    }
    
    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function submitProposalValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->eContractApplications->rulesForSubmission());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }
    
    /**
     * Show the e-contract application.
     * 
     * @param array $request The request data containing application id, company id
     * @return mixed Returns the e-contract application.
     */
    private function showEContractApplications($request): mixed
    {
        return $this->eContractApplications->where('company_id', $request['company_id'])->find($request['id']);
    }
    
    /**
     * Updates the e-contract application data with the given request.
     * 
     * @param array $request The array containing application data.
     *                      The array should have the following keys:
     *                      - quota_requested: (string) The updated application quota requested.
     *                      - person_incharge: (string) The updated application person incharge.
     *                      - cost_quoted: (int) The updated application cost quoted.
     *                      - remarks: (string) The updated application remarks.
     *                      - modified_by: (int) The updated application modified by.
     */
    private function updateEContractApplications($applicationDetails, $request): void
    {
        $applicationDetails->quota_requested = $request['quota_requested'] ?? $applicationDetails->quota_applied;
        $applicationDetails->person_incharge = $request['person_incharge'] ?? $applicationDetails->person_incharge;
        $applicationDetails->cost_quoted = $request['cost_quoted'] ?? $applicationDetails->cost_quoted;
        $applicationDetails->status = self::PROPOSAL_SUBMITTED;
        $applicationDetails->remarks = $request['remarks'] ?? $applicationDetails->remarks;
        $applicationDetails->modified_by = $request['modified_by'];
        $applicationDetails->save();
    }
    
    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function allocateQuotaValidateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->allocateQuotaValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }
    
    /**
     * Updates the quota details with the given request.
     * 
     * @param object $serviceDetails The service object to be updated.
     * @param array $request The array containing quota details.
     *                       The array should have the following keys:
     *                      - fomnext_quota: (string) The updated fomnext quota.
     *                      - air_ticket_deposit: (string) The updated air ticket deposit.
     */
    private function updateCrmProspectServiceDetails($serviceDetails, $request): void
    {
        $serviceDetails->fomnext_quota = $request['fomnext_quota'] ?? $serviceDetails->fomnext_quota;
        $serviceDetails->air_ticket_deposit = $request['air_ticket_deposit'] ?? $serviceDetails->air_ticket_deposit;
        $serviceDetails->save();
    }

}
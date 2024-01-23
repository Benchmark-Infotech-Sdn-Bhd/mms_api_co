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
    public const UNAUTHORIZED_ERROR = 'Unauthorized';

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
     * Constructs a new instance of the class.
     * 
     * @param CRMProspect $crmProspect The crm prospect object.
     * @param CRMProspectService $crmProspectService The crm prospect service object.
     * @param CRMProspectAttachment $crmProspectAttachment The crm prospect attachment object.
     * @param Services $services The services object.
     * @param Sectors $sectors The sectors object.
     * @param EContractApplications $eContractApplications The e-contract applications object.
     * @param EContractApplicationAttachments $eContractApplicationAttachments The e-contract application attachments object.
     * @param DirectrecruitmentApplications $directrecruitmentApplications The direct recruitment applications object.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The direct recruitment onboarding country object.
     * @param Storage $storage The storage object.
     */
    public function __construct(
        CRMProspect $crmProspect, 
        CRMProspectService $crmProspectService, 
        CRMProspectAttachment $crmProspectAttachment, 
        Services $services, 
        Sectors $sectors, 
        EContractApplications $eContractApplications, 
        EContractApplicationAttachments $eContractApplicationAttachments, 
        DirectrecruitmentApplications $directrecruitmentApplications, 
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, 
        Storage $storage
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
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return $this->eContractApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'e-contract_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        ->leftJoin('e-contract_project', 'e-contract_project.application_id', 'e-contract_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','e-contract_project.id')
            ->where('worker_employment.service_type', self::SERVICE_TYPE)
            ->where('worker_employment.transfer_flag', self::EMPLOYMENT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
        })
        ->where('crm_prospect_services.service_id', self::PROSPECT_SERVICES_ID)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->whereIn('e-contract_applications.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == self::CUSTOMER) {
                $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            if (isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->selectRaw('`e-contract_applications`.`id`, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, `e-contract_applications`.`status`, `e-contract_applications`.`quota_requested`, count(distinct `e-contract_project`.`id`) as projects, count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
        ->groupBy('e-contract_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'e-contract_applications.status', 'e-contract_applications.quota_requested')
        ->orderBy('e-contract_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Creates a new service from the given request data.
     * 
     * @param $request The request data containing service details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if prospect company is null.
     * - "isSubmit": A boolean indicating if the crm prospect service, e-contract applications was successfully created.
     */
    public function addService($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->addServiceValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $prospectCompany = $this->crmProspect
        ->where('company_id', $request['company_id'])
        ->find($request['prospect_id']);
        if (is_null($prospectCompany)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }
        
        $service = $this->services->find($request['service_id']);
        $prospectService = $this->crmProspectService->create([
            'crm_prospect_id'    => $request['prospect_id'],
            'service_id'         => $service->id,
            'service_name'       => $service->service_name,
            'sector_id'          => $request['sector_id'] ?? 0,
            'sector_name'        => $request['sector_name'] ?? '',
            'status'             => $request['status'] ?? 0,
            'fomnext_quota'      => $request['fomnext_quota'] ?? 0,
            'air_ticket_deposit' => $request['air_ticket_deposit'] ?? 0,
        ]);

        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/crm/prospect/' . $request['service_name'] . '/' . $request['sector_name']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->crmProspectAttachment->create([
                    "file_id" => $request['prospect_id'],
                    "prospect_service_id" => $prospectService->id,
                    "file_name" => $fileName,
                    "file_type" => self::PROSPECT_SERVICE,
                    "file_url" =>  $fileUrl          
                ]);  
            }
        }

        $this->eContractApplications::create([
            'crm_prospect_id' => $request['prospect_id'],
            'service_id' => $prospectService->id,
            'quota_requested' => $request['fomnext_quota'] ?? 0,
            'person_incharge' => '',
            'cost_quoted' => self::APPLICATION_COST_QUOTED,
            'status' => self::PENDING_PROPOSAL,
            'remarks' => '',
            'created_by' => $request["created_by"] ?? 0,
            'modified_by' => $request["created_by"] ?? 0,
            'company_id' => $request['company_id']
        ]);

        return true;
    }

    /**
     * @param $request The request data containing proposal details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if application details is null.
     * - "isSubmit": A boolean indicating if the application details was successfully updated.
     */
    public function submitProposal($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->eContractApplications->rulesForSubmission());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];

        $applicationDetails = $this->eContractApplications->where('company_id', $request['company_id'])->find($request['id']);
        if (is_null($applicationDetails)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }

        $applicationDetails->quota_requested = $request['quota_requested'] ?? $applicationDetails->quota_applied;
        $applicationDetails->person_incharge = $request['person_incharge'] ?? $applicationDetails->person_incharge;
        $applicationDetails->cost_quoted = $request['cost_quoted'] ?? $applicationDetails->cost_quoted;
        $applicationDetails->status = self::PROPOSAL_SUBMITTED;
        $applicationDetails->remarks = $request['remarks'] ?? $applicationDetails->remarks;
        $applicationDetails->modified_by = $request['modified_by'];
        $applicationDetails->save();

        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/proposal/' . $request['id'] . '/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractApplicationAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => self::PROPOSAL,
                    "file_url" =>  $fileUrl, 
                    "created_by" => $request['modified_by'],
                    "modified_by" => $request['modified_by']
                ]);  
            }
        }

        return true;
    }

    /**
     * @param $request The request data containing e-contract applications id,  company_id
     * @return mixed The list of proposal
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
     * @param $request The request data containing quota details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if service details is null.
     * - "isAllocate": A boolean indicating if the quota details was successfully updated.
     */
    public function allocateQuota($request): bool|array
    {
        $validator = Validator::make($request, $this->allocateQuotaValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        //$serviceDetails = $this->crmProspectService->findOrFail($request['prospect_service_id']);
        $serviceDetails = $this->crmProspectService
        ->join('crm_prospects', function($query) use($request) {
            $query->on('crm_prospects.id','=','crm_prospect_services.crm_prospect_id')
            ->whereIn('crm_prospects.company_id', $request['company_id']);
        })
        ->select('crm_prospect_services.*')
        ->find($request['prospect_service_id']);
        if (is_null($serviceDetails)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        } 

        $serviceDetails->fomnext_quota = $request['fomnext_quota'] ?? $serviceDetails->fomnext_quota;
        $serviceDetails->air_ticket_deposit = $request['air_ticket_deposit'] ?? $serviceDetails->air_ticket_deposit;
        $serviceDetails->save();

        $applicationDetails = $this->eContractApplications->findOrFail($request['id']);
        $applicationDetails->quota_requested = $request['fomnext_quota'] ?? $serviceDetails->fomnext_quota;
        $applicationDetails->save();

        return true;
    }

    /**
     * show crm prospect service.
     * 
     * @param $request The request data containing crm prospect service id,  company_id
     * @return mixed
     */
    public function showService($request): mixed
    {
        return $this->crmProspectService
        ->join('crm_prospects', function($query) use($request) {
            $query->on('crm_prospects.id','=','crm_prospect_services.crm_prospect_id')
            ->whereIn('crm_prospects.company_id', $request['company_id']);
        })
        ->select('crm_prospect_services.*')
        ->find($request['prospect_service_id']);
    }

}
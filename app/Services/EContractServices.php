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
     * TotalManagementServices constructor.
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param Services $services
     * @param Sectors $sectors
     * @param EContractApplications $eContractApplications
     * @param EContractApplicationAttachments $eContractApplicationAttachments
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     * @param Storage $storage
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors, EContractApplications $eContractApplications, EContractApplicationAttachments $eContractApplicationAttachments, DirectrecruitmentApplications $directrecruitmentApplications, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, Storage $storage)
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
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * @return array
     */
    public function addServiceValidation(): array
    {
        return [
            'prospect_id' => 'required',
            'company_name' => 'required',
            'sector_id' => 'required',
            'sector_name' => 'required',
            'fomnext_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'air_ticket_deposit' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        ];
    }
    /**
     * @return array
     */
    public function getQuotaValidation(): array
    {
        return [
            'prospect_service_id' => 'required',
            'fomnext_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'air_ticket_deposit' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function applicationListing($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->eContractApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'e-contract_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        // ->leftJoin('total_management_project', 'total_management_project.application_id', 'e-contract_applications.id')
        // ->leftJoin('workers', 'workers.crm_prospect_id', 'e-contract_applications.crm_prospect_id')
        ->where('crm_prospect_services.service_id', 2)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('e-contract_applications.id', 'crm_prospects.id as prospect_id', 'crm_prospect_services.id as prospect_service_id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name')
        ->orderBy('e-contract_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function addService($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->addServiceValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $service = $this->services->findOrFail($request['service_id']);
        $prospectService = $this->crmProspectService->create([
            'crm_prospect_id'    => $request['prospect_id'],
            'service_id'         => $service->id,
            'service_name'       => $service->service_name,
            'sector_id'          => $request['sector'] ?? 0,
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
                    "file_type" => 'prospect service',
                    "file_url" =>  $fileUrl          
                ]);  
            }
        }
        $this->eContractApplications::create([
            'crm_prospect_id' => $request['id'],
            'service_id' => $prospectService->id,
            'quota_requested' => 0,
            'person_incharge' => '',
            'cost_quoted' => 0,
            'status' => 'Pending Proposal',
            'remarks' => '',
            'created_by' => $request["created_by"] ?? 0,
            'modified_by' => $request["created_by"] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function submitProposal($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->eContractApplications->rulesForSubmission());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['modified_by'] = $user['id'];
        $applicationDetails = $this->eContractApplications->findOrFail($params['id']);
        $applicationDetails->quota_requested = $params['quota_requested'] ?? $applicationDetails->quota_applied;
        $applicationDetails->person_incharge = $params['person_incharge'] ?? $applicationDetails->person_incharge;
        $applicationDetails->cost_quoted = $params['cost_quoted'] ?? $applicationDetails->cost_quoted;
        $applicationDetails->status = 'Proposal Submitted';
        $applicationDetails->remarks = $params['remarks'] ?? $applicationDetails->remarks;
        $applicationDetails->modified_by = $params['modified_by'];
        $applicationDetails->save();

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/proposal/' . $params['id'] . '/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractApplicationAttachments::create([
                    "file_id" => $params['id'],
                    "file_name" => $fileName,
                    "file_type" => 'proposal',
                    "file_url" =>  $fileUrl, 
                    "created_by" => $params['modified_by'],
                    "modified_by" => $params['modified_by']
                ]);  
            }
        }
        return true;
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function showProposal($request) : mixed
    {
        return $this->eContractApplications
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        ->where('e-contract_applications.id', $request['id'])
        ->with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->select('e-contract_applications.id', 'e-contract_applications.quota_requested', 'e-contract_applications.person_incharge', 'e-contract_applications.cost_quoted', 'e-contract_applications.remarks', 'crm_prospect_services.sector_name')->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function getQuota($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->getQuotaValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $serviceDetails = $this->crmProspectService->findOrFail($request['prospect_service_id']);
        $serviceDetails->fomnext_quota = $request['fomnext_quota'] ?? $serviceDetails->fomnext_quota;
        $serviceDetails->air_ticket_deposit = $request['air_ticket_deposit'] ?? $serviceDetails->air_ticket_deposit;
        $serviceDetails->modified_by = $request['modified_by'];
        $serviceDetails->save();
        return true;
    }
}
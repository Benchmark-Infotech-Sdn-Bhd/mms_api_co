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
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var TotalManagementApplicationAttachments
     */
    private TotalManagementApplicationAttachments $totalManagementApplicationAttachments;
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
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * TotalManagementServices constructor.
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param Services $services
     * @param Sectors $sectors
     * @param TotalManagementApplications $totalManagementApplications
     * @param TotalManagementApplicationAttachments $totalManagementApplicationAttachments
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     * @param Storage $storage
     * @param AuthServices $authServices
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors, TotalManagementApplications $totalManagementApplications, TotalManagementApplicationAttachments $totalManagementApplicationAttachments, DirectrecruitmentApplications $directrecruitmentApplications, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, Storage $storage, AuthServices $authServices)
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

        return $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->leftJoin('total_management_project', 'total_management_project.application_id', 'total_management_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        })
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
            }
        })
        ->where('crm_prospect_services.service_id', 3)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->selectRaw('total_management_applications.id, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, crm_prospect_services.from_existing, total_management_applications.status, total_management_applications.quota_applied, count(distinct total_management_project.id) as projects, count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
        ->groupBy('total_management_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.from_existing', 'total_management_applications.status', 'total_management_applications.quota_applied')
        ->orderBy('total_management_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    } 
    /**
     * Checks if the given quota is valid for the provided request.
     *
     * @param mixed $request The request that contains the 'initial_quota' and 'service_quota' fields.
     * @return bool Returns true if the quota is valid for the request, false otherwise.
     */
    private function isValidQuota($request): bool
    {
        return $request['initial_quota'] < $request['service_quota'];
    }

    /**
     * Returns an array indicating that the quota is error.
     *
     * @return array An array containing the key 'quotaError' set to true.
     */
    private function quotaErrorResponse(): array
    {
        return ['quotaError' => true];
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
            'sector_id'         => $request['sector'] ?? 0,
            'sector_name'       => $request['sector_name'] ?? '',
            'status'            => $request['status'] ?? 0,
            'from_existing'     => $request['from_existing'] ?? 0,
            'client_quota'      => $request['client_quota'] ?? 0,
            'fomnext_quota'     => $request['fomnext_quota'] ?? 0,
            'initial_quota'     => $request['initial_quota'] ?? 0,
            'service_quota'     => $request['service_quota'] ?? 0,
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
            'quota_applied' => ($request['from_existing'] == 0) ? ($prospectService->client_quota + $prospectService->fomnext_quota) : $prospectService->service_quota,
            'person_incharge' => '',
            'cost_quoted' => 0,
            'status' => 'Pending Proposal',
            'remarks' => '',
            'created_by' => $request["created_by"] ?? 0,
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
     */
    public function addService($request): bool|array
    {
        $validator = Validator::make($request, $this->addServiceValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if (!$this->isValidQuota($request)) {
            return $this->quotaErrorResponse();
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
     * @param $request
     * @return int
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
     *
     * @param $request
     * @return mixed
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
     * @param $request
     * @return bool|array
     */
    public function submitProposal($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->totalManagementApplications->rulesForSubmission());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
        $applicationDetails = $this->totalManagementApplications->whereIn('company_id', $params['company_id'])->find($request['id']);
        if(is_null($applicationDetails)){
            return [
                'unauthorizedError' => true
            ];
        }
        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        if($serviceDetails->from_existing == 0) {
            $totalQuota = $serviceDetails->client_quota + $serviceDetails->fomnext_quota;
            if($totalQuota < $request['quota_requested']) {
                return [
                    'quotaError' => true
                ];
            }
        }
        
        $params = $request->all();
        $params['modified_by'] = $user['id'];
        $applicationDetails->quota_applied = $params['quota_requested'] ?? $applicationDetails->quota_applied;
        $applicationDetails->person_incharge = $params['person_incharge'] ?? $applicationDetails->person_incharge;
        $applicationDetails->cost_quoted = $params['cost_quoted'] ?? $applicationDetails->cost_quoted;
        $applicationDetails->status = 'Proposal Submitted';
        $applicationDetails->remarks = $params['remarks'] ?? $applicationDetails->remarks;
        $applicationDetails->modified_by = $params['modified_by'];
        $applicationDetails->save();

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/totalManagement/proposal/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->totalManagementApplicationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function allocateQuota($request): array|bool
    {
        if(isset($request['initial_quota'])) {
            if($request['initial_quota'] < $request['service_quota']) {
                return [
                    'quotaError' => true
                ];
            }
        }
        $prospectService = $this->crmProspectService->join('crm_prospects', function ($join) use ($request) {
            $join->on('crm_prospects.id', '=', 'crm_prospect_services.crm_prospect_id')
                 ->whereIn('crm_prospects.company_id', $request['company_id']);
        })->select('crm_prospect_services.*')->find($request['prospect_service_id']);
        if(is_null($prospectService)){
            return [
                'unauthorizedError' => true
            ];
        }
        $prospectService->from_existing =  $request['from_existing'] ?? 0;
        $prospectService->client_quota = $request['client_quota'] ?? $prospectService->client_quota;
        $prospectService->fomnext_quota = $request['fomnext_quota'] ?? $prospectService->fomnext_quota;
        $prospectService->initial_quota = $request['initial_quota'] ?? $prospectService->initial_quota;
        $prospectService->service_quota = $request['service_quota'] ?? $prospectService->service_quota;
        $prospectService->save();

        $applicationDetails = $this->totalManagementApplications->whereIn('company_id', $request['company_id'])->find($request['id']);
        if(is_null($applicationDetails)){
            return [
                'unauthorizedError' => true
            ];
        }
        $applicationDetails->quota_applied = ($request['from_existing'] == 0) ? ($prospectService->client_quota + $prospectService->fomnext_quota) : $prospectService->service_quota;
        $applicationDetails->save();
        return true;
    }
    /**
     *
     * @param $request
     * @return mixed
     */
    public function showService($request) : mixed
    {
        return $this->crmProspectService
        ->join('crm_prospects', function ($join) use ($request) {
            $join->on('crm_prospects.id', '=', 'crm_prospect_services.crm_prospect_id')
                 ->whereIn('crm_prospects.company_id', $request['company_id']);
        })
        ->select('crm_prospect_services.*')
        ->find($request['prospect_service_id']);
    }
}
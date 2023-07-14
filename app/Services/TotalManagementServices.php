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
use App\Models\Levy;

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
     * @var Levy
     */
    private Levy $levy;
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
     * @param TotalManagementApplications $totalManagementApplications
     * @param TotalManagementApplicationAttachments $totalManagementApplicationAttachments
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param Levy $levy
     * @param Storage $storage
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors, TotalManagementApplications $totalManagementApplications, TotalManagementApplicationAttachments $totalManagementApplicationAttachments, DirectrecruitmentApplications $directrecruitmentApplications, Levy $levy, Storage $storage)
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->totalManagementApplicationAttachments = $totalManagementApplicationAttachments;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->levy = $levy;
        $this->storage = $storage;
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
            'sector' => 'required',
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
        return $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->where('crm_prospect_services.service_id', 3)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('total_management_applications.id', 'crm_prospects.id as prospect_id', 'crm_prospect_services.id as prospect_service_id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.service_name', 'crm_prospect_services.status as service_status')
        ->distinct('total_management_applications.id')
        ->orderBy('total_management_applications.id', 'desc')
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
        $service = $this->services->findOrFail(Config::get('services.TOTAL_MANAGEMENT_SERVICE'));
        $sector = $this->sectors->findOrFail($request['sector']);
        $prospectService = $this->crmProspectService->create([
            'crm_prospect_id'   => $request['id'],
            'service_id'        => $service->id,
            'service_name'      => $service->service_name,
            'sector_id'         => $request['sector'] ?? 0,
            'sector_name'       => $sector->sector_name,
            'status'            => $request['status'] ?? 0,
            'from_existing'     => $request['from_existing'] ?? 0,
            'client_quota'      => $request['client_quota'] ?? 0,
            'fomnext_quota'     => $request['fomnext_quota'] ?? 0,
            'initial_quota'     => $request['initial_quota'] ?? 0,
            'service_quota'     => $request['service_quota'] ?? 0,
        ]);
        $this->totalManagementApplications::create([
            'crm_prospect_id' => $request['id'],
            'service_id' => $prospectService->id,
            'quota_applied' => 0,
            'person_incharge' => '',
            'cost_quoted' => 0,
            'status' => 'Pending Proposal',
            'remarks' => '',
            'created_by' => $request["created_by"] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return int
     */
    public function getQuota($request): int
    {
        $directrecruitmentApplicationIds = $this->directrecruitmentApplications->where('crm_prospect_id', $request['id'])
                                            ->select('id')
                                            ->get()
                                            ->toArray();
        $applicationIds = array_column($directrecruitmentApplicationIds, 'id');
        $approvedQuota = $this->levy->whereIn('application_id', $applicationIds)->sum('approved_quota');
        return $approvedQuota;
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
        $params = $request->all();
        $params['modified_by'] = $user['id'];
        $applicationDetails = $this->totalManagementApplications->findOrFail($params['id']);
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
}
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
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param Levy $levy
     * @param Storage $storage
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors, TotalManagementApplications $totalManagementApplications, DirectrecruitmentApplications $directrecruitmentApplications, Levy $levy, Storage $storage)
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->totalManagementApplications = $totalManagementApplications;
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
            'sector' => 'required'
        ];
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
}
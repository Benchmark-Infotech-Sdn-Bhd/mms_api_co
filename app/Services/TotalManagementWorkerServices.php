<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\Vendor;
use App\Models\Accommodation;
use App\Models\WorkerEmployment;
use App\Models\TotalManagementApplications;
use App\Models\CRMProspectService;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class TotalManagementWorkerServices
{
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
     * TotalManagementWorkerServices constructor.
     * @param Workers $workers
     * @param Vendor $vendor
     * @param Accommodation $accommodation
     * @param WorkerEmployment $workerEmployment
     * @param TotalManagementApplications $totalManagementApplications
     * @param CRMProspectService $crmProspectService
     */
    public function __construct(Workers $workers, Vendor $vendor, Accommodation $accommodation, WorkerEmployment $workerEmployment, TotalManagementApplications $totalManagementApplications, CRMProspectService $crmProspectService)
    {
        $this->workers = $workers;
        $this->vendor = $vendor;
        $this->accommodation = $accommodation;
        $this->workerEmployment = $workerEmployment;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
    }
    /**
     * @return array
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
     * @param $request
     * @return mixed
     */
    public function workerListForAssignWorker($request): mixed
    {
        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('directrecruitment_applications', 'directrecruitment_applications.id', 'workers.application_id')
            ->leftJoin('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
            ->where(function ($query) use ($request) {
                if (isset($request['filter']) && $request['filter']) {
                    $query->where('crm_prospects.id', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'crm_prospects.id as company_id', 'crm_prospects.company_name')
            ->distinct()
            ->orderBy('workers.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function accommodationProviderDropDown($request): mixed
    {
        return $this->vendor->where('type', 'Accommodation')->select('id', 'name')->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function accommodationUnitDropDown($request): mixed
    {
        return $this->accommodation->where('vendor_id', $request['id'])->select('id', 'name')->get();
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function assignWorker($request): array|bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];

        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if(isset($request['workers']) && !empty($request['workers'])) {
            foreach ($request['workers'] as $workerId) {
                $this->workerEmployment->create([
                    'worker_id' => $workerId,
                    'project_id' => $request['project_id'],
                    'department' => $request['department'],
                    'sub_department' => $request['sub_department'],
                    'accommodation_provider_id' => $request['accommodation_provider_id'],
                    'accommodation_unit_id' => $request['accommodation_unit_id'],
                    'work_start_date' => $request['work_start_date'],
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
        }
        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'total_management_flag' => 1,
                'modified_by' => $request['created_by']
            ]);
        return true;
    }
    /**
     * @param $request
     * @return array
     */
    public function getBalancedQuota($request): array
    {
        $applicationDetails = $this->totalManagementApplications->findOrFail($request['application_id']);
        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        $workersCount = $this->workers->where('crm_prospect_id', $applicationDetails->crm_prospect_id)
                            ->where('worker_status', 'Assigned')
                            ->count('id');
        if($serviceDetails->from_existing == 0) {
            return [
                'clientQuota' => $serviceDetails->client_quota,
                'clientBalancedQuota' => $workersCount - $serviceDetails->client_quota,
                'fomnextQuota' => $serviceDetails->fomnext_quota,
                'fomnextBalancedQuota' => $workersCount - $serviceDetails->client_quota
            ];
        } else if($serviceDetails->from_existing == 1) {
            return [
                'serviceQuota' => $serviceDetails->service_quota,
                'balancedServiceQuota' => $workersCount - $serviceDetails->service_quota
            ];
        }
    }
}
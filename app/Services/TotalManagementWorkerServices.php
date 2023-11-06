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
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;

    /**
     * TotalManagementWorkerServices constructor.
     * @param Workers $workers
     * @param Vendor $vendor
     * @param Accommodation $accommodation
     * @param WorkerEmployment $workerEmployment
     * @param TotalManagementApplications $totalManagementApplications
     * @param CRMProspectService $crmProspectService
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(Workers $workers, Vendor $vendor, Accommodation $accommodation, WorkerEmployment $workerEmployment, TotalManagementApplications $totalManagementApplications, CRMProspectService $crmProspectService, DirectrecruitmentApplications $directrecruitmentApplications, TotalManagementProject $totalManagementProject)
    {
        $this->workers = $workers;
        $this->vendor = $vendor;
        $this->accommodation = $accommodation;
        $this->workerEmployment = $workerEmployment;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->totalManagementProject = $totalManagementProject;
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
     * @return array
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
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'total_management_applications.service_id')
            ->leftJoin('vendors as vendor_transport', 'vendor_transport.id', 'total_management_project.transportation_provider_id')
            ->leftJoin('vendors', 'vendors.id', 'worker_employment.accommodation_provider_id')
            ->where('total_management_project.id', $request['project_id'])
            ->where('worker_employment.service_type', 'Total Management')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && $request['search']) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%');
                    $query->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%');
                    $query->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%');
                    $query->orWhere('worker_employment.department', 'like', '%' . $request['search'] . '%');
                }
            })
            ->where(function ($query) use ($request) {
                if((isset($request['filter']) && !empty($request['filter'])) || $request['filter'] == 0) {
                    $query->where('workers.status', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'vendors.name as accommodation_provider', 'vendor_transport.name as transportation_provider', 'worker_employment.department', 'workers.status', 'workers.total_management_status', 'worker_employment.status as worker_assign_status', 'worker_employment.remove_date', 'worker_employment.remarks', 'crm_prospect_services.from_existing', 'total_management_project.application_id')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workerListForAssignWorker($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        $request['company_ids'] = array($request['prospect_id'], 0);
        $applicationDetails = $this->totalManagementApplications->findOrFail($request['application_id']);
        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        
        if(isset($serviceDetails->from_existing) && $serviceDetails->from_existing == 1) {
            $request['from_existing'] = $serviceDetails->from_existing;
        }else{
            $request['from_existing'] = 0;
        }
        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->where('workers.econtract_status', 'On-Bench')
            ->where('workers.total_management_status', 'On-Bench')
            ->whereIn('workers.crm_prospect_id', $request['company_ids'])
        ->where(function ($query) use ($request) {
            if(isset($request['from_existing']) && $request['from_existing'] == 1){
                $query->where([
                    ['workers.crm_prospect_id', $request['prospect_id']],
                    ['workers.plks_status', 'Approved']
                ]);
            }else{
                    $query->where('workers.module_type', '<>', Config::get('services.WORKER_MODULE_TYPE')[0])->orWhereNull('workers.module_type');
            }
        })
        ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if ((isset($request['company_filter']) && !empty($request['company_filter'])) || $request['company_filter'] == 0) {
                    $query->where('workers.crm_prospect_id', $request['company_filter']);
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['ksm_reference_number']) && !empty($request['ksm_reference_number'])) {
                    $query->where('worker_visa.ksm_reference_number', $request['ksm_reference_number']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'workers.crm_prospect_id as company_id', 'workers.econtract_status', 'workers.total_management_status', 'workers.plks_status', 'workers.module_type')
            ->distinct()
            ->orderBy('workers.created_at','DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function accommodationProviderDropDown($request): mixed
    {
        return $this->vendor->where('type', 'Accommodation')
                ->whereIn('company_id', $request['company_id'])
                ->select('id', 'name')
                ->get();
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

        if(isset($request['workers']) && !empty($request['workers'])) 
        {
            $projectDetails = $this->totalManagementProject->findOrFail($request['project_id']);
            $applicationDetails = $this->totalManagementApplications->findOrFail($projectDetails->application_id);
            $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
            $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
            
            if($serviceDetails->from_existing == 1) {
                $workerCountArray['clientWorkersCount'] += count($request['workers']);
                if($workerCountArray['clientWorkersCount'] > $applicationDetails->quota_applied) {
                    return [
                        'quotaError' => true
                    ];
                }
            } else if($serviceDetails->from_existing == 0) {
                $fomnextWorkerCount = $this->workers->whereIn('id', $request['workers'])
                                        ->where('crm_prospect_id', 0)
                                        ->count();
                $clientWorkerCount = $this->workers->whereIn('id', $request['workers'])
                                        ->where('crm_prospect_id', $applicationDetails->crm_prospect_id)
                                        ->count();

                $workerCountArray['fomnextWorkersCount'] += $fomnextWorkerCount;
                if($workerCountArray['fomnextWorkersCount'] > $serviceDetails->fomnext_quota) {
                    return [
                        'fomnextQuotaError' => true
                    ];
                }
                
                $workerCountArray['clientWorkersCount'] += $clientWorkerCount;
                if($workerCountArray['clientWorkersCount'] > $serviceDetails->client_quota) {
                    return [
                        'clientQuotaError' => true
                    ];
                }
            }

            foreach ($request['workers'] as $workerId) {
                $this->workerEmployment->create([
                    'worker_id' => $workerId,
                    'project_id' => $request['project_id'],
                    'department' => $request['department'],
                    'sub_department' => $request['sub_department'],
                    'accommodation_provider_id' => $request['accommodation_provider_id'],
                    'accommodation_unit_id' => $request['accommodation_unit_id'],
                    'work_start_date' => $request['work_start_date'],
                    'service_type' => 'Total Management',
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'module_type' => Config::get('services.WORKER_MODULE_TYPE')[1],
                    'total_management_status' => 'Assigned',
                    'modified_by' => $request['created_by']
                ]);
        }
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

        $workerCount = $this->getWorkerCount($request['application_id'], $applicationDetails->crm_prospect_id);

        if($serviceDetails->from_existing == 0) {
            return [
                'clientQuota' => $serviceDetails->client_quota,
                'clientBalancedQuota' => $serviceDetails->client_quota - $workerCount['clientWorkersCount'],
                'fomnextQuota' => $serviceDetails->fomnext_quota,
                'fomnextBalancedQuota' => $serviceDetails->fomnext_quota - $workerCount['fomnextWorkersCount']
            ];
        } else if($serviceDetails->from_existing == 1) {
            return [
                'serviceQuota' => $serviceDetails->service_quota,
                'balancedServiceQuota' => $serviceDetails->service_quota - $workerCount['clientWorkersCount']
            ];
        }
    }
    /**
     * @param $request
     * @return mixed
     */
    public function getCompany($request): mixed
    {
        return $this->totalManagementApplications
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
                    ->where('total_management_applications.id', $request['application_id'])
                    ->select('crm_prospects.id', 'crm_prospects.company_name')
                    ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function ksmRefereneceNUmberDropDown($request): mixed
    {
        $companyId = $this->totalManagementApplications
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
                    ->where('total_management_applications.id', $request['application_id'])
                    ->select('crm_prospects.id')
                    ->get()->toArray();
        $companyId = array_column($companyId, 'id');
        $ksmReferenceNumbers = $this->directrecruitmentApplications
        ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_applications.id')
        ->whereIn('directrecruitment_applications.crm_prospect_id', $companyId)
        ->where('directrecruitment_application_approval.ksm_reference_number', '!=', NULL)
        ->select('directrecruitment_applications.id as directrecruitment_application_id', 'directrecruitment_application_approval.ksm_reference_number')
        ->get();
        return $ksmReferenceNumbers;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function getSectorAndValidUntil($request): mixed
    {
        return $this->directrecruitmentApplications
                    ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_applications.id')
                    ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
                    ->where('directrecruitment_applications.crm_prospect_id', $request['prospect_id'])
                    ->where('directrecruitment_application_approval.ksm_reference_number', $request['ksm_reference_number'])
                    ->select('crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'directrecruitment_application_approval.valid_until')
                    ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function getAssignedWorker($request): mixed
    {
        return $this->workerEmployment
        ->leftjoin('workers', 'workers.id', 'worker_employment.worker_id')
        ->where('worker_employment.project_id', $request['project_id'])
        ->where('worker_employment.status', 1)
        ->where('service_type', 'Total Management')
        ->whereIn('workers.company_id', $request['company_id'])
        ->select('worker_employment.id','worker_employment.worker_id','workers.name','workers.passport_number')
        ->get();
    }   

    /**
     * @param $request
     * @return array|bool
     */
    public function removeWorker($request): array|bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];

        $validator = Validator::make($request, $this->removeValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $workerDetails = $this->workerEmployment->where("worker_id", $request['worker_id'])
                        ->where("project_id", $request['project_id'])
                        ->where("service_type", "Total Management")
                        ->get();

        $this->workerEmployment->where("worker_id", $request['worker_id'])
        ->where("project_id", $request['project_id'])
        ->where("service_type", "Total Management")
        ->update([
            'status' => 0,
            'work_end_date' => $request['last_working_day'],
            'remove_date' => $request['remove_date'],
            'remarks' => $request['remarks']
        ]);

        $this->workers->where('id', $request['worker_id'])
        ->update([
            'total_management_status' => 'On-Bench',
            'modified_by' => $request['modified_by']
        ]);

        return true;
    }
    /**
     * @param $applicationId, $prospectId
     * @return array
     */
    public function getWorkerCount($applicationId, $prospectId): array
    {
        $projectIds = $this->totalManagementProject->where('application_id', $applicationId)
                            ->select('id')
                            ->get()
                            ->toArray();
        $projectIds = array_column($projectIds, 'id');

        $clientWorkersCount = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date')
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type');
        })
        ->where('workers.crm_prospect_id', $prospectId)
        ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
        ->whereIn('worker_employment.project_id', $projectIds)
        ->distinct('workers.id')
        ->count('workers.id');

        $fomnextWorkersCount = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date')
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type');
        })
        ->where('workers.crm_prospect_id', 0)
        ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
        ->where('worker_employment.project_id', $projectIds)
        ->distinct('workers.id')
        ->count('workers.id');

        return [
            'clientWorkersCount' => $clientWorkersCount,
            'fomnextWorkersCount' => $fomnextWorkersCount
        ];
    }
}
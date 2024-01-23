<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerEmployment;
use App\Models\CRMProspect;
use App\Models\TotalManagementProject;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\EContractProject;
use App\Models\EContractApplications;
use App\Models\TotalManagementApplications;
use App\Models\CRMProspectService;

class TotalManagementTransferServices
{
    const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    const ERROR_PROJECT_EXIST = ['projectExist' => true];
    const ERROR_FROM_EXISTING = ['fromExistingError' => true];
    const ERROR_OTHER_COMPANY = ['otherCompanyError' => true];
    const ERROR_QUOTA = ['quotaError' => true];
    const ERROR_QUOTA_FROM_EXISTING = ['quotaFromExistingError' => true];
    const ERROR_FROM_EXISTING_WORKER = ['fromExistingWorkerError' => true];
    const ERROR_FOMNEXT_QUOTA = ['fomnextQuotaError' => true];
    const ERROR_CLIENT_QUOTA = ['clientQuotaError' => true];

    const CUSTOMER = 'Customer';
    const WORKER_STATUS_ASSIGNED = 'Assigned';
    const WORKER_STATUS_ONBENCH = 'On-Bench';
    const FROM_EXISTING = 1;
    const NOT_FROM_EXISTING = 0;
    const DIRECT_RECRUITMENT_SERVICE_ID = 1;
    const TOTAL_MANAGEMENT_SERVICE_ID = 3;
    const DEFAULT_TRANSFER_FLAG = 0;
    const ACTIVE_TRANSFER_FLAG = 1;
    const STATUS_ACTIVE = 1;

    private Workers $workers;
    private WorkerEmployment $workerEmployment;
    private TotalManagementApplications $totalManagementApplications;
    private CRMProspect $crmProspect;
    private TotalManagementProject $totalManagementProject;
    private AuthServices $authServices;
    private EContractProject $eContractProject;
    private EContractApplications $eContractApplications;
    private CRMProspectService $crmProspectService;
    
    /**
     * TotalManagementTransferServices constructor.
     * 
     * @param Workers $workers The Workers instance.
     * @param WorkerEmployment $workerEmployment The WorkerEmployment instance.
     * @param CRMProspect $crmProspect The CRMProspect instance.
     * @param TotalManagementProject $totalManagementProject The TotalManagementProject instance.
     * @param AuthServices $authServices The AuthServices object.
     * @param EContractTransferServices $eContractTransferServices The EContractTransferServices object.
     * @param EContractProject $eContractProject The EContractProject instance.
     * @param EContractApplications $eContractApplications The EContractApplications instance.
     * @param TotalManagementApplications $totalManagementApplications The TotalManagementApplications instance.
     * @param CRMProspectService $crmProspectService The CRM prospect service object
     */
    public function __construct(
        Workers $workers, 
        WorkerEmployment $workerEmployment, 
        CRMProspect $crmProspect, 
        TotalManagementProject $totalManagementProject, 
        AuthServices $authServices, 
        EContractProject $eContractProject, 
        EContractApplications $eContractApplications, 
        TotalManagementApplications $totalManagementApplications, 
        CRMProspectService $crmProspectService
    )
    {
        $this->workers = $workers;
        $this->workerEmployment = $workerEmployment;
        $this->crmProspect = $crmProspect;
        $this->totalManagementProject = $totalManagementProject;
        $this->authServices = $authServices;
        $this->eContractProject = $eContractProject;
        $this->eContractApplications = $eContractApplications;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
    }
    /**
     * validate the transfer submit request data
     * 
     * @return array
     */
    public function submitValidation(): array
    {
        return [
            'worker_id' => 'required',
            'current_project_id' => 'required',
            'new_project_id' => 'required',
            'accommodation_provider_id' => 'required|regex:/^[0-9]*$/',
            'accommodation_unit_id' => 'required|regex:/^[0-9]*$/',
            'last_working_day' => 'required|date|date_format:Y-m-d',
            'new_joining_date' => 'required|date|date_format:Y-m-d',
            'service_type' => 'required'
        ];
    }
    /**
     * get the worker emplyment detail based on worker id
     * 
     * @param $request
     *        worker_id (int) worker ID
     * 
     * @return mixed
     */
    public function workerEmploymentDetail($request): mixed
    {
        return $this->workers
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'workers.crm_prospect_id')
                    ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
                    ->where('workers.id', $request['worker_id'])
                    ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                    ->select('workers.id', 'crm_prospects.id as company_id', 'crm_prospects.company_name', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id', 'worker_employment.department', 'worker_employment.sub_department', 'worker_employment.work_start_date', 'worker_employment.work_end_date','worker_employment.service_type', 'worker_employment.transfer_flag')
                    ->get();
    }
    /**
     * get the company list based on request data
     * 
     * @param $request
     *        from_existing (int) from existing company
     *        company_id (array) user company ID
     *        search (text) search text for company name
     *        filter (int) CRM service ID
     * @return mixed
     */
    public function companyList($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('sectors', 'sectors.id', 'crm_prospect_services.sector_id')
        ->where('crm_prospects.status', self::STATUS_ACTIVE)
        ->whereNull('crm_prospect_services.deleted_at')
        ->where(function ($query) use ($request) {
            $request['from_existing_value'] = self::FROM_EXISTING;
            $request['total_management_service_id'] = self::TOTAL_MANAGEMENT_SERVICE_ID;
            $request['direct_recruitment_service_id'] = self::DIRECT_RECRUITMENT_SERVICE_ID;
            if(isset($request['from_existing']) && $request['from_existing'] == $request['from_existing_value']) {
                $query->where('crm_prospect_services.from_existing', $request['from_existing_value'])
                ->where('crm_prospect_services.service_id', '=', $request['total_management_service_id']);
            }else{
                $query->where('crm_prospect_services.service_id', '!=', $request['direct_recruitment_service_id'])
                ->where('crm_prospect_services.from_existing', '!=', $request['from_existing_value']);
            }
        })
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == self::CUSTOMER) {
                $query->where('crm_prospects.id', '=', $user['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if(!empty($search)) {
                $query->where('crm_prospects.company_name', 'like', '%'.$search.'%');
            }
        })
        ->where(function ($query) use ($request) {
            $filter = $request['filter'] ?? '';
            if(!empty($filter)) {
                $query->where('crm_prospect_services.service_id', $filter);
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospect_services.service_id', 'sectors.sector_name', 'crm_prospect_services.from_existing')
        ->selectRaw("(CASE WHEN (crm_prospect_services.service_id = 1) THEN 'Direct Recruitment' WHEN (crm_prospect_services.service_id = 2) THEN 'e-Contract' ELSE 'Total Management' END) as service_type, crm_prospect_services.id as prospect_service_id")
        ->distinct('crm_prospect_services.id')
        ->orderBy('crm_prospects.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * get the total management project list based on request data
     * 
     * @param $request
     *        service_type (string) type of service
     *        crm_prospect_id (int) ID of crm prospect
     *        prospect_service_id (int) service ID of prospect
     *        from_existing (int) from existing
     * 
     * @return mixed
     */
    public function projectList($request): mixed
    {
        if($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]) {
            return $this->getTotalManagementProjectList($request);
        } else if($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[3]) {
            return $this->geteContractProjectList($request);
        }
    }
    /**
     * Retrieve totalmanagement project list.
     *
     * @param array $request
     *              crm_prospect_id (int) ID of crm prospect
     *              prospect_service_id (int) service ID of prospect
     *              from_existing (int) from existing
     * 
     * @return mixed
     */
    private function getTotalManagementProjectList($request)
    {
        return $this->totalManagementProject
        ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'total_management_applications.service_id')
        ->where('crm_prospect_services.crm_prospect_id',$request['crm_prospect_id'])
        ->where('crm_prospect_services.id',$request['prospect_service_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['from_existing']) && $request['from_existing'] == self::FROM_EXISTING) {
                $query->where('crm_prospect_services.from_existing', self::FROM_EXISTING);
            }else{
                $query->where('crm_prospect_services.from_existing', self::NOT_FROM_EXISTING);
            }
        })
        ->select('total_management_project.id', 'total_management_project.name')
        ->distinct('total_management_project.id')
        ->orderBy('total_management_project.id', 'desc')
        ->get();
    }
    /**
     * Retrieve econtract project list.
     *
     * @param array $request
     *              crm_prospect_id (int) ID of crm prospect
     *              prospect_service_id (int) service ID of prospect
     *
     * @return mixed
     */
    private function geteContractProjectList($request)
    {
        return $this->eContractProject
        ->leftJoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'e-contract_applications.service_id')
        ->where('crm_prospect_services.crm_prospect_id',$request['crm_prospect_id'])
        ->where('crm_prospect_services.id',$request['prospect_service_id'])
        ->select('e-contract_project.id', 'e-contract_project.name')
        ->distinct('e-contract_project.id')
        ->orderBy('e-contract_project.id', 'desc')
        ->get();
    }
    /**
     * submit the total mangement worker transfer
     * 
     * @param $request The request data containing transfer details
     * 
     * 
     * @return array|bool
     */
    public function submit($request): array|bool
    {
        $validator = Validator::make($request, $this->submitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $workerData = $this->getWorker($request);
        if(is_null($workerData)){
            return self::ERROR_UNAUTHORIZED;
            }

        // CHECK WORKER EMPLOYMENT DATA - SAME PROJECT ID
        $workerEmployment = $this->getWorkerEmploymentCount($request);

        if($workerEmployment > 0) {
            return self::ERROR_PROJECT_EXIST;
        }

        if($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[3]) {
            $processEContractService = $this->processEContractService($request);
            if($processEContractService){
                return $processEContractService;
            }
        } else if($request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]) {
            $processTotalManagementService = $this->processTotalManagementService($request);
            if($processTotalManagementService){
                return $processTotalManagementService;
            }
        }
        // UPDATE WORKERS TABLE
        $this->updateWorkerTransferDetail($request);

        $this->updateWorkerEmploymentDetail($request);
        
        return true;
    }
    /**
     * Retrieve worker record.
     *
     * @param array $request
     *              company_id (array) company ID's of the user
     *              worker_id (int) ID of the worker
     * 
     * @return mixed
     */
    private function getWorker($request)
    {
        return $this->workers::whereIn('company_id', $request['company_id'])->find($request['worker_id']);
    }
    /**
     * Retrieve worker Employemnt count.
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              new_project_id (int) ID of the new transfer project 
     *              service_type (string) type of the service
     * @return mixed
     */
    private function getWorkerEmploymentCount($request)
    {
        return $this->workerEmployment->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['new_project_id']],
            ['service_type', $request['service_type']]
        ])
        ->where('transfer_flag', self::DEFAULT_TRANSFER_FLAG)
        ->whereNull('remove_date')
        ->count();
    }
    /**
     * process EContract Service based on provided request data.
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              new_prospect_id (int) ID of the new prospect 
     *              company_id (array) company ID's of the user
     * 
     * @return array
     */
    private function processEContractService($request)
    {
        $workerDetail = $this->workers->findOrFail($request['worker_id']);
            if($request['from_existing'] == self::FROM_EXISTING) {
                return self::ERROR_FROM_EXISTING;
            } else if ($request['from_existing'] == self::NOT_FROM_EXISTING) {
                if($workerDetail->crm_prospect_id != 0) {
                    if($workerDetail->crm_prospect_id != $request['new_prospect_id']) {
                        return self::ERROR_OTHER_COMPANY;
                    }
                }
                $projectDetails = $this->eContractProject->findOrFail($request['new_project_id']);
                $applicationDeatils = $this->eContractApplications::whereIn('company_id', $request['company_id'])->find($projectDetails->application_id);
                if(is_null($applicationDeatils)){
                    return self::ERROR_UNAUTHORIZED;
                }
                $projectIds = $this->eContractProject->where('application_id', $projectDetails->application_id)
                                ->select('id')
                                ->get()
                                ->toArray();
                $projectIds = array_column($projectIds, 'id');

                $assignedWorkerCount = $this->workers
                ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
                ->whereIn('worker_employment.project_id', $projectIds)
                ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[3])
                ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
                ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                ->whereNull('worker_employment.work_end_date')
                ->whereNull('worker_employment.event_type')
                ->distinct('workers.id')->count('workers.id');

                $assignedWorkerCount++;

                if($assignedWorkerCount > $applicationDeatils->quota_requested) {
                    return self::ERROR_QUOTA;
                }
            }
    }
    /**
     * process TotalManagement Service based on provided request data
     *
     * @param array $request
     *              new_project_id (int) ID of the new transfer project
     *              company_id (array) company ID's of the user
     *              worker_id (int) ID of the worker
     *              from_existing (int) from existing
     *              new_prospect_id (int) ID of the new prospect
     *              current_project_id (int) current project ID of the worker
     * 
     * @return array
     */
    private function processTotalManagementService($request)
    {
        $projectDetails = $this->totalManagementProject->findOrFail($request['new_project_id']);
            $applicationDetails = $this->totalManagementApplications::whereIn('company_id', $request['company_id'])->find($projectDetails->application_id);
            if(is_null($applicationDetails)){
                return self::ERROR_UNAUTHORIZED;
            }
            $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
            
            if($serviceDetails->from_existing == self::FROM_EXISTING) {
                $workerDetail = $this->workers->findOrFail($request['worker_id']);
                if($request['from_existing'] == self::NOT_FROM_EXISTING) {
                    return self::ERROR_QUOTA_FROM_EXISTING;
                } else if($request['from_existing'] == self::FROM_EXISTING) {
                    if($workerDetail->crm_prospect_id != $request['new_prospect_id']) {
                        return self::ERROR_OTHER_COMPANY;
                    } else if($workerDetail->crm_prospect_id == $request['new_prospect_id']) {
                        $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
                        $currentProjectDetails = $this->totalManagementProject->findOrFail($request['current_project_id']);
                        if($currentProjectDetails->application_id != $projectDetails->application_id) {
                            $workerCountArray['clientWorkersCount']++;
                        }
                        if($workerCountArray['clientWorkersCount'] > $applicationDetails->quota_applied) {
                            return self::ERROR_QUOTA;
                        }
                    }
                }
            } else if($serviceDetails->from_existing == self::NOT_FROM_EXISTING) {
                $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
                $workerDetail = $this->workers->findOrFail($request['worker_id']);
                if($request['from_existing'] == self::FROM_EXISTING) {
                    // if($workerDetail->crm_prospect_id != $request['new_prospect_id']) { // Waiting for confirmation
                        return self::ERROR_FROM_EXISTING_WORKER;
                    /*} else if($workerDetail->crm_prospect_id == $request['new_prospect_id']) { // Waiting for confirmation
                        $workerCountArray['clientWorkersCount']++;
                        if($workerCountArray['clientWorkersCount'] > $serviceDetails->client_quota) {
                            return [
                                'clientQuotaError' => true
                            ];
                        }
                    }*/
                } else if($request['from_existing'] == self::NOT_FROM_EXISTING) {
                    if($workerDetail->crm_prospect_id == 0) {
                        $workerCountArray['fomnextWorkersCount']++;
                        if($workerCountArray['fomnextWorkersCount'] > $serviceDetails->fomnext_quota) {
                            return self::ERROR_FOMNEXT_QUOTA;
                        }
                    } else if($workerDetail->crm_prospect_id != 0) {
                        if($workerDetail->crm_prospect_id != $request['new_prospect_id']) {
                            return self::ERROR_OTHER_COMPANY;
                        } else if($workerDetail->crm_prospect_id == $request['new_prospect_id']) {
                            $workerCountArray['clientWorkersCount']++;
                            if($workerCountArray['clientWorkersCount'] > $serviceDetails->client_quota) {
                                return self::ERROR_CLIENT_QUOTA;
                            }
                        }
                    }
                }
            }
    }
    /**
     * update the worker transfer record based on provided request data
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              service_type (string) type of the service
     *              modified_by (int) modified user ID
     * 
     * @return void
     */
    private function updateWorkerTransferDetail($request)
    {
        $this->workers->where([
            'id' => $request['worker_id'],
        ])->update([
            'crm_prospect_id' => $request['new_prospect_id'], 
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);
        if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]){
            $worker = $this->workers->findOrFail($request['worker_id']);
            $worker->crm_prospect_id = 0;
            $worker->updated_at = Carbon::now();
            $worker->modified_by = $request['modified_by'];
            $worker->module_type = $request['service_type'];
            $worker->econtract_status = self::WORKER_STATUS_ASSIGNED;
            if(in_array($worker->total_management_status, Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))) {
                $worker->total_management_status = self::WORKER_STATUS_ONBENCH;
            }
            $worker->save();
        } else if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]){
            $worker = $this->workers->findOrFail($request['worker_id']);
            $worker->updated_at = Carbon::now();
            $worker->modified_by = $request['modified_by'];
            $worker->module_type = $request['service_type'];
            $worker->total_management_status = self::WORKER_STATUS_ASSIGNED;
            if(in_array($worker->econtract_status, Config::get('services.ECONTRACT_WORKER_STATUS'))) {
                $worker->econtract_status = self::WORKER_STATUS_ONBENCH;
            }
            $worker->save();
        }
    }
    /**
     * update the worker employment record based on provided request data
     *
     * @param array $request
     *              current_project_id (int) current project ID of the worker
     *              worker_id (int) ID of the worker
     *              last_working_day (date) last working date of the worker
     *              modified_by (int) modified user ID
     *              new_project_id (int) new transfer project ID
     *              accommodation_provider_id (int) ID of the accommodation
     *              accommodation_unit_id (int) ID of the accommodation unit
     *              department (string) department of worker
     *              sub_department (string) sub department of worker
     *              new_joining_date (date) date of worker joining date
     *              service_type (string) type of the service
     * 
     * @return void
     */
    private function updateWorkerEmploymentDetail($request)
    {
        // UPDATE WORKER EMPLOYMENT TABLE
        $this->workerEmployment->where([
            'project_id' => $request['current_project_id'],
            'worker_id' => $request['worker_id']
        ])->update([
            'work_end_date' => $request['last_working_day'],
            'transfer_flag' => self::ACTIVE_TRANSFER_FLAG,
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);

        // CREATE A RECORD WORKER EMPLOYMENT TABLE
        $this->workerEmployment->create([
            'worker_id' => $request['worker_id'],
            'project_id' => $request['new_project_id'],
            'accommodation_provider_id' => $request['accommodation_provider_id'] ?? null,
            'accommodation_unit_id' => $request['accommodation_unit_id'] ?? null,
            'department' => $request['department'] ?? null,
            'sub_department' => $request['sub_department'] ?? null,
            'work_start_date' => $request['new_joining_date'],
            'service_type' => $request['service_type'],
            'transfer_flag' => self::DEFAULT_TRANSFER_FLAG,
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);
    }
    /**
     * Retrive the worker count based on request data
     * 
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

        $clientWorkersCount = $this->getClientWorkersCount($prospectId,$projectIds);
        $fomnextWorkersCount = $this->getFomnextWorkersCount($projectIds);

        return [
            'clientWorkersCount' => $clientWorkersCount,
            'fomnextWorkersCount' => $fomnextWorkersCount
        ];
    }
    /**
     * Retrieve cleint worker count.
     *
     * @param $prospectId
     * @param $projectIds
     * @return mixed
     */
    private function getClientWorkersCount($prospectId,$projectIds)
    {
        return $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[2])
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date')
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type');
        })
        ->where('workers.crm_prospect_id', $prospectId)
        ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
        ->whereIn('worker_employment.project_id', $projectIds)
        ->distinct('workers.id')
        ->count('workers.id');
    }
    /**
     * Retrieve formnext worker count.
     *
     * @param $projectIds
     * @return mixed
     */
    private function getFomnextWorkersCount($projectIds)
    {
        return $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[2])
            ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date')
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type');
        })
        ->where('workers.crm_prospect_id', 0)
        ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
        ->where('worker_employment.project_id', $projectIds)
        ->distinct('workers.id')
        ->count('workers.id');
    }
}
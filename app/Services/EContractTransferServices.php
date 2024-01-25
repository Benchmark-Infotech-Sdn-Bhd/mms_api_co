<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerEmployment;
use App\Models\CRMProspect;
use App\Models\EContractProject;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\AuthServices;
use App\Models\TotalManagementProject;
use App\Models\EContractApplications;
use App\Models\TotalManagementApplications;
use App\Models\CRMProspectService;

class EContractTransferServices
{   
    public const PROSPECT_SERVICES_ID = 1;
    public const PROSPECT_STATUS = 1;
    public const SERVICE_FROM_EXISTING_0 = 0;
    public const SERVICE_FROM_EXISTING_1 = 1;
    public const TRANSFER_FLAG_0 = 0;
    public const TRANSFER_FLAG_1 = 1;
    public const CRM_PROSPECT_ID_0 = 0;
    public const USER_TYPE_CUSTOMER = 'Customer';
    public const SERVICE_TYPE_ECONTRACT = 'e-Contract';
    public const TOTAL_MANAGEMENT = 'Total Management';
    public const WORKER_EMPLOYMENT_0 = 0;

    /**
     * @var Workers
     */
    private Workers $workers;

    /**
     * @var WorkerEmployment
     */
    private WorkerEmployment $workerEmployment;

    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;

    /**
     * @var EContractProject
     */
    private EContractProject $eContractProject;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;

    /**
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;

    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;

    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;

    /**
     * Constructs a new instance of the class.
     * 
     * @param Workers $workers The workers object.
     * @param WorkerEmployment $workerEmployment The worker employment object.
     * @param CRMProspect $crmProspect The crm prospect object.
     * @param EContractProject $eContractProject The e-contract project object.
     * @param AuthServices $authServices The auth services object.
     * @param TotalManagementProject $totalManagementProject The total management project object.
     * @param EContractApplications $eContractApplications The e-contract applications object.
     * @param TotalManagementApplications $totalManagementApplications The total management applications object.
     * @param CRMProspectService $crmProspectService The crm prospect service object.
     */
    public function __construct(
        Workers $workers, 
        WorkerEmployment $workerEmployment, 
        CRMProspect $crmProspect, 
        EContractProject $eContractProject, 
        AuthServices $authServices, 
        TotalManagementProject $totalManagementProject, 
        EContractApplications $eContractApplications, 
        TotalManagementApplications $totalManagementApplications, 
        CRMProspectService $crmProspectService
    )
    {
        $this->workers = $workers;
        $this->workerEmployment = $workerEmployment;
        $this->crmProspect = $crmProspect;
        $this->eContractProject = $eContractProject;
        $this->authServices = $authServices;
        $this->totalManagementProject = $totalManagementProject;
        $this->eContractApplications = $eContractApplications;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
    }

    /**
     * Creates the validation rules for transferring a new worker.
     *
     * @return array The array containing the validation rules.
     */
    public function submitValidation(): array
    {
        return [
            'worker_id' => 'required',
            'current_project_id' => 'required',
            'new_project_id' => 'required',
            'new_prospect_id' => 'required',
            'accommodation_provider_id' => 'required|regex:/^[0-9]*$/',
            'accommodation_unit_id' => 'required|regex:/^[0-9]*$/',
            'last_working_day' => 'required|date|date_format:Y-m-d',
            'new_joining_date' => 'required|date|date_format:Y-m-d',
            'service_type' => 'required'
        ];
    }

    /**
     * Returns a paginated list of crm prospect based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of crm prospect.
     */
    public function companyList($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('sectors', 'sectors.id', 'crm_prospect_services.sector_id')
        ->where('crm_prospects.status', self::PROSPECT_STATUS)
        ->where('crm_prospect_services.service_id', '!=', self::PROSPECT_SERVICES_ID)
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == self::USER_TYPE_CUSTOMER) {
                $query->where('crm_prospects.id', '=', $user['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            if (isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->where(function ($query) use ($request) {
            if (isset($request['filter']) && !empty($request['filter'])) {
                $query->where('crm_prospect_services.service_id', $request['filter'])
                ->where('crm_prospect_services.deleted_at', NULL);
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospect_services.service_id', 'sectors.sector_name')
        ->selectRaw("(CASE WHEN (crm_prospect_services.service_id = 1) THEN 'Direct Recruitment' WHEN (crm_prospect_services.service_id = 2) THEN 'e-Contract' ELSE 'Total Management' END) as service_type, crm_prospect_services.id as prospect_service_id")
        ->distinct('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospect_services.service_id', 'sectors.sector_name', 'crm_prospect_services.id')
        ->orderBy('crm_prospects.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Returns a paginated list of e-contract project based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of e-contract project.
     */
    public function projectList($request): mixed 
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($request['service_type'] == self::SERVICE_TYPE_ECONTRACT) {
            return $this->eContractProject
            ->Join('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'e-contract_applications.service_id')
            ->where('crm_prospect_services.crm_prospect_id',$request['crm_prospect_id'])
            ->where('crm_prospect_services.id',$request['prospect_service_id'])
            ->where('e-contract_applications.company_id',$user['company_id'])
            ->select('e-contract_project.id', 'e-contract_project.name')
            ->distinct('e-contract_project.id')
            ->orderBy('e-contract_project.id', 'desc')
            ->get();
        } else if ($request['service_type'] == self::TOTAL_MANAGEMENT) {
            return $this->totalManagementProject
            ->Join('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', '=', 'total_management_applications.service_id')
            ->where('crm_prospect_services.crm_prospect_id',$request['crm_prospect_id'])
            ->where('crm_prospect_services.id',$request['prospect_service_id'])
            ->where('total_management_applications.company_id',$user['company_id'])
            ->where('crm_prospect_services.from_existing', self::SERVICE_FROM_EXISTING_0)
            ->select('total_management_project.id', 'total_management_project.name')
            ->distinct('total_management_project.id')
            ->orderBy('total_management_project.id', 'desc')
            ->get();
        }
    }

    /**
     * Show the worker with related prospect and employment.
     * 
     * @param array $request The request data containing worker id, company id
     * @return mixed Returns the worker with related prospect and employment.
     */
    public function workerEmploymentDetail($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();

        return $this->workers
                ->leftJoin('crm_prospects', 'crm_prospects.id', 'workers.crm_prospect_id')
                ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
                ->where('workers.id', $request['worker_id'])
                ->where('worker_employment.transfer_flag', self::TRANSFER_FLAG_0)
                ->where('workers.company_id', $user['company_id'])
                ->select('workers.id', 'workers.crm_prospect_id as company_id', 'crm_prospects.company_name', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id', 'worker_employment.department', 'worker_employment.sub_department', 'worker_employment.work_start_date', 'worker_employment.work_end_date', 'worker_employment.service_type', 'worker_employment.transfer_flag')
                ->get();
    }

    /**
     * Transferring a new worker from the given request data.
     * 
     * @param $request The request data containing worker details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if check worker is null.
     * - "projectExist": A array returns project exist if worker employment is null.
     * - "isSubmit": A boolean indicating if the e-contract cost was successfully created.
     */
    public function submit($request): array|bool
    {
        $validator = Validator::make($request, $this->submitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];
        $request['modified_by'] = $user['id'];

        $checkWorker = $this->workers->where('company_id', $request['company_id'])->first();
        if (is_null($checkWorker)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $workerEmployment = $this->workerEmployment->where([
            ['worker_id', $request['worker_id']], ['project_id', $request['new_project_id']], ['service_type', $request['service_type']]
        ])
        ->where('transfer_flag', self::TRANSFER_FLAG_0)->whereNull('remove_date')
        ->count();
        if ($workerEmployment > self::WORKER_EMPLOYMENT_0) {
            return [
                'projectExist' => true
            ];
        }

        if ($request['service_type'] == self::SERVICE_TYPE_ECONTRACT) {

            $this->submitEContractProcess($request);

        } else if ($request['service_type'] == self::TOTAL_MANAGEMENT) {

            $this->submitTotalManagementProcess($request);

        }

        // UPDATE WORKERS TABLE
        $this->updateWorkers($request);

        // UPDATE WORKER EMPLOYMENT TABLE
        $this->updateWorkerEmployment($request);

        return true;
    }

    /**
     * @param $applicationId, $prospectId
     * @return array
     */

    /**
     * Returns a count of fomnext workers based on the given application id and prospect id.
     * 
     * @param int $applicationId and $prospectId
     * @return array Returns a count of fomnext workers.
     */
    public function getWorkerCount($applicationId, $prospectId): array
    {
        $projectIds = $this->totalManagementProject->where('application_id', $applicationId)
                            ->select('id')
                            ->get()->toArray();

        $projectIds = array_column($projectIds, 'id');
        $fomnextWorkersCount = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.service_type', self::TOTAL_MANAGEMENT)
            ->where('worker_employment.transfer_flag', self::TRANSFER_FLAG_0)
            ->whereNull('worker_employment.remove_date')
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type');
        })
        ->where('workers.crm_prospect_id', self::CRM_PROSPECT_ID_0)
        ->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'))
        ->where('worker_employment.project_id', $projectIds)
        ->distinct('workers.id')
        ->count('workers.id');

        return [
            'fomnextWorkersCount' => $fomnextWorkersCount
        ];
    }
    
    /**
     * Creates a new e-contract process from the given request data.
     * 
     * @param $request The request data containing e-contract details.
     * @return array Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if project details is null.
     * - "quotaError": A array returns quotaError if the application quota requested count is greater than assigned worker count.
     */
    public function submitEContractProcess($request)
    {
        $projectDetails = $this->showEContractProject($request);
        if (is_null($projectDetails)) {
            return [
                'unauthorizedError' => true
            ];
        }
        
        $applicationDeatils = $this->eContractApplications->findOrFail($projectDetails->application_id);
        $projectIds = $this->eContractProject->where('application_id', $projectDetails->application_id)->select('id')
                        ->get()->toArray();

        $projectIds = array_column($projectIds, 'id');
        $assignedWorkerCount = $this->workers
        ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
        ->whereIn('worker_employment.project_id', $projectIds)
        ->where('worker_employment.service_type', self::SERVICE_TYPE_ECONTRACT)
        ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
        ->where('worker_employment.transfer_flag', self::TRANSFER_FLAG_0)
        ->whereNull('worker_employment.work_end_date')
        ->whereNull('worker_employment.event_type')
        ->distinct('workers.id')->count('workers.id');

        $assignedWorkerCount++;

        if ($assignedWorkerCount > $applicationDeatils->quota_requested) {
            return [
                'quotaError' => true
            ];
        }
    }
    
    /**
     * Show the e-contract project with related application.
     * 
     * @param array $request The request data containing project id, company id
     * @return mixed Returns the e-contract project with related application.
     */
    public function showEContractProject($request): mixed
    {
        return $this->eContractProject::join('e-contract_applications', function($query) use($request) {
                $query->on('e-contract_applications.id','=','e-contract_project.application_id')
                ->where('e-contract_applications.company_id', $request['company_id']);
            })
            ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.created_by', 'e-contract_project.modified_by', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at', 'e-contract_project.deleted_at')
            ->find($request['new_project_id']);
    }
    
    /**
     * Creates a new total management process from the given request data.
     * 
     * @param $request The request data containing total management details.
     * @return array Returns an array with the following keys:
     * - "quotaFromExistingError": A array returns quotaFromExistingError if service details fromExisting is equal to 1.
     * - "fomnextQuotaError": A array returns fomnextQuotaError if the fomnext quota count is greater than fomnext workers count.
     * - "otherCompanyError": A array returns otherCompanyError if worker detail crmprospect id is equal to 0.
     */
    public function submitTotalManagementProcess($request)
    {
        $projectDetails = $this->totalManagementProject->findOrFail($request['new_project_id']);
        $applicationDetails = $this->totalManagementApplications->findOrFail($projectDetails->application_id);
        $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
        if ($serviceDetails->from_existing == self::SERVICE_FROM_EXISTING_1) {
            return [
                'quotaFromExistingError' => true
            ];
        } else if ($serviceDetails->from_existing == self::SERVICE_FROM_EXISTING_0) {
            $workerCountArray = $this->getWorkerCount($projectDetails->application_id, $applicationDetails->crm_prospect_id);
            $workerDetail = $this->workers->findOrFail($request['worker_id']);
            if ($workerDetail->crm_prospect_id == self::CRM_PROSPECT_ID_0) {
                $workerCountArray['fomnextWorkersCount']++;
                if ($workerCountArray['fomnextWorkersCount'] > $serviceDetails->fomnext_quota) {
                    return [
                        'fomnextQuotaError' => true
                    ];
                }
            } else {
                return [
                    'otherCompanyError' => true
                ];
            }
        }
    }
    
    /**
     * Updates the workers from the given request data.
     * 
     * @param array $request The array containing worker data.
     *                      The array should have the following keys:
     *                      - worker_id: The updated worker id.
     *                      - service_type: The updated service type.
     *                      - econtract_status: The updated econtract status.
     *                      - total_management_status: The updated total management status.
     *                      - modified_by: The updated worker modified by.
     */
    public function updateWorkers($request): void
    {
        if (isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]) {
            $worker = $this->workers->findOrFail($request['worker_id']);
            $worker->crm_prospect_id = self::CRM_PROSPECT_ID_0;
            $worker->updated_at = Carbon::now();
            $worker->modified_by = $request['modified_by'];
            $worker->module_type = $request['service_type'];
            $worker->econtract_status = "Assigned";
            if (in_array($worker->total_management_status, ['Assigned', 'Counselling'])) {
                $worker->total_management_status = "On-Bench";
            }
            $worker->save();
        } else if (isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]) {
            $worker = $this->workers->findOrFail($request['worker_id']);
            $worker->updated_at = Carbon::now();
            $worker->modified_by = $request['modified_by'];
            $worker->module_type = $request['service_type'];
            $worker->total_management_status = "Assigned";
            if (in_array($worker->econtract_status, ['Assigned', 'Counselling'])) {
                $worker->econtract_status = "On-Bench";
            }
            $worker->save();
        }
    }
    
    /**
     * Creates a new worker employment from the given request data.
     * 
     * @param array $request The array containing worker employment data.
     *                      The array should have the following keys:
     *                      - project_id: The project id of the employment.
     *                      - worker_id: The worker id of the employment.
     *                      - accommodation_provider_id: The accommodation provider id of the employment.
     *                      - accommodation_unit_id: The accommodation unit id of the employment.
     *                      - department: The department of the employment.
     *                      - sub_department: The sub department of the employment.
     *                      - new_joining_date: The new joining date of the employment.
     *                      - service_type: The service type of the employment.
     *                      - transfer_flag: The transfer flag of the employment.
     *                      - created_by: The ID of the user who created the employment.
     *                      - modified_by: The updated employment modified by.
     */
    public function updateWorkerEmployment($request): void
    {
        $this->workerEmployment->where([
            'project_id' => $request['current_project_id'],
            'worker_id' => $request['worker_id']
        ])->update([
            'work_end_date' => $request['last_working_day'],
            'transfer_flag' => self::TRANSFER_FLAG_1,
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);

        // CREATE A RECORD WORKER EMPLOYMENT TABLE
        $this->workerEmployment->create([
            'worker_id' => $request['worker_id'],
            'project_id' => $request['new_project_id'],
            'accommodation_provider_id' => (isset($request['accommodation_provider_id']) && !empty($request['accommodation_provider_id'])) ? $request['accommodation_provider_id'] : null,
            'accommodation_unit_id' => (isset($request['accommodation_unit_id']) && !empty($request['accommodation_unit_id'])) ? $request['accommodation_unit_id'] : null,
            'department' => (isset($request['department']) && !empty($request['department'])) ? $request['department'] : null,
            'sub_department' => (isset($request['sub_department']) && !empty($request['sub_department'])) ? $request['sub_department'] : null,
            'work_start_date' => $request['new_joining_date'],
            'service_type' => $request['service_type'],
            'transfer_flag' => self::TRANSFER_FLAG_0,
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);
    }

}
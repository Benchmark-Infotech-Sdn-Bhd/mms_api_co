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
use App\Models\EContractProject;
use App\Models\EContractApplications;

class EContractWorkerServices
{
    public const USER_TYPE_CUSTOMER = 'Customer';
    public const SERVICE_TYPE_ECONTRACT = 'e-Contract';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_ONBENCH = 'On-Bench';
    public const EMPLOYMENT_TRANSFER_FLAG = 0;
    public const CRM_PROSPECT_ID = 0;
    public const EMPLOYMENT_STATUS = 0;

    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_QUOTA = ['quotaError' => true];

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
     * @var EContractProject
     */
    private EContractProject $eContractProject;

    /**
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;

    /**
     * Constructor method.
     * 
     * @param Workers $workers Instance of the Workers class.
     * @param Vendor $vendor Instance of the Vendor class.
     * @param Accommodation $accommodation Instance of the Accommodation class.
     * @param WorkerEmployment $workerEmployment Instance of the WorkerEmployment class.
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class.
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class.
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class.
     * @param EContractProject $eContractProject Instance of the EContractProject class.
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class.
     */
    public function __construct(
        Workers                           $workers, 
        Vendor                            $vendor, 
        Accommodation                     $accommodation, 
        WorkerEmployment                  $workerEmployment, 
        TotalManagementApplications       $totalManagementApplications, 
        CRMProspectService                $crmProspectService, 
        DirectrecruitmentApplications     $directrecruitmentApplications, 
        EContractProject                  $eContractProject, 
        EContractApplications             $eContractApplications
    )
    {
        $this->workers = $workers;
        $this->vendor = $vendor;
        $this->accommodation = $accommodation;
        $this->workerEmployment = $workerEmployment;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->eContractProject = $eContractProject;
        $this->eContractApplications = $eContractApplications;
    }

    /**
     * Creates the validation rules for workers list search.
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
     * Creates the validation rules for removing the worker from project.
     *
     * @return array The array containing the validation rules.
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
     * Creates the validation rules for assigning a new worker to project.
     *
     * @return array The array containing the validation rules.
     */
    public function createValidation(): array
    {
        return [
            'department' => 'regex:/^[a-zA-Z ]*$/',
            'sub_department' => 'regex:/^[a-zA-Z ]*$/',
            'work_start_date' => 'required|date|date_format:Y-m-d'
        ];
    }

    /**
     * Returns a paginated list of workers based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid. Otherwise, returns a paginated list of workers.
     */
    public function list($request): mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where(function ($query) use ($request) {
                $this->applyWorkerFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applyUserFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_employment.department', 'workers.status', 'workers.econtract_status', 'worker_employment.status as worker_assign_status', 'worker_employment.remove_date', 'worker_employment.remarks', 'workers.crm_prospect_id', 'worker_employment.project_id')
            ->distinct('workers.id')->orderBy('workers.id','DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Returns a paginated list of assign worker based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid. Otherwise, returns a paginated list of assign worker.
     */
    public function workerListForAssignWorker($request): mixed
    {
        $validationResult = $this->workerListForAssignWorkerValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->where(function ($query) use ($request) {
                $this->applyAssignWorkerFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applyAssignUserFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applyAssignSearchFilter($query, $request);
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'workers.created_at')
            ->distinct()
            ->orderBy('workers.created_at','DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Assigning a new worker to project from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract project details is null.
     * - "isSubmit": A boolean indicating if the workers are assigned successfully updated.
     */
    public function assignWorker($request): array|bool
    {
        $validationResult = $this->assignWorkerValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];

        if (isset($request['workers']) && !empty($request['workers'])) {

            $projectDetails = $this->showEContractProject(['id' => $request['project_id'], 'company_id' => $user['company_id']]);
            if (is_null($projectDetails)) {
                return self::ERROR_UNAUTHORIZED;
            }
            
            $applicationDeatils = $this->showEContractApplications($projectDetails->application_id);
            $projectIds = $this->showEContractProjectApplications($projectDetails->application_id);

            $projectIds = array_column($projectIds, 'id');
            $assignedWorkerCount = $this->getAssignedWorkerCount($projectIds);
            $assignedWorkerCount += count($request['workers']);
            if ($assignedWorkerCount > $applicationDeatils->quota_requested) {
                return self::ERROR_QUOTA;
            }

            $this->createAssignWorkerEmployment($request);

        }

        return true;
    }

    /**
     * Removing a worker from project from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract project details is null.
     * - "isSubmit": A boolean indicating if the worker removed successfully updated.
     */
    public function removeWorker($request): array|bool
    {
        $validationResult = $this->removeWorkerValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];

        $projectDetails = $this->showEContractProject(['id' => $request['project_id'], 'company_id' => $user['company_id']]);
        if (is_null($projectDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }
        
        $workerDetails =$this->showWorkerEmployment($request);

        $this->updateRemoveWorkerEmployment($request);

        return true;
    }

    /**
     * Assigning a new worker to project from the given request data.
     *
     * @param array $request The array containing project and worker data.
     *                      The array should have the following keys:
     *                      - worker_id: The worker of the project.
     *                      - econtract_status: The status of the project.
     *                      - modified_by: The updated project modified by.
     *
     */
    public function createAssignWorkerEmployment($request)
    {
        $this->createWorkerEmployment($request);

        $this->workers->whereIn('id', $request['workers'])
        ->update([
            'econtract_status' => self::STATUS_ASSIGNED,
            'modified_by' => $request['created_by']
        ]);
    }
    
    /**
     * Removing a worker from project from the given request data.
     *
     * @param array $request The request containing the updated project and worker data.
     *               - worker_id: (int) The updated worker id.
     *               - econtract_status: (int) The updated econtract status.
     *               - modified_by: (int) The updated project modified by.
     */
    public function updateRemoveWorkerEmployment($request)
    {
        $this->updateWorkerEmployment($request);

        $this->workers->where('id', $request['worker_id'])
        ->update([
            'econtract_status' => self::STATUS_ONBENCH,
            'modified_by' => $request['modified_by']
        ]);
    }
    
    /**
     * Show the e-contract project with related attachment and project, application.
     * 
     * @param array $request The request data containing e-contract project id, company id
     * @return mixed Returns the e-contract project with related attachment and application.
     */
    public function showEContractProject($request): mixed
    {
        return $this->eContractProject->join('e-contract_applications', 
            function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
                ->where('e-contract_applications.company_id', $request['company_id']);
            })
            ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.created_by', 'e-contract_project.modified_by', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at', 'e-contract_project.deleted_at')
            ->find($request['id']);
    }

    private function listValidateRequest($request): array|bool
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return true;
    }

    private function applyWorkerFilter($query, $request)
    {
        $query->where('e-contract_project.id', $request['project_id'])
            ->where('worker_employment.service_type', self::SERVICE_TYPE_ECONTRACT)
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', self::EMPLOYMENT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date')
            ->whereIn('workers.company_id', $request['company_id']);
    }

    private function applyUserFilter($query, $request)
    {
        if ($request['user']['user_type'] == self::USER_TYPE_CUSTOMER) {
            $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
        }
    }

    private function applySearchFilter($query, $request)
    {
        if (isset($request['search']) && $request['search']) {
            $query->where('workers.name', 'like', '%' . $request['search'] . '%');
            $query->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%');
            $query->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%');
            $query->orWhere('worker_employment.department', 'like', '%' . $request['search'] . '%');
        }
    }

    private function workerListForAssignWorkerValidateRequest($request): array|bool
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return true;
    }

    private function applyAssignWorkerFilter($query, $request)
    {
        $query->where('workers.total_management_status', self::STATUS_ONBENCH)
            ->where('workers.econtract_status', self::STATUS_ONBENCH)
            ->where('workers.crm_prospect_id', self::CRM_PROSPECT_ID)
            ->whereIn('workers.company_id', $request['company_id']);
    }

    private function applyAssignUserFilter($query, $request)
    {
        if ($request['user']['user_type'] == self::USER_TYPE_CUSTOMER) {
            $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
        }
    }

    private function applyAssignSearchFilter($query, $request)
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $query->where('workers.name', 'like', '%'.$request['search'].'%')
            ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
            ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
            ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
        }
    }

    private function assignWorkerValidateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->createValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    private function showEContractApplications($application_id)
    {
        return $this->eContractApplications->findOrFail($application_id);
    }

    private function showEContractProjectApplications($application_id)
    {
        return $this->eContractProject->where('application_id', $application_id)->select('id')
            ->get()
            ->toArray();
    }

    private function getAssignedWorkerCount($projectIds)
    {
        return $this->workers
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->whereIn('worker_employment.project_id', $projectIds)
            ->where('worker_employment.service_type', self::SERVICE_TYPE_ECONTRACT)
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', self::EMPLOYMENT_TRANSFER_FLAG)
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type')
            ->distinct('workers.id')->count('workers.id');
    }

    private function removeWorkerValidateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->removeValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    private function showWorkerEmployment($request)
    {
        return $this->workerEmployment->where("worker_id", $request['worker_id'])
            ->where("project_id", $request['project_id'])
            ->where("service_type", self::SERVICE_TYPE_ECONTRACT)
            ->get();
    }
    
    /**
     * Assigning a new worker to project from the given request data.
     *
     * @param array $request The array containing project and worker data.
     *                      The array should have the following keys:
     *                      - worker_id: The worker of the project.
     *                      - project_id: The project id of the project.
     *                      - department: The department of the project.
     *                      - sub_department: The sub department of the project.
     *                      - work_start_date: The work start date of the project.
     *                      - service_type: The service type of the project.
     *                      - created_by: The ID of the user who created the project.
     *                      - modified_by: The updated project modified by.
     *
     */
    private function createWorkerEmployment($request)
    {
        foreach ($request['workers'] as $workerId) {
            $this->workerEmployment->create([
                'worker_id' => $workerId,
                'project_id' => $request['project_id'],
                'department' => $request['department'],
                'sub_department' => $request['sub_department'],
                'work_start_date' => $request['work_start_date'],
                'service_type' => self::SERVICE_TYPE_ECONTRACT,
                'created_by' => $request['created_by'],
                'modified_by' => $request['created_by']
            ]);
        }
    }
    
    /**
     * Removing a worker from project from the given request data.
     *
     * @param array $request The request containing the updated project and worker data.
     *               - worker_id: (int) The updated worker id.
     *               - status: (string) The updated status.
     *               - work_end_date: (int) The updated work end date.
     *               - remove_date: (string) The updated remove date.
     *               - remarks: (int) The updated remarks.
     *               - econtract_status: (int) The updated econtract status.
     *               - modified_by: (int) The updated project modified by.
     */
    private function updateWorkerEmployment($request)
    {
        $this->workerEmployment->where("worker_id", $request['worker_id'])
        ->where("project_id", $request['project_id'])
        ->where("service_type", self::SERVICE_TYPE_ECONTRACT)
        ->update([
            'status' => self::EMPLOYMENT_STATUS,
            'work_end_date' => $request['last_working_day'],
            'remove_date' => $request['remove_date'],
            'remarks' => $request['remarks']
        ]);
    }
}
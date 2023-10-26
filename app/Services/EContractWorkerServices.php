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

class EContractWorkerServices
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
     * @var EContractProject
     */
    private EContractProject $eContractProject;
    /**
     * TotalManagementWorkerServices constructor.
     * @param Workers $workers
     * @param Vendor $vendor
     * @param Accommodation $accommodation
     * @param WorkerEmployment $workerEmployment
     * @param TotalManagementApplications $totalManagementApplications
     * @param CRMProspectService $crmProspectService
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param EContractProject $eContractProject
     */
    public function __construct(Workers $workers, Vendor $vendor, Accommodation $accommodation, WorkerEmployment $workerEmployment, TotalManagementApplications $totalManagementApplications, CRMProspectService $crmProspectService, DirectrecruitmentApplications $directrecruitmentApplications, EContractProject $eContractProject)
    {
        $this->workers = $workers;
        $this->vendor = $vendor;
        $this->accommodation = $accommodation;
        $this->workerEmployment = $workerEmployment;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->crmProspectService = $crmProspectService;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->eContractProject = $eContractProject;
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
     * @return array
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
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('e-contract_project.id', $request['project_id'])
            ->where('worker_employment.service_type', 'e-Contract')
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
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
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_employment.department', 'workers.status', 'workers.econtract_status', 'worker_employment.status as worker_assign_status', 'worker_employment.remove_date', 'worker_employment.remarks', 'workers.crm_prospect_id', 'worker_employment.project_id')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
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
        return $this->workers->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->where('workers.total_management_status', 'On-Bench')
            ->where('workers.econtract_status', 'On-Bench')
            ->where('workers.crm_prospect_id', 0)
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
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
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number')
            ->distinct()
            ->orderBy('workers.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function assignWorker($request): array|bool
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];

        if(isset($request['workers']) && !empty($request['workers'])) {

            $applicationQuotaRequested = $this->eContractProject
            ->leftJoin('e-contract_applications', 'e-contract_applications.id', 'e-contract_project.application_id')
            ->where('e-contract_project.id',$request['project_id'])
            ->select('e-contract_applications.quota_requested')->get();

            $quotaRequested = isset($applicationQuotaRequested[0]['quota_requested']) ?$applicationQuotaRequested[0]['quota_requested'] : 0;

            $assignedWorkerCount = $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
            ->where('worker_employment.project_id', $request['project_id'])
            ->where('worker_employment.service_type', 'e-Contract')
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.work_end_date')
            ->whereNull('worker_employment.event_type')
            ->distinct('workers.id')->count('workers.id');

            $assignedWorkerCount += count($request['workers']);

            if($assignedWorkerCount > $quotaRequested) {
                return [
                    'quotaError' => true
                ];
            }

            foreach ($request['workers'] as $workerId) {
                $this->workerEmployment->create([
                    'worker_id' => $workerId,
                    'project_id' => $request['project_id'],
                    'department' => $request['department'],
                    'sub_department' => $request['sub_department'],
                    'work_start_date' => $request['work_start_date'],
                    'service_type' => 'e-Contract',
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'econtract_status' => 'Assigned',
                    'modified_by' => $request['created_by']
                ]);
        }
        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function removeWorker($request): array|bool
    {
        $validator = Validator::make($request, $this->removeValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];

        $workerDetails = $this->workerEmployment->where("worker_id", $request['worker_id'])
                        ->where("project_id", $request['project_id'])
                        ->where("service_type", "e-Contract")
                        ->get();

        $this->workerEmployment->where("worker_id", $request['worker_id'])
        ->where("project_id", $request['project_id'])
        ->where("service_type", "e-Contract")
        ->update([
            'status' => 0,
            'work_end_date' => $request['last_working_day'],
            'remove_date' => $request['remove_date'],
            'remarks' => $request['remarks']
        ]);

        $this->workers->where('id', $request['worker_id'])
        ->update([
            'econtract_status' => 'On-Bench',
            'modified_by' => $request['modified_by']
        ]);

        return true;
    }
}
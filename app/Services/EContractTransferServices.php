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

class EContractTransferServices
{
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
     * TotalManagementWorkerServices constructor.
     * @param Workers $workers
     * @param WorkerEmployment $workerEmployment
     * @param CRMProspect $crmProspect
     * @param EContractProject $eContractProject
     * @param AuthServices $authServices
     */
    public function __construct(Workers $workers, WorkerEmployment $workerEmployment, CRMProspect $crmProspect, EContractProject $eContractProject, AuthServices $authServices)
    {
        $this->workers = $workers;
        $this->workerEmployment = $workerEmployment;
        $this->crmProspect = $crmProspect;
        $this->eContractProject = $eContractProject;
        $this->authServices = $authServices;
    }
    /**
     * @return array
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
     * @param $request
     * @return mixed
     */
    public function companyList($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('sectors', 'sectors.id', 'crm_prospect_services.sector_id')
        ->where('crm_prospects.status', 1)
        ->where('crm_prospect_services.service_id', '!=', 1)
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->where(function ($query) use ($request) {
            if(isset($request['filter']) && !empty($request['filter'])) {
                $query->where('crm_prospect_services.service_id', $request['filter'])
                ->where('crm_prospect_services.deleted_at', NULL);
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospect_services.service_id', 'sectors.sector_name')
        ->selectRaw("(CASE WHEN (crm_prospect_services.service_id = 1) THEN 'Direct Recruitment' WHEN (crm_prospect_services.service_id = 2) THEN 'E-Contract' ELSE 'Total Management' END) as service_type")
        ->distinct('crm_prospects.id')
        ->orderBy('crm_prospects.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function projectList($request): mixed
    {
        return $this->eContractProject
        ->leftJoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
        ->where('crm_prospects.id',$request['crm_prospect_id'])
        ->select('e-contract_project.id', 'e-contract_project.name')
        ->distinct('e-contract_project.id')
        ->orderBy('e-contract_project.id', 'desc')
        ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workerEmploymentDetail($request): mixed
    {
        return $this->workers
                ->leftJoin('crm_prospects', 'crm_prospects.id', 'workers.crm_prospect_id')
                ->leftJoin('worker_employment', 'worker_employment.worker_id', 'workers.id')
                ->where('workers.id', $request['worker_id'])
                ->where('worker_employment.transfer_flag', 0)
                ->select('workers.id', 'workers.crm_prospect_id as company_id', 'crm_prospects.company_name', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id', 'worker_employment.department', 'worker_employment.sub_department', 'worker_employment.work_start_date', 'worker_employment.work_end_date', 'worker_employment.service_type', 'worker_employment.transfer_flag')
                ->get();
    }
    /**
     * @param $request
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

        // CHECK WORKER EMPLOYMENT DATA - SAME PROJECT ID
        $workerEmployment = $this->workerEmployment->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['new_project_id']]
        ])
        ->where('transfer_flag', 0)
        ->whereNull('remove_date')
        ->count();

        if($workerEmployment > 0) {
            return [
                'projectExist' => true
            ];
        }

        // UPDATE WORKERS TABLE
        $this->workers->where([
            'id' => $request['worker_id'],
        ])->update([
            'crm_prospect_id' => $request['new_prospect_id'], 
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);

        // UPDATE WORKER EMPLOYMENT TABLE
        $this->workerEmployment->where([
            'project_id' => $request['current_project_id'],
            'worker_id' => $request['worker_id']
        ])->update([
            'work_end_date' => $request['last_working_day'],
            'transfer_flag' => 1,
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
            'transfer_flag' => 0,
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);

        return true;
    }
}
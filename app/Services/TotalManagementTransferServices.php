<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerEmployment;
use App\Models\CRMProspect;
use App\Models\TotalManagementProject;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TotalManagementTransferServices
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
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;

    /**
     * TotalManagementWorkerServices constructor.
     * @param Workers $workers
     * @param WorkerEmployment $workerEmployment
     * @param CRMProspect $crmProspect
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(Workers $workers, WorkerEmployment $workerEmployment, CRMProspect $crmProspect, TotalManagementProject $totalManagementProject)
    {
        $this->workers = $workers;
        $this->workerEmployment = $workerEmployment;
        $this->crmProspect = $crmProspect;
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * @return array
     */
    public function submitValidation(): array
    {
        return [
            'worker_employment_id' => 'required',
            'worker_id' => 'required',
            'project_id' => 'required',
            'accommodation_provider_id' => 'required|regex:/^[0-9]*$/',
            'accommodation_unit_id' => 'required|regex:/^[0-9]*$/',
            'to_date' => 'required|date|date_format:Y-m-d',
            'transfer_date' => 'required|date|date_format:Y-m-d'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workerEmploymentDetail($request): mixed
    {
        return $this->workers
                    ->leftJoin('crm_prospects', 'crm_prospects.id', 'workers.crm_prospect_id')
                    ->leftJoin('worker_employment', function($query) {
                        $query->on('worker_employment.worker_id','=','workers.id')
                            ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)');
                    })
                    ->where('workers.id', $request['worker_id'])
                    ->select('workers.id', 'workers.worker_status', 'crm_prospects.id as company_id', 'crm_prospects.company_name', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id', 'worker_employment.department', 'worker_employment.sub_department', 'worker_employment.work_start_date', 'worker_employment.work_end_date')
                    ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function companyList($request): mixed
    {
        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('sectors', 'sectors.id', 'crm_prospect_services.sector_id')
        ->where('crm_prospects.status', 1)
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
        return $this->totalManagementProject
        ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
        ->where('crm_prospects.id',$request['crm_prospect_id'])
        ->select('total_management_project.id', 'total_management_project.name')
        ->distinct('total_management_project.id')
        ->orderBy('total_management_project.id', 'desc')
        ->get();
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function submit($request): array|bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];

        $validator = Validator::make($request, $this->submitValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        // CHECK WORKER EMPLOYMENT DATA - SAME PROJECT ID
        $workerEmployment = $this->workerEmployment->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['project_id']]
        ])
        ->whereNull('work_end_date')
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
            'crm_prospect_id' => $request['crm_prospect_id'], 
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);

        // UPDATE WORKER EMPLOYMENT TABLE
        $this->workerEmployment->where([
            'id' => $request['worker_employment_id'],
            'worker_id' => $request['worker_id']
        ])->update([
            'work_end_date' => $request['to_date'], 
            'updated_at' => Carbon::now(), 
            'modified_by' => $request['modified_by']
        ]);

        // CREATE A RECORD WORKER EMPLOYMENT TABLE
        $this->workerEmployment->create([
            'worker_id' => $request['worker_id'],
            'project_id' => $request['project_id'],
            'department' => $request['department'] ?? null,
            'sub_department' => $request['sub_department'] ?? null,
            'accommodation_provider_id' => $request['accommodation_provider_id'],
            'accommodation_unit_id' => $request['accommodation_unit_id'],
            'work_start_date' => $request['transfer_date'],
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);

        return true;
    }
}
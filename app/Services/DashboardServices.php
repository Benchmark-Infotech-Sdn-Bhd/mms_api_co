<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Services\ValidationServices;
use Carbon\Carbon;
use App\Models\CRMProspect;
use App\Models\Workers;
use App\Models\WorkerVisa;

class DashboardServices
{
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * DashboardServices constructor.
     * @param CRMProspect $crmProspect
     * @param ValidationServices $validationServices
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     */
    public function __construct(CRMProspect $crmProspect, ValidationServices $validationServices, Workers $workers, WorkerVisa $workerVisa)
    {
        $this->crmProspect = $crmProspect;
        $this->validationServices = $validationServices;
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        $workerCount = $this->workers->where('crm_prospect_id',0)->count('id');
        $totalManagementOnbench = $this->workers->where('crm_prospect_id',0)->where('total_management_status','On-Bench')->count('id');

        $serviceDirectRecruitment = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->where('crm_prospect_services.service_id', 1)
        ->count('crm_prospect_services.id');

        $serviceEcontract = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->where('crm_prospect_services.service_id', 2)
        ->count('crm_prospect_services.id');

        $serviceTotalManagementCount = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->where('crm_prospect_services.service_id', 3)
        ->count('crm_prospect_services.id');

        $conditionDate = Carbon::now()->addDays(60)->toDateTimeString(); 

        $workerPassportExpired = $this->workers
        ->where('passport_valid_until', '<=', $conditionDate)
        ->count('id');

        $fomemaExpired = $this->workers
        ->where('fomema_valid_until', '<=', $conditionDate)
        ->count('id');

        $visaPermitExpired = $this->workerVisa
        ->where('work_permit_valid_until', '<=', $conditionDate)
        ->count('id');

        return [
            'worker_count' => $workerCount,
            'worker_onbench_count' => $totalManagementOnbench,
            'direct_recruitment_count' => $serviceDirectRecruitment,
            'econtract_count' => $serviceEcontract,
            'totalmanagement_count' => $serviceTotalManagementCount,
            'worker_passport_expired_count' => $workerPassportExpired,
            'fomema_expired_count' =>$fomemaExpired,
            'visa_permit_expired_count' =>$visaPermitExpired
        ];
    }

}
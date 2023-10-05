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
use App\Models\TotalManagementApplications;
use App\Models\EContractApplications;
use App\Models\DirectrecruitmentApplications;

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
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * DashboardServices constructor.
     * @param CRMProspect $crmProspect
     * @param ValidationServices $validationServices
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param TotalManagementApplications $totalManagementApplications
     * @param EContractApplications $eContractApplications
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     */
    public function __construct(CRMProspect $crmProspect, ValidationServices $validationServices, Workers $workers, WorkerVisa $workerVisa, TotalManagementApplications $totalManagementApplications, EContractApplications $eContractApplications, DirectrecruitmentApplications $directrecruitmentApplications)
    {
        $this->crmProspect = $crmProspect;
        $this->validationServices = $validationServices;
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->eContractApplications = $eContractApplications;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        $workerCount = $this->workers->where('crm_prospect_id',0)->count('id');
        $totalManagementOnbench = $this->workers->where('crm_prospect_id',0)->where('total_management_status','On-Bench')->count('id');

        $serviceDirectRecruitment = $this->directrecruitmentApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
        ->where('crm_prospect_services.service_id', 1)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->count('directrecruitment_applications.id');

        $serviceEcontract = $this->eContractApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'e-contract_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
        ->where('crm_prospect_services.service_id', 2)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->count('e-contract_applications.id');

        $serviceTotalManagementCount = $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->where('crm_prospect_services.service_id', 3)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->count('total_management_applications.id');

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
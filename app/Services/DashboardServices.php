<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Workers;
use App\Models\TotalManagementApplications;
use App\Models\EContractApplications;
use App\Models\DirectrecruitmentApplications;

class DashboardServices
{
    public const STATUS_ON_BENCH = 'On-Bench';
    public const TRANSFER_FLAG_0 = 0;
    public const PROSPECT_ID_0 = 0;
    public const SERVICE_ID_1 = 1;
    public const SERVICE_ID_2 = 2;
    public const SERVICE_ID_3 = 3;

    /**
     * @var Workers
     */
    private Workers $workers;

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
     * @param Workers $workers
     * @param TotalManagementApplications $totalManagementApplications
     * @param EContractApplications $eContractApplications
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     */
    public function __construct(
        Workers                           $workers,
        TotalManagementApplications       $totalManagementApplications,
        EContractApplications             $eContractApplications,
        DirectrecruitmentApplications     $directrecruitmentApplications
    )
    {
        $this->workers = $workers;
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
        $workerCount = $this->getworkerCount($request);

        $workerOnbenchCount = $this->getworkerCount(array_merge($request, ['econtractStatus' => self::STATUS_ON_BENCH]));

        $serviceDirectRecruitment = $this->getDirectrecruitmentApplicationCount($request);

        $serviceEcontract =  $this->getEContractApplicationCount($request);

        $serviceTotalManagementCount = $this->getTotalManagementApplicationCount($request);

        $conditionDate = Carbon::now()->addDays(60)->toDateTimeString(); 

        $workerPassportExpired = $this->getWorkerPassportExpiredCount($conditionDate, $request);

        $fomemaExpired = $this->getFomemaExpiredCount($conditionDate, $request);

        $visaPermitExpired = $this->getVisaPermitExpiredCount($conditionDate, $request);

        return [
            'worker_count' => $workerCount,
            'worker_onbench_count' => $workerOnbenchCount,
            'direct_recruitment_count' => $serviceDirectRecruitment,
            'econtract_count' => $serviceEcontract,
            'totalmanagement_count' => $serviceTotalManagementCount,
            'worker_passport_expired_count' => $workerPassportExpired,
            'fomema_expired_count' =>$fomemaExpired,
            'visa_permit_expired_count' =>$visaPermitExpired
        ];
    }

    private function getworkerCount($request)
    {
        return $this->workers
            ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->leftJoin('worker_employment', function ($join) {
                $join->on('workers.id', '=', 'worker_employment.worker_id')
                    ->where('worker_employment.transfer_flag', self::TRANSFER_FLAG_0)
                    ->whereNull('worker_employment.remove_date');
            })
            ->where('workers.crm_prospect_id', self::PROSPECT_ID_0)
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                $this->applyWorkerEcontractStatusFilter($query, $request);
            })
            ->count('workers.id');
    }

    private function applyWorkerEcontractStatusFilter($query, $request){
        if (!empty($request['econtractStatus']) && $request['econtractStatus'] == self::STATUS_ON_BENCH) {
            $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status ELSE 'On-Bench' END) = 'On-Bench'");

        } else {
            $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status ELSE 'On-Bench' END) IN('On-Bench','Assigned','Counselling','e-Run')");
        }
    }

    private function getDirectrecruitmentApplicationCount($request)
    {
        return $this->directrecruitmentApplications
            ->leftJoin('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
            ->whereIn('crm_prospects.company_id', $request['company_id'])
            ->where('crm_prospect_services.service_id', self::SERVICE_ID_1)
            ->where('crm_prospect_services.deleted_at', NULL)
            ->count('directrecruitment_applications.id');
    }

    private function getEContractApplicationCount($request)
    {
        return $this->eContractApplications
            ->leftJoin('crm_prospects', 'crm_prospects.id', 'e-contract_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'e-contract_applications.service_id')
            ->whereIn('crm_prospects.company_id', $request['company_id'])
            ->where('crm_prospect_services.service_id', self::SERVICE_ID_2)
            ->where('crm_prospect_services.deleted_at', NULL)
            ->count('e-contract_applications.id');
    }

    private function getTotalManagementApplicationCount($request)
    {
        return $this->totalManagementApplications
            ->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
            ->whereIn('crm_prospects.company_id', $request['company_id'])
            ->where('crm_prospect_services.service_id', self::SERVICE_ID_3)
            ->where('crm_prospect_services.deleted_at', NULL)
            ->count('total_management_applications.id');
    }

    private function getWorkerPassportExpiredCount($conditionDate, $request)
    {
        return $this->workers
            ->where('passport_valid_until', '<=', $conditionDate)
            ->whereIn('company_id', $request['company_id'])
            ->count('id');
    }

    private function getFomemaExpiredCount($conditionDate, $request)
    {
        return $this->workers
            ->where('fomema_valid_until', '<=', $conditionDate)
            ->whereIn('company_id', $request['company_id'])
            ->count('id');
    }

    private function getVisaPermitExpiredCount($conditionDate, $request)
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where('worker_visa.work_permit_valid_until', '<=', $conditionDate)
            ->count('workers.id');
    }
}
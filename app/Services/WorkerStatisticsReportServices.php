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

class WorkerStatisticsReportServices
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
     * WorkerStatisticsReportServices constructor.
     * @param CRMProspect $crmProspect
     * @param ValidationServices $validationServices
     */
    public function __construct(CRMProspect $crmProspect, ValidationServices $validationServices)
    {
        $this->crmProspect = $crmProspect;
        $this->validationServices = $validationServices;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            if(!($this->validationServices->validate($request,['search' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        $data = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('directrecruitment_applications', 'directrecruitment_applications.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('levy', 'levy.application_id', 'directrecruitment_applications.id')
        ->leftJoin('directrecruitment_application_approval', function($join){
            $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
            ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
            })
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.application_id', '=', 'directrecruitment_application_approval.application_id')
        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'directrecruitment_workers.worker_id')
        ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
        ->leftJoin('workers', 'workers.id', 'worker_arrival.worker_id')
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where('crm_prospect_services.service_id', 1)
        ->whereNotNull('levy.ksm_reference_number')
        ->whereNotNull('directrecruitment_application_approval.ksm_reference_number')
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        });

        if(isset($request['export']) && !empty($request['export']) ){
            $data = $data->select('crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->selectRaw("SUM(CASE WHEN worker_visa.status = 'Processed' THEN 1 ELSE 0 END) AS calling_visa_submitted, SUM(CASE WHEN worker_visa.status = 'Pending' THEN 1 ELSE 0 END) AS calling_visa_pending, SUM(CASE WHEN worker_arrival.arrival_status = 'Not Arrived' THEN 1 ELSE 0 END) AS arrival_status_notarrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Arrived' THEN 1 ELSE 0 END) AS arrival_status_arrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Cancelled' THEN 1 ELSE 0 END) AS arrival_status_cancelled, SUM(CASE WHEN workers.plks_status = 'Pending' THEN 1 ELSE 0 END) AS plks_status_pending, SUM(CASE WHEN workers.plks_status = 'Approved' THEN 1 ELSE 0 END) AS plks_status_approved, SUM(CASE WHEN workers.directrecruitment_status = 'Repatriated' THEN 1 ELSE 0 END) AS workers_status_repatriated")
            ->groupBy('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->orderBy('crm_prospects.id','DESC')
            ->get();
        }else{
            $data = $data->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->selectRaw("SUM(CASE WHEN worker_visa.status = 'Processed' THEN 1 ELSE 0 END) AS calling_visa_submitted, SUM(CASE WHEN worker_visa.status = 'Pending' THEN 1 ELSE 0 END) AS calling_visa_pending, SUM(CASE WHEN worker_arrival.arrival_status = 'Not Arrived' THEN 1 ELSE 0 END) AS arrival_status_notarrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Arrived' THEN 1 ELSE 0 END) AS arrival_status_arrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Cancelled' THEN 1 ELSE 0 END) AS arrival_status_cancelled, SUM(CASE WHEN workers.plks_status = 'Pending' THEN 1 ELSE 0 END) AS plks_status_pending, SUM(CASE WHEN workers.plks_status = 'Approved' THEN 1 ELSE 0 END) AS plks_status_approved, SUM(CASE WHEN workers.directrecruitment_status = 'Repatriated' THEN 1 ELSE 0 END) AS workers_status_repatriated")
            ->groupBy('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->orderBy('crm_prospects.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
        }
        return $data;
    }

}
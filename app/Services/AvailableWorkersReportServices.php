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

class AvailableWorkersReportServices
{
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * AvailableWorkersReportServices constructor.
     * @param CRMProspect $crmProspect
     * @param Workers $workers
     * @param ValidationServices $validationServices
     */
    public function __construct(CRMProspect $crmProspect, Workers $workers, ValidationServices $validationServices)
    {
        $this->crmProspect = $crmProspect;
        $this->workers = $workers;
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
        $data = $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->leftjoin('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'workers.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', '=', 'crm_prospects.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
                ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)');
        })
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['service_id']) && !empty($request['service_id'])) {
                $query->where('crm_prospect_services.service_id', $request['service_id']);
            }
            if(isset($request['status']) && !empty($request['status'])) {
                $query->where('workers.total_management_status', $request['status']);
            }
            if (isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', "%{$request['search']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhere('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
            
        })
        ->whereNotNull('workers.crm_prospect_id');
        if(isset($request['export']) && !empty($request['export']) ){
            $data = $data->select('workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number', 'crm_prospects.company_name', 'crm_prospect_services.service_name', 'crm_prospect_services.sector_name', 'workers.total_management_status')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->get();
        }else{
            $data = $data->select('workers.id','workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number', 'crm_prospects.company_name', 'crm_prospect_services.service_name', 'crm_prospect_services.sector_name', 'workers.total_management_status', 'workers.module_type', 'worker_employment.service_type', 'worker_employment.id as worker_employment_id')
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
        }
        return $data;
    }

}
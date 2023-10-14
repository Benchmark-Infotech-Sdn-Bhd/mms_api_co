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
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id')
                ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)');
        })
        ->leftJoin('total_management_project', 'total_management_project.id', '=', 'worker_employment.project_id')
        ->leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
        ->leftJoin('crm_prospects as total_management_crm', 'total_management_crm.id', '=', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services as total_management_service', 'total_management_service.id', 'total_management_applications.service_id')
        ->leftJoin('e-contract_project as econtract_project', 'econtract_project.id', '=', 'worker_employment.project_id')
        ->leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'econtract_project.application_id')
        ->leftJoin('crm_prospects as econtract_crm', 'econtract_crm.id', '=', 'e-contract_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services as econtract_service', 'econtract_service.id', 'e-contract_applications.service_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->leftjoin('directrecruitment_applications', 'directrecruitment_applications.id', '=', 'directrecruitment_workers.application_id')
        ->leftJoin('crm_prospects as directrecruitment_crm', 'directrecruitment_crm.id', '=', 'directrecruitment_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services as directrecruitment_service', 'directrecruitment_service.id', 'directrecruitment_applications.service_id')
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['service_id']) && !empty($request['service_id'])) {
                $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_id 
                WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_id
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_id 
                ELSE '' END) = '".$request['service_id']."'");
            }
            if (isset($request['search']) && !empty($request['search'])) {
                $query->where('workers.name', 'like', "%{$request['search']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                ->orWhereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name 
                WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name 
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name 
                ELSE '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' END) like '%".$request['search']."%'");
            }
            if(isset($request['status']) && !empty($request['status'])) {
                $query->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status 
                WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status 
                WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN workers.directrecruitment_status
                ELSE 'On-Bench' END) = '".$request['status']."'");
            }
            
        })
        ->whereNotNull('workers.crm_prospect_id');
        if(isset($request['export']) && !empty($request['export']) ){
            $data = $data->select('workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number')
            ->selectRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' END) as company_name,   (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.sector_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.sector_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.sector_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['sector']."' END) as sector_name, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_name 
        ELSE '' END) as service_type, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status 
        WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN workers.directrecruitment_status
        ELSE 'On-Bench' END) as status")
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->get();
        }else{
            $data = $data->select('workers.id','workers.name', 'workers.passport_number', 'workers.gender', 'worker_visa.ksm_reference_number',  'workers.total_management_status', 'workers.module_type', 'worker_employment.id as worker_employment_id')
            ->selectRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_crm.company_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_crm.company_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_crm.company_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' END) as company_name,   (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.sector_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.sector_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.sector_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['sector']."' END) as sector_name, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_service.service_name 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_service.service_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN directrecruitment_service.service_name 
        ELSE '' END) as service_type, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status 
        WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN workers.directrecruitment_status
        ELSE 'On-Bench' END) as status")
            ->distinct('workers.id')
            ->orderBy('workers.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
        }
        return $data;
    }

}
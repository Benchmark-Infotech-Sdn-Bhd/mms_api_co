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
    public const DEFAULT_INTEGER_VALUE_ONE = 1;

    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * Constructor method.
     * 
     * @param CRMProspect $crmProspect Instance of the CRMProspect class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * 
     * @return void
     */
    public function __construct(
        CRMProspect            $crmProspect,
        ValidationServices     $validationServices
    )
    {
        $this->crmProspect = $crmProspect;
        $this->validationServices = $validationServices;
    }

    /**
     * Returns a paginated list of crm prospect based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid. Otherwise, returns a paginated list of crm prospect.
     */  
    public function list($request): mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $data = $this->crmProspect
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
            ->leftJoin('directrecruitment_applications', 'directrecruitment_applications.crm_prospect_id', 'crm_prospects.id')
            ->leftJoin('levy', 'levy.application_id', 'directrecruitment_applications.id')
            ->leftJoin('directrecruitment_application_approval', function($join){
                $this->applyDirectrecruitmentApplicationApprovalTableFilter($join);
            })
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.application_id', '=', 'directrecruitment_application_approval.application_id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'directrecruitment_workers.worker_id')
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
            ->leftJoin('workers', 'workers.id', 'worker_arrival.worker_id')
            ->where(function ($query) use ($request) {
                $this->applyCompanyFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            });

        if(!empty($request['export']) ){
            $data = $this->selectExportData($data);
        } else {
            $data = $this->selectListData($data);
        }

        return $data;
    }
    
    /**
     * Apply the "directrecruitment application approval" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $join The query builder instance
     *
     * @return void
     */
    private function applyDirectrecruitmentApplicationApprovalTableFilter($join)
    {
        $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
    }
    
    /**
     * Apply the "company" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyCompanyFilter($query, $request)
    {
        $query->whereIn('crm_prospects.company_id', $request['company_id'])
            ->where('crm_prospect_services.service_id', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereNotNull('levy.ksm_reference_number')
            ->whereNotNull('directrecruitment_application_approval.ksm_reference_number');
    }
    
    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if(!empty($request['search'])) {
            $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if(!empty($request['search'])){
            if(!($this->validationServices->validate($request,['search' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }
    
    /**
     * Select prospect data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function selectExportData($data)
    {
        return $data->select('crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->selectRaw("SUM(CASE WHEN worker_visa.status = 'Processed' THEN 1 ELSE 0 END) AS calling_visa_submitted, SUM(CASE WHEN worker_visa.status = 'Pending' THEN 1 ELSE 0 END) AS calling_visa_pending, SUM(CASE WHEN worker_arrival.arrival_status = 'Not Arrived' THEN 1 ELSE 0 END) AS arrival_status_notarrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Arrived' THEN 1 ELSE 0 END) AS arrival_status_arrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Cancelled' THEN 1 ELSE 0 END) AS arrival_status_cancelled, SUM(CASE WHEN workers.plks_status = 'Pending' THEN 1 ELSE 0 END) AS plks_status_pending, SUM(CASE WHEN workers.plks_status = 'Approved' THEN 1 ELSE 0 END) AS plks_status_approved, SUM(CASE WHEN workers.directrecruitment_status = 'Repatriated' THEN 1 ELSE 0 END) AS workers_status_repatriated")
            ->groupBy('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->orderBy('crm_prospects.id','DESC')
            ->get();
    }
    
    /**
     * Select prospect data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function selectListData($data)
    {
        return $data->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->selectRaw("SUM(CASE WHEN worker_visa.status = 'Processed' THEN 1 ELSE 0 END) AS calling_visa_submitted, SUM(CASE WHEN worker_visa.status = 'Pending' THEN 1 ELSE 0 END) AS calling_visa_pending, SUM(CASE WHEN worker_arrival.arrival_status = 'Not Arrived' THEN 1 ELSE 0 END) AS arrival_status_notarrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Arrived' THEN 1 ELSE 0 END) AS arrival_status_arrived, SUM(CASE WHEN worker_arrival.arrival_status = 'Cancelled' THEN 1 ELSE 0 END) AS arrival_status_cancelled, SUM(CASE WHEN workers.plks_status = 'Pending' THEN 1 ELSE 0 END) AS plks_status_pending, SUM(CASE WHEN workers.plks_status = 'Approved' THEN 1 ELSE 0 END) AS plks_status_approved, SUM(CASE WHEN workers.directrecruitment_status = 'Repatriated' THEN 1 ELSE 0 END) AS workers_status_repatriated")
            ->groupBy('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.sector_name', 'levy.ksm_reference_number', 'levy.approved_quota', 'levy.new_ksm_reference_number')
            ->orderBy('crm_prospects.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
}
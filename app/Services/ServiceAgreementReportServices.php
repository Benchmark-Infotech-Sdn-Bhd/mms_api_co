<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CRMProspect;

class ServiceAgreementReportServices
{
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * ServiceAgreementReportServices constructor.
     * 
     * @param CRMProspect $crmProspect
     * 
     * @return void
     * 
     */
    public function __construct(CRMProspect $crmProspect)
    {
        $this->crmProspect = $crmProspect;
    }
    /**
     * List the service agreement
     * 
     * @param $request
     * 
     * @return mixed Returns the paginated list of service agreement.
     */   
    public function list($request): mixed
    {
        return $this->crmProspect->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number')
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if (!empty($search)) {
                $query->where('crm_prospects.company_name', 'like', '%' . $search . '%');
            }
        })
        ->with(['prospectServices' => function ($query) {
            $query->select(['id', 'crm_prospect_id', 'service_id', 'service_name']);
        }])
        ->with(['prospectServices.totalManagemntApplications.applicationAttachment' => function ($query) {
            $query->select([ 'file_id', 'file_url']);
        }])
        ->with(['prospectServices.eContractApplications.applicationAttachment' => function ($query) { 
            $query->select(['file_id', 'file_url']);
        }])
        ->whereHas('prospectServices.totalManagemntApplications.applicationAttachment', function($query){
            $query->whereNotNull('file_url');
        })
        ->orWhereHas('prospectServices.eContractApplications.applicationAttachment', function ($query) {
            $query->whereNotNull('file_url');
        })
        ->orderBy('crm_prospects.id','DESC')
        ->paginate(Config::get('services.paginate_row'));

    }

}
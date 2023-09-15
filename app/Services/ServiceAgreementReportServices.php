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
     * @param CRMProspect $crmProspect
     */
    public function __construct(CRMProspect $crmProspect)
    {
        $this->crmProspect = $crmProspect;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        $totalManagementCrmList = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('total_management_applications', 'total_management_applications.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('total_management_application_attachemnts', function($join) use ($request){
            $join->on('total_management_applications.id', '=', 'total_management_application_attachemnts.file_id');
          })
        ->where('crm_prospects.status', 1)
        ->where('crm_prospect_services.service_id', 3)
        ->whereNotNull('total_management_application_attachemnts.file_url')
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.service_id', 'crm_prospect_services.service_name', 'total_management_applications.id as application_id', 'total_management_application_attachemnts.file_url')
        ->distinct('crm_prospect_services.id', 'total_management_applications.id', 'total_management_application_attachemnts.id');

        $eContractCrmList = $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('e-contract_applications as econtract_application', 'econtract_application.crm_prospect_id', 'crm_prospects.id')
        ->leftJoin('e-contract_application_attachments as econtract_application_attachments', function($join) use ($request){
            $join->on('econtract_application.id', '=', 'econtract_application_attachments.file_id');
          })
        ->where('crm_prospects.status', 1)
        ->where('crm_prospect_services.service_id', 2)
        ->whereNotNull('econtract_application_attachments.file_url')
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospect_services.service_id', 'crm_prospect_services.service_name', 'econtract_application.id as application_id', 'econtract_application_attachments.file_url');
        
        return $eContractCrmList->union($totalManagementCrmList)->paginate(Config::get('services.paginate_row'));
    }

}
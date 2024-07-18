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
        return $this->crmProspect->select('crm_prospects.id','levy.new_ksm_reference_number','levy.ksm_reference_number')
        ->leftjoin('directrecruitment_applications','directrecruitment_applications.company_id','crm_prospects.company_id')
        ->leftJoin('levy', 'levy.application_id', 'directrecruitment_applications.id')
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if (!empty($search)) {
                $query->where('crm_prospects.company_name', 'like', '%' . $search . '%');
            }
        })
        ->with(['prospectServicesExcludingDirectRecruitment' => function ($query) {
            $query->select(['id', 'crm_prospect_id', 'service_id', 'service_name']);
        }])
        // ->with(['prospectServicesExcludingDirectRecruitment.totalManagemntApplications.applicationAttachment' => function ($query) {
        //     $query->select([ 'file_id', 'file_url']);
        // }])
        ->with(['prospectServicesExcludingDirectRecruitment.eContractApplications.applicationAttachment' => function ($query) { 
            $query->select(['file_id', 'file_url']);
        }])
        ->where(function ($query) {
            $query->whereHas('prospectServicesExcludingDirectRecruitment.totalManagemntApplications.applicationAttachment', function($query){
                $query->whereNotNull('file_url');
            })
            ->orWhereHas('prospectServicesExcludingDirectRecruitment.eContractApplications.applicationAttachment', function ($query) {
                $query->whereNotNull('file_url');
            });
        })
        ->orderBy('crm_prospects.id','DESC')
        ->paginate(Config::get('services.paginate_row'));

    }

}
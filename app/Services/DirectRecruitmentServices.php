<?php

namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\Services;
use App\Models\Sectors;
use App\Models\DirectRecruitmentApplicationStatus;
use App\Models\TotalManagementApplications;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class DirectRecruitmentServices
{
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectrecruitmentApplicationAttachments
     */
    private DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentApplicationChecklistServices
     */
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;
    /**
     * @var CRMProspectAttachment
     */
    private CRMProspectAttachment $crmProspectAttachment;
    /**
     * @var Services
     */
    private Services $services;
    /**
     * @var Sectors
     */
    private Sectors $sectors;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * @var DirectRecruitmentApplicationStatus
     */
    private DirectRecruitmentApplicationStatus $directRecruitmentApplicationStatus;
    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;

    /**
     * DirectRecruitmentServices constructor.
     * @param DirectrecruitmentApplications $storage
     * @param DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments
     * @param Storage $storage
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param Services $services
     * @param Sectors $sectors
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices
     * @param ApplicationSummaryServices $applicationSummaryServices;
     * @param DirectRecruitmentApplicationStatus $directRecruitmentApplicationStatus
     * @param TotalManagementApplications $totalManagementApplications
     */
    public function __construct(DirectrecruitmentApplications $directrecruitmentApplications, DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments, 
    Storage $storage, CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors,
    DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices, ApplicationSummaryServices $applicationSummaryServices, DirectRecruitmentApplicationStatus $directRecruitmentApplicationStatus, TotalManagementApplications $totalManagementApplications)
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directrecruitmentApplicationAttachments = $directrecruitmentApplicationAttachments;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->directRecruitmentApplicationStatus = $directRecruitmentApplicationStatus;
        $this->totalManagementApplications = $totalManagementApplications;
    }       
    /**
     * @return array
     */
    public function addServiceValidation(): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required',
            'contact_number' => 'required',
            'email' => 'required',
            'pic_name' => 'required',
            'sector' => 'required',
            'contract_type' => 'required',
            'service_id' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function proposalSubmissionValidation(): array
    {
        return [
            'id' => 'required',
            'quota_applied' => 'required|regex:/^[0-9]+$/|max:3',
            'cost_quoted' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'person_incharge' => 'required',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'

        ];
    }
    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function addService($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->addServiceValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $service = $this->services->findOrFail($request['service_id']);
        $sector = $this->sectors->findOrFail($request['sector']);
        $prospectService = $this->crmProspectService->create([
            'crm_prospect_id'   => $request['id'],
            'service_id'        => $request['service_id'] ?? 1,
            'service_name'      => $service->service_name ?? 'Direct Recruitment',
            'sector_id'         => $request['sector'] ?? 0,
            'sector_name'       => $sector->sector_name,
            'contract_type'     => $service->id == 1 ? $request['contract_type'] : 'No Contract',
            'status'            => $request['status'] ?? 0
        ]);
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/crm/prospect/' . $request['sector_type']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->crmProspectAttachment->create([
                    "file_id" => $request['id'],
                    "prospect_service_id" => $prospectService->id,
                    "file_name" => $fileName,
                    "file_type" => 'prospect',
                    "file_url" =>  $fileUrl          
                ]);  
            }
        }
        $this->directrecruitmentApplications::create([
            'crm_prospect_id' => $request['id'],
            'service_id' => $prospectService->id,
            'quota_applied' => 0,
            'person_incharge' => '',
            'cost_quoted' => 0,
            'status' => Config::get('services.PENDING_PROPOSAL'),
            'remarks' => '',
            'created_by' => $request["created_by"] ?? 0,
            'company_id' => $request['company_id'] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function applicationListing($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->directrecruitmentApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'directrecruitment_applications.service_id')
        ->leftJoin('direct_recruitment_application_status', 'direct_recruitment_application_status.id', 'directrecruitment_applications.status')
        ->where('crm_prospect_services.service_id', 1)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->whereIn('directrecruitment_applications.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('directrecruitment_applications.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->where(function ($query) use ($request) {
            if(isset($request['filter']) && !empty($request['filter'])) {
                $query->where('directrecruitment_applications.status', $request['filter']);
            }
        })
        ->where(function ($query) use ($request) {
            if(isset($request['contract_type']) && !empty($request['contract_type'])) {
                $query->where('crm_prospect_services.contract_type', $request['contract_type']);
            }
        })
        ->select('directrecruitment_applications.id', 'directrecruitment_applications.approval_flag', 'crm_prospects.id as prospect_id', 'crm_prospect_services.id as prospect_service_id','crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospect_services.contract_type as type', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.service_name', 'directrecruitment_applications.quota_applied as applied_quota', 'direct_recruitment_application_status.status_name as status', 'crm_prospect_services.status as service_status', 'directrecruitment_applications.onboarding_flag')
        ->withCount(['fwcms'])
        ->with(['fwcms' => function ($query) {
            $query->leftJoin('application_interviews', 'application_interviews.ksm_reference_number', 'fwcms.ksm_reference_number')
            ->leftJoin('levy', 'levy.ksm_reference_number', 'fwcms.ksm_reference_number')
            ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.ksm_reference_number', 'levy.new_ksm_reference_number')
            ->select('fwcms.application_id')
            ->selectRaw("(CASE WHEN(levy.id IS NULL) THEN fwcms.ksm_reference_number WHEN(levy.id IS NOT NULL) THEN levy.new_ksm_reference_number ELSE fwcms.ksm_reference_number END) as ksm_reference_number, (CASE WHEN(directrecruitment_application_approval.id IS NOT NULL) THEN 'Approval Completed' WHEN(directrecruitment_application_approval.id IS NULL AND levy.id IS NOT NULL) THEN 'Levy Paid' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Scheduled') THEN 'Interview Scheduled' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Completed') THEN 'Interview Completed' WHEN(levy.id IS NULL AND application_interviews.id IS NOT NULL AND application_interviews.status = 'Approved') THEN 'Interview Approved' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Approved') THEN 'FWCMS Approved' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Rejected') THEN 'FWCMS Rejected' WHEN(application_interviews.id IS NULL AND fwcms.id IS NOT NULL AND fwcms.status = 'Query') THEN 'FWCMS Query' ELSE 'FWCMS Submitted' END) as status");
        }])
        ->distinct('directrecruitment_applications.id')
        ->orderBy('directrecruitment_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }        
    /**
     * @param $request
     * @return mixed | boolean
     */
    public function inputValidation($request)
    {
        if(!($this->directrecruitmentApplications->validate($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
        return false;
    }
    /**
     * @param $request
     * @return mixed | boolean
     */
    public function updateValidation($request)
    {
        if(!($this->directrecruitmentApplications->validateUpdation($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
        return false;
    }
     /**
     *
     * @param $request
     * @return mixed
     */
    public function showProposal($request) : mixed
    {
        return $this->directrecruitmentApplications::with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($request['id']);
    }
    /**
     *
     * @param $request
     * @return array
     */
    public function submitProposal($request): array
    {   
        $validator = Validator::make($request->toArray(), $this->proposalSubmissionValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $data = $this->directrecruitmentApplications::findorfail($request['id']);
        $activeServiceCount = $this->crmProspectService->where('crm_prospect_id', $data->crm_prospect_id)
                            ->where('status', 1)
                            ->where('service_id', 1)
                            ->count('id');
        if($activeServiceCount > 0) {
            return [
                'error' => true
            ];
        }
        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 
        if($data->status != Config::get('services.APPROVAL_COMPLETED')){
            $input['status'] = Config::get('services.PROPOSAL_SUBMITTED');
        }
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/proposal/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directrecruitmentApplicationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
        $res = $data->update($input);
        if($res){
            $applicationChecklist = $this->directRecruitmentApplicationChecklistServices->create(
                ['application_id' => $request['id'],
                'item_name' => 'Document Checklist',
                'application_checklist_status' => 'Pending',
                'created_by' => $user['id']]
            );
        }

        $serviceData = $this->crmProspectService->findOrFail($data->service_id);
        $serviceData->status = 1;
        $serviceData->save();

        $input['application_id'] = $request['id'];
        $input['created_by'] = $user['id'];
        $input['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[1];
        $input['status'] = 'Submitted';
        $this->applicationSummaryServices->updateStatus($input);

        return  [
            "isUpdated" => $res,
            "message" => "Updated Successfully"
        ];
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->directrecruitmentApplicationAttachments::find($request['id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        $directrecruitmentApplications = $this->directrecruitmentApplications->find($request['id']);
        if(is_null($directrecruitmentApplications)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        if($directrecruitmentApplications->status != Config::get('services.APPROVAL_COMPLETED')){
            $directrecruitmentApplications->status = $request['status'];
        }
        return [
            "isUpdated" => $directrecruitmentApplications->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @return mixed
     */
    public function dropDownFilter() : mixed
    {
        return $this->directRecruitmentApplicationStatus->where('status', 1)
                    ->select('id', 'status_name')
                    ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function totalManagementListing($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return $this->totalManagementApplications->leftJoin('crm_prospects', 'crm_prospects.id', 'total_management_applications.crm_prospect_id')
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.id', 'total_management_applications.service_id')
        ->leftJoin('total_management_project', 'total_management_project.application_id', 'total_management_applications.id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        })
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where(`e-contract_applications`.`crm_prospect_id`, '=', $request['user']['reference_id']);
            }
        })
        ->where('crm_prospect_services.service_id', 3)
        ->where('crm_prospect_services.from_existing', 1)
        ->where('crm_prospect_services.deleted_at', NULL)
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->selectRaw('total_management_applications.id, crm_prospects.id as prospect_id, crm_prospect_services.id as prospect_service_id, crm_prospects.company_name, crm_prospects.pic_name, crm_prospects.contact_number, crm_prospects.email, crm_prospect_services.sector_id, crm_prospect_services.sector_name, crm_prospect_services.from_existing, total_management_applications.status, count(distinct total_management_project.id) as projects, count(distinct workers.id) as workers')
        ->groupBy('total_management_applications.id', 'crm_prospects.id', 'crm_prospect_services.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospect_services.sector_id', 'crm_prospect_services.sector_name', 'crm_prospect_services.from_existing', 'total_management_applications.status')
        ->orderBy('total_management_applications.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }        
}
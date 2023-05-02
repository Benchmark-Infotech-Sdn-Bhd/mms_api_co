<?php

namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\Services;
use App\Models\Sectors;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
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
     * DirectRecruitmentServices constructor.
     * @param DirectrecruitmentApplications $storage
     * @param DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments
     * @param Storage $storage
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param Services $services
     * @param Sectors $sectors
     */
    public function __construct(DirectrecruitmentApplications $directrecruitmentApplications, DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments, Storage $storage, CRMProspect $crmProspect, CRMProspectService $crmProspectService, CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors)
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directrecruitmentApplicationAttachments = $directrecruitmentApplicationAttachments;
        $this->storage = $storage;
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
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
            'service_id'        => $request['service_id'],
            'service_name'      => $service->service_name,
            'sector_id'         => $request['sector'] ?? 0,
            'sector_name'       => $sector->sector_name,
            'contract_type'     => $service->id == 1 ? $request['contract_type'] : 'No Contract',
            'status'            => $request['status'] ?? 1
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
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function applicationListing($request): mixed
    {
        return $this->directrecruitmentApplications->join('crm_prospects', 'crm_prospects.id', 'directrecruitment_applications.crm_prospect_id')
        ->join('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->where('crm_prospect_services.service_id', 1)
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
        ->select('crm_prospects.company_name', 'crm_prospect_services.contract_type as type', 'directrecruitment_applications.quota_applied as applied_quota', 'directrecruitment_applications.status', 'directrecruitment_applications.id as application_id')
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
        $data = $this->directrecruitmentApplications::findorfail($request['id']);
        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 
        $input['status'] = 'Proposal Submitted'; 
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
        return  [
            "isUpdated" => $data->update($input),
            "message" => "Updated Successfully"
        ];
    }
}
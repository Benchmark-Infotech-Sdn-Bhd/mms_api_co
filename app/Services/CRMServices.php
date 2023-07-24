<?php

namespace App\Services;

use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\LoginCredential;
use App\Models\Sectors;
use App\Models\SystemType;
use App\Models\DirectrecruitmentApplications;
use App\Models\TotalManagementApplications;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CRMServices
{
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
     * @var LoginCredential
     */
    private LoginCredential $loginCredential;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var Sectors
     */
    private Sectors $sectors;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var SystemType
     */
    private SystemType $systemType;
    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;

    /**
     * RolesServices constructor.
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param LoginCredential $loginCredential
     * @param Storage $storage
     * @param Sectors $sectors
     * @param DirectrecruitmentApplications $directrecruitmentApplications;
     * @param SystemType $systemType
     * @param TotalManagementApplications $totalManagementApplications
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, CRMProspectAttachment $crmProspectAttachment, LoginCredential $loginCredential, Storage $storage, Sectors $sectors, DirectrecruitmentApplications $directrecruitmentApplications, SystemType $systemType, TotalManagementApplications $totalManagementApplications)
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->loginCredential = $loginCredential;
        $this->storage = $storage;
        $this->sectors = $sectors;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->systemType = $systemType;
        $this->totalManagementApplications = $totalManagementApplications;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'company_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'roc_number' => 'required|regex:/^[a-zA-Z0-9 ]*$/|unique:crm_prospects,roc_number,NULL,id,deleted_at,NULL',
            'director_or_owner' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'email' => 'required|email|unique:crm_prospects,email,NULL,id,deleted_at,NULL',
            'address' => 'required',
            'pic_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'pic_contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'pic_designation' => 'regex:/^[a-zA-Z ]*$/',
            'registered_by' => 'required',
            'sector_type' => 'required',
            'prospect_service' => 'required',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @param $params
     * @return array
     */
    public function updateValidation($params): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'roc_number' => 'required|regex:/^[a-zA-Z0-9 ]*$/|unique:crm_prospects,roc_number,'.$params['id'].',id,deleted_at,NULL',
            'director_or_owner' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'email' => 'required|unique:crm_prospects,email,'.$params['id'].',id,deleted_at,NULL',
            'address' => 'required',
            'pic_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'pic_contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'pic_designation' => 'regex:/^[a-zA-Z ]*$/',
            'registered_by' => 'required',
            'sector_type' => 'required',
            'attachment.*' => 'mimes:jpeg,pdf,png'
        ];
    }
    /**
     * @return array
     */
    public function crmValidationCustomMessage(): array
    {
        return [
            'attachment.*.max' => 'The attachment size must be within 2MB.',
            'contact_number.integer' => 'The contact number format is invalid.',
            'contact_number.digits_between' => 'The contact number must be within 11 digits.',
            'pic_contact_number.integer' => 'The PIC contact number format is invalid.',
            'pic_contact_number.digits_between' => 'The PIC contact number must be within 11 digits.'
        ];
    }
    /**
     * @param $request
     * @return mixed 
     */
    public function list($request): mixed
    {
        return $this->crmProspect
            ->leftJoin('employee', 'employee.id', 'crm_prospects.registered_by')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
            ->where('crm_prospects.status', 1)
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('crm_prospects.company_name', 'like', '%'.$request['search'].'%')
                    ->orWhere('crm_prospects.pic_name', 'like', '%'.$request['search'].'%')
                    ->orWhere('crm_prospects.director_or_owner', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['filter']) && !empty($request['filter'])) {
                    $query->where('crm_prospect_services.service_id', $request['filter'])
                    ->where('crm_prospect_services.deleted_at', NULL);
                }
            })
            ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.pic_name', 'crm_prospects.director_or_owner', 'crm_prospects.created_at', 'employee.employee_name as registered_by')
            ->with(['prospectServices', 'prospectServices.prospectAttachment', 'prospectLoginCredentials'])->distinct('crm_prospects.id')
            ->orderBy('crm_prospects.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed 
     */
    public function show($request): mixed
    {
        return $this->crmProspect->where('crm_prospects.id', $request['id'])
            ->leftJoin('employee', 'employee.id', 'crm_prospects.registered_by')
            ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospects.director_or_owner', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospects.address', 'crm_prospects.pic_name', 'crm_prospects.pic_contact_number', 'crm_prospects.pic_designation', 'employee.id as registered_by', 'employee.employee_name as registered_by_name')
            ->with(['prospectServices', 'prospectServices.prospectAttachment', 'prospectLoginCredentials'])
            ->get();
    }
    /**
     * @param $request
     * @return bool|array 
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->createValidation(), $this->crmValidationCustomMessage());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $prospect  = $this->crmProspect->create([
            'company_name'          => $request['company_name'] ?? '',
            'roc_number'            => $request['roc_number'] ?? '',
            'director_or_owner'     => $request['director_or_owner'] ?? '',
            'contact_number'        => $request['contact_number'] ?? 0,
            'email'                 => $request['email'] ?? '',
            'address'               => $request['address'] ?? '',
            'status'                => $request['status'] ?? 1,
            'pic_name'              => $request['pic_name'] ?? '',
            'pic_contact_number'    => $request['pic_contact_number'] ?? 0,
            'pic_designation'       => $request['pic_designation'] ?? '',
            'registered_by'         => $request['registered_by'] ?? 0,
            'created_by'            => $request['created_by'] ?? 0,
            'modified_by'           => $request['created_by'] ?? 0
        ]);

        $sector = $this->sectors->findOrFail($request['sector_type']);
        if(isset($request['prospect_service']) && !empty($request['prospect_service'])) {
            $services = json_decode($request['prospect_service']);
            foreach ($services as $service) {
                $prospectService = $this->crmProspectService->create([
                    'crm_prospect_id'   => $prospect->id,
                    'service_id'        => $service->service_id,
                    'service_name'      => $service->service_name,
                    'sector_id'         => $request['sector_type'] ?? 0,
                    'sector_name'       => $sector->sector_name,
                    'contract_type'     => $service->service_id == 1 ? $request['contract_type'] : 'No Contract',
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
                            "file_id" => $prospect->id,
                            "prospect_service_id" => $prospectService->id,
                            "file_name" => $fileName,
                            "file_type" => 'prospect',
                            "file_url" =>  $fileUrl          
                        ]);  
                    }
                }
                if($service->service_id == 1) {
                    $this->directrecruitmentApplications::create([
                       'crm_prospect_id' => $prospect->id,
                       'service_id' => $prospectService->id,
                       'quota_applied' => 0,
                       'person_incharge' => '',
                       'cost_quoted' => 0,
                       'status' => Config::get('services.PENDING_PROPOSAL'),
                       'remarks' => '',
                       'created_by' => $request["created_by"] ?? 0,
                   ]);
                }
                if($service->service_id == 3) {
                    $this->totalManagementApplications::create([
                        'crm_prospect_id' => $prospect->id,
                        'service_id' => $prospectService->id,
                        'quota_applied' => 0,
                        'person_incharge' => $request['pic_name'],
                        'cost_quoted' => 0,
                        'status' => 'Pending Proposal',
                        'remarks' => '',
                        'created_by' => $request["created_by"] ?? 0
                    ]);
                }
            }
        }

        if(isset($request['login_credential']) && !empty($request['login_credential'])) {
            $credentials = json_decode($request['login_credential']);
            foreach ($credentials as $credential) {
                $this->loginCredential->create([
                    'crm_prospect_id'   => $prospect->id,
                    'system_id'         => $credential->system_id ?? 0,
                    'system_name'       => $credential->system_name ?? '',
                    'username'          => $credential->username ?? '',
                    'password'          => $credential->password ?? ''
                ]);
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation($request), $this->crmValidationCustomMessage());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $prospect = $this->crmProspect->findOrFail($request['id']);
        $prospect['company_name'] = $request['company_name'] ?? $prospect['company_name'];
        $prospect['roc_number'] = $request['roc_number'] ?? $prospect['roc_number'];
        $prospect['director_or_owner'] = $request['director_or_owner'] ?? $prospect['director_or_owner'];
        $prospect['contact_number'] = $request['contact_number'] ?? $prospect['contact_number'];
        $prospect['email'] = $request['email'] ?? $prospect['email'];
        $prospect['address'] = $request['address'] ?? $prospect['address'];
        $prospect['status'] = $request['status'] ?? $prospect['status'];
        $prospect['pic_name'] = $request['pic_name'] ?? $prospect['pic_name'];
        $prospect['pic_contact_number'] = $request['pic_contact_number'] ?? $prospect['pic_contact_number'];
        $prospect['pic_designation'] = $request['pic_designation'] ?? $prospect['pic_designation'];
        $prospect['registered_by'] = $request['registered_by'] ?? $prospect['registered_by'];
        $prospect['modified_by'] = $request['modified_by'] ?? $prospect['modified_by'];
        $prospect->save();

        $sector = $this->sectors->findOrFail($request['sector_type']);
        if(isset($request['prospect_service']) && !empty($request['prospect_service'])) {
            if (request()->hasFile('attachment')) {
                $this->crmProspectAttachment->where('file_id', $request['id'])->delete();
            }
            $prospect->prospectServices()->delete();
            $services = json_decode($request['prospect_service']);
            foreach ($services as $service) {
                $prospectService = $this->crmProspectService->create([
                    'crm_prospect_id'   => $prospect->id,
                    'service_id'        => $service->service_id,
                    'service_name'      => $service->service_name,
                    'sector_id'         => $request['sector_type'] ?? 0,
                    'sector_name'       => $sector->sector_name,
                    'contract_type'     => $service->service_id == 1 ? $request['contract_type'] : 'No Contract',
                    'status'            => $request['status'] ?? 0
                ]);
                if (request()->hasFile('attachment')) {
                    foreach($request->file('attachment') as $file) {                
                        $fileName = $file->getClientOriginalName();                 
                        $filePath = '/crm/prospect/' . $fileName; 
                        $linode = $this->storage::disk('linode');
                        $linode->put($filePath, file_get_contents($file));
                        $fileUrl = $this->storage::disk('linode')->url($filePath);
                        $this->crmProspectAttachment->create([
                            "file_id" => $prospect->id,
                            "prospect_service_id" => $prospectService->id,
                            "file_name" => $fileName,
                            "file_type" => 'prospect',
                            "file_url" =>  $fileUrl       
                        ]);  
                    } 
                }
            }
        }

        if(isset($request['login_credential']) && !empty($request['login_credential'])) {
            $prospect->prospectLoginCredentials()->delete();
            $credentials = json_decode($request['login_credential']);
            foreach ($credentials as $credential) {
                $this->loginCredential->create([
                    'crm_prospect_id'   => $request['id'],
                    'system_id'         => $credential->system_id ?? 0,
                    'system_name'       => $credential->system_name ?? '',
                    'username'          => $credential->username ?? '',
                    'password'          => $credential->password ?? ''
                ]);
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return bool
     */
    public function deleteAttachment($request): bool
    {
        return $this->crmProspectAttachment->where('id', $request['id'])->delete();
    }
    /**
     * @return mixed
     */
    public function dropDownCompanies(): mixed
    {
        return $this->crmProspect->where('status', 1)
            ->select('id', 'company_name')
            ->get();
    }
    /**
     * @param $request
     * @return mixed
     */
    public function getProspectDetails($request): mixed
    {
        return $this->crmProspect->where('id', $request['id'])
            ->select('id', 'company_name', 'contact_number', 'email', 'pic_name')
            ->get();
    }
    /**
     * @return mixed
     */
    public function systemList(): mixed
    {
        return $this->systemType->where('status', 1)
            ->select('id', 'system_name')
            ->get();
    }
}
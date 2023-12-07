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
use App\Models\EContractApplications;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\InvoiceServices;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use App\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CrmImport;

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
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;
    /**
     * @var InvoiceServices
     */
    private InvoiceServices $invoiceServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var User
     */
    private User $user;
    /**
     * @var Role
     */
    private Role $role;

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
     * @param EContractApplications $eContractApplications
     * @param InvoiceServices $invoiceServices
     * @param AuthServices $authServices
     * @param User $user
     * @param Role $role
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, CRMProspectAttachment $crmProspectAttachment, LoginCredential $loginCredential, Storage $storage, Sectors $sectors, DirectrecruitmentApplications $directrecruitmentApplications, SystemType $systemType, TotalManagementApplications $totalManagementApplications, EContractApplications $eContractApplications, InvoiceServices $invoiceServices, AuthServices $authServices, User $user, Role $role)
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
        $this->eContractApplications = $eContractApplications;
        $this->invoiceServices = $invoiceServices;
        $this->authServices = $authServices;
        $this->user = $user;
        $this->role = $role;
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
            'bank_account_name'=>'regex:/^[a-zA-Z0-9 ]*$/',
            'bank_account_number'=>'regex:/^[0-9]+$/|min:5|max:17',
            'tax_id'=>'regex:/^[a-zA-Z0-9]+$/|min:12|max:13',
            'account_receivable_tax_type'=>'regex:/^[a-zA-Z0-9 ]*$/',
            'account_payable_tax_type'=>'regex:/^[a-zA-Z0-9 ]*$/',
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
            'bank_account_name'=>'regex:/^[a-zA-Z0-9 ]*$/',
            'bank_account_number'=>'regex:/^[0-9]+$/|min:5|max:17',
            'tax_id'=>'regex:/^[a-zA-Z0-9]+$/|min:12|max:13',
            'account_receivable_tax_type'=>'regex:/^[a-zA-Z0-9 ]*$/',
            'account_payable_tax_type'=>'regex:/^[a-zA-Z0-9 ]*$/',
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
            ->whereIn('crm_prospects.company_id', $request['company_id'])
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
            ->withCount('prospectServices')
            ->with(['prospectServices' => function ($query) {
                $query->select('crm_prospect_id', 'service_name');
            }])->distinct('crm_prospects.id')
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
            ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospects.director_or_owner', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospects.address', 'crm_prospects.pic_name', 'crm_prospects.pic_contact_number', 'crm_prospects.pic_designation', 'employee.id as registered_by', 'employee.employee_name as registered_by_name','crm_prospects.bank_account_number','crm_prospects.bank_account_name','crm_prospects.tax_id','crm_prospects.account_receivable_tax_type','crm_prospects.account_payable_tax_type','crm_prospects.xero_contact_id')
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
            'company_name'                  => $request['company_name'] ?? '',
            'roc_number'                    => $request['roc_number'] ?? '',
            'director_or_owner'             => $request['director_or_owner'] ?? '',
            'contact_number'                => $request['contact_number'] ?? 0,
            'email'                         => $request['email'] ?? '',
            'address'                       => $request['address'] ?? '',
            'status'                        => $request['status'] ?? 1,
            'pic_name'                      => $request['pic_name'] ?? '',
            'pic_contact_number'            => $request['pic_contact_number'] ?? 0,
            'pic_designation'               => $request['pic_designation'] ?? '',
            'registered_by'                 => $request['registered_by'] ?? 0,
            'bank_account_name'             => $request['bank_account_name'] ?? '',
            'bank_account_number'           => $request['bank_account_number'] ?? 0,
            'tax_id'                        => $request['tax_id'] ?? '',
            'account_receivable_tax_type'   => $request['account_receivable_tax_type'] ?? '',
            'account_payable_tax_type'      => $request['account_payable_tax_type'] ?? '',
            'created_by'                    => $request['created_by'] ?? 0,
            'modified_by'                   => $request['created_by'] ?? 0,
            'company_id'                    => $request['company_id'] ?? 0
        ]);

        $role = $this->role->where('role_name', 'Customer')
                ->where('company_id', $request['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id');
        if(is_null($role)) { 
            $role = $this->role->create([
                'role_name'     => 'Customer',
                'system_role'   => $request['system_role'] ?? 0,
                'status'        => $request['status'] ?? 1,
                'parent_id'     => $request['parent_id'] ?? 0,
                'created_by'    => $request['created_by'] ?? 0,
                'modified_by'   => $request['created_by'] ?? 0,
                'company_id'   => $request['company_id'] ?? 0,
                'special_permission' => $request['special_permission'] ?? 0
            ]);
        }

        $res = $this->authServices->create(
            ['name' => $request['pic_name'],
            'email' => $request['email'],
            'role_id' => $role->id,
            'user_id' => $request['created_by'],
            'status' => 1,
            'password' => Str::random(8),
            'reference_id' => $prospect['id'],
            'user_type' => "Customer",
            'subsidiary_companies' => $request['subsidiary_companies'] ?? [],
            'company_id' => $request['company_id']
        ]);

        if(!$res){
            $prospect->delete();
            return [
                "isCreated" => false,
                "message"=> "Employee not created"
            ];
        }

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
                       'company_id' => $request['company_id'] ?? 0
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
                        'created_by' => $request["created_by"] ?? 0,
                        'company_id' => $request['company_id'] ?? 0
                    ]);
                }
                if($service->service_id == 2) {
                    $this->eContractApplications::create([
                        'crm_prospect_id' => $prospect->id,
                        'service_id' => $prospectService->id,
                        'quota_requested' => 0,
                        'person_incharge' => $request['pic_name'],
                        'cost_quoted' => 0,
                        'status' => 'Pending Proposal',
                        'remarks' => '',
                        'created_by' => $request["created_by"] ?? 0,
                        'company_id' => $request['company_id'] ?? 0
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

        if (\DB::getDriverName() !== 'sqlite') {
            $createContactXero = $this->invoiceServices->createContacts($request);
            if(isset($createContactXero->original['Contacts'][0]['ContactID']) && !empty($createContactXero->original['Contacts'][0]['ContactID'])){
                $prospectData = $this->crmProspect->findOrFail($prospect['id']);
                $prospectData->xero_contact_id = $createContactXero->original['Contacts'][0]['ContactID'];
                $prospectData->save();
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
        $prospect['bank_account_name'] = $request['bank_account_name'] ?? $prospect['bank_account_name'];
        $prospect['bank_account_number'] = $request['bank_account_number'] ?? $prospect['bank_account_number'];
        $prospect ['tax_id'] = $request['tax_id'] ?? $prospect['tax_id'];
        $prospect['account_receivable_tax_type'] = $request['account_receivable_tax_type'] ?? $prospect['account_receivable_tax_type'];
        $prospect['account_payable_tax_type'] = $request['account_payable_tax_type'] ?? $prospect['account_receivable_tax_type'];
        $prospect['modified_by'] = $request['modified_by'] ?? $prospect['modified_by'];
        $prospect->save();

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

        if(isset($request['pic_name']) && !empty($request['pic_name'])) {
            $this->user->where('user_type', 'Customer')
                ->where('reference_id', $request['id'])
                ->update([
                    'name' => $request['pic_name']
                ]);
        }        
        $request['ContactID'] = $prospect['xero_contact_id'];
        $createContactXero = $this->invoiceServices->createContacts($request);
        if(isset($createContactXero->original['Contacts'][0]['ContactID']) && !empty($createContactXero->original['Contacts'][0]['ContactID'])){
            $prospect->xero_contact_id = $createContactXero->original['Contacts'][0]['ContactID'];
            $prospect->save();
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
    public function dropDownCompanies($request): mixed
    {
        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where('crm_prospects.status', 1)
        ->where(function ($query) use ($request) {
            if(isset($request['service_id']) && !empty($request['service_id'])) {
                $query->where('crm_prospect_services.service_id', $request['service_id']);
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name')
        ->distinct('crm_prospects.id', 'crm_prospects.company_name')
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

    /**
     * @return mixed
     */
    public function crmImport($request, $file): mixed
    {
        $params = $request->all();
        $params['created_by'] = 1;
        $params['modified_by'] = 1;

        $row = Excel::import(new CrmImport($params, '', $this), $file);
        return true; 

    }
}
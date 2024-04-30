<?php

namespace App\Services;

use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\LoginCredential;
use App\Models\Sectors;
use App\Models\DirectrecruitmentApplications;
use App\Models\SystemType;
use App\Models\TotalManagementApplications;
use App\Models\EContractApplications;
use App\Models\User;
use App\Models\CompanyModulePermission;
use App\Models\RolePermission;
use App\Models\Role;
use App\Models\XeroTaxRates;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\InvoiceServices;
use App\Services\AuthServices;
use App\Imports\CrmImport;
use Maatwebsite\Excel\Facades\Excel;

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
     *  @var XeroTaxRates
     */
    private XeroTaxRates $xeroTaxRates;

    /**
     * @var companyModulePermission
     */
    private CompanyModulePermission $companyModulePermission;

    /**
     * @var RolePermission
     */
    private RolePermission $rolePermission;

    /**
     * CRMServices constructor.
     *
     * @param CRMProspect $crmProspect Instance of the CRMProspect class
     * @param CRMProspectService $crmProspectService Instance of the CRMProspectService class
     * @param CRMProspectAttachment $crmProspectAttachment Instance of the CRMProspectAttachment class
     * @param LoginCredential $loginCredential Instance of the LoginCredential class
     * @param Storage $storage Instance of the Storage class
     * @param Sectors $sectors Instance of the Sectors class
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class
     * @param SystemType $systemType Instance of the SystemType class
     * @param TotalManagementApplications $totalManagementApplications Instance of the TotalManagementApplications class
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class
     * @param InvoiceServices $invoiceServices Instance of the InvoiceServices class
     * @param AuthServices $authServices Instance of the AuthServices class
     * @param User $user Instance of the User class
     * @param Role $role Instance of the Role class
     * @param XeroTaxRates $xeroTaxRates Instance of the XeroTaxRates class
     * @param CompanyModulePermission $companyModulePermission Instance of the CompanyModulePermission class
     * @param RolePermission $rolePermission Instance of the RolePermission class
     *
     * @return void
     *
     */
    public function __construct(
        CRMProspect                     $crmProspect,
        CRMProspectService              $crmProspectService,
        CRMProspectAttachment           $crmProspectAttachment,
        LoginCredential                 $loginCredential,
        Storage                         $storage,
        Sectors                         $sectors,
        DirectrecruitmentApplications   $directrecruitmentApplications,
        SystemType $systemType,
        TotalManagementApplications     $totalManagementApplications,
        EContractApplications           $eContractApplications,
        InvoiceServices                 $invoiceServices,
        AuthServices                    $authServices,
        User                            $user,
        Role                            $role,
        XeroTaxRates                    $xeroTaxRates,
        CompanyModulePermission         $companyModulePermission,
        RolePermission                  $rolePermission
    )
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
        $this->xeroTaxRates = $xeroTaxRates;
        $this->companyModulePermission = $companyModulePermission;
        $this->rolePermission = $rolePermission;
    }

    /**
     * validate the update create request data
     *
     * @return array The validation rules for the input data.
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
     * validate the update update request data
     *
     * @param $params
     * @return array The validation rules for the input data.
     */
    public function updateValidation($params): array
    {
        return [
            'id' => 'required',
            'company_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'roc_number' => 'required|regex:/^[a-zA-Z0-9 ]*$/|unique:crm_prospects,roc_number,'.$params['id'].',id,deleted_at,NULL',
            'director_or_owner' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'email' => 'required|email|unique:crm_prospects,email,'.$params['id'].',id,deleted_at,NULL',
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
     * custom message
     *
     * @return array The validation rules for the input data.
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
     * validate the update search request data
     *
     * @return array The validation rules for the input data.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListRequest($request): array|bool
    {
	    $search = $request['search'] ?? '';
        if(!empty($search)){
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->createValidation(), $this->crmValidationCustomMessage());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->updateValidation($request), $this->crmValidationCustomMessage());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * List the CRM
     *
     * @param $request The request data containing the 'search', 'filter', 'company_id'
     *
     * @return mixed Returns the paginated list of crm.
     */
    public function list($request): mixed
    {
        $validationResult = $this->validateListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        return $this->crmProspect
            ->leftJoin('employee', 'employee.id', 'crm_prospects.registered_by')
            ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
            ->whereIn('crm_prospects.company_id', $request['company_id'])
            ->where('crm_prospects.status', 1)
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query,$request);
            })
            ->where(function ($query) use ($request) {
                $this->applyServiceFilter($query,$request);
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
     * Apply search filter to the query.
     *
     * @param array $request The request data containing the search keyword.
     *
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        $search = $request['search'] ?? '';
        if(!empty($search)) {
            $query->where('crm_prospects.company_name', 'like', '%'.$search.'%')
            ->orWhere('crm_prospects.pic_name', 'like', '%'.$search.'%')
            ->orWhere('crm_prospects.director_or_owner', 'like', '%'.$search.'%');
        }
    }

    /**
     * Apply service filter to the query.
     *
     * @param array $request The request data containing the filter key.
     *
     * @return void
     */
    private function applyServiceFilter($query, $request)
    {
        $filter = $request['filter'] ?? '';
        if(!empty($filter)) {
            $query->where('crm_prospect_services.service_id', $filter)
            ->where('crm_prospect_services.deleted_at', NULL);
        }
    }

    /**
     * show the crm detail
     *
     * @param $request The request data containing the 'company_id', 'id' key
     *
     * @return mixed Returns the crm detail with related attachments
     */
    public function show($request): mixed
    {
        return $this->crmProspect->whereIn('crm_prospects.company_id', $request['company_id'])->where('crm_prospects.id', $request['id'])
            ->leftJoin('employee', 'employee.id', 'crm_prospects.registered_by')
            ->select('crm_prospects.id', 'crm_prospects.company_name', 'crm_prospects.roc_number', 'crm_prospects.director_or_owner', 'crm_prospects.contact_number', 'crm_prospects.email', 'crm_prospects.address', 'crm_prospects.pic_name', 'crm_prospects.pic_contact_number', 'crm_prospects.pic_designation', 'employee.id as registered_by', 'employee.employee_name as registered_by_name','crm_prospects.bank_account_number','crm_prospects.bank_account_name','crm_prospects.tax_id','crm_prospects.account_receivable_tax_type','crm_prospects.account_payable_tax_type','crm_prospects.xero_contact_id')
            ->with(['prospectServices', 'prospectServices.prospectAttachment', 'prospectLoginCredentials'])
            ->get();
    }

    /**
     * create CRM Prospect
     *
     * @param array $request The request data containing the create data
     *
     * @return mixed Returns the created crm prospect data
     */
    private function createCrmProspect($request)
    {
        return $this->crmProspect->create([
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
    }

    /**
     * create Role
     *
     * @param array $request The request data containing the create role data
     *
     * @return mixed Returns the created role data
     */
    private function createRole($request)
    {
        return $this->role->create([
            'role_name'     => 'Customer',
            'system_role'   => $request['system_role'] ?? 0,
            'status'        => $request['status'] ?? 1,
            'parent_id'     => $request['parent_id'] ?? 0,
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0,
            'company_id'   => $request['company_id'] ?? 0,
            'special_permission' => $request['special_permission'] ?? 0,
            'editable' => 0
        ]);
    }

    /**
     * Upload attachment of prospect.
     *
     * @param array $request
     *              attachment (file)
     * @param int $prospectId
     * @param int $prospectServiceId
     *
     * @return void
     */
    private function uploadAttachment($request, $prospectId, $prospectServiceId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/crm/prospect/' . $request['sector_type']. '/'. $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->crmProspectAttachment->create([
                    "file_id" => $prospectId,
                    "prospect_service_id" => $prospectServiceId,
                    "file_name" => $fileName,
                    "file_type" => 'prospect',
                    "file_url" =>  $fileUrl
                ]);
            }
        }
    }

    /**
     * create Directrecruitment Applications
     *
     * @param array $request The request data containing the create application details
     * @param int $prospectId
     * @param int $prospectServiceId
     *
     * @return void
     */
    private function createDirectrecruitmentApplications($request, $prospectId, $prospectServiceId): void
    {
        $this->directrecruitmentApplications::create([
                   'crm_prospect_id' => $prospectId,
                   'service_id' => $prospectServiceId,
                   'quota_applied' => 0,
                   'person_incharge' => '',
                   'cost_quoted' => 0,
                   'status' => Config::get('services.PENDING_PROPOSAL'),
                   'remarks' => '',
                   'created_by' => $request["created_by"] ?? 0,
                   'company_id' => $request['company_id'] ?? 0
               ]);
    }

    /**
     * create TotalManagement Applications
     *
     * @param array $request The request data containing the create application details
     * @param int $prospectId
     * @param int $prospectServiceId
     *
     * @return void
     */
    private function createTotalManagementApplications($request, $prospectId, $prospectServiceId): void
    {
        $this->totalManagementApplications::create([
                'crm_prospect_id' => $prospectId,
                'service_id' => $prospectServiceId,
                'quota_applied' => 0,
                'person_incharge' => $request['pic_name'],
                'cost_quoted' => 0,
                'status' => 'Pending Proposal',
                'remarks' => '',
                'created_by' => $request["created_by"] ?? 0,
                'company_id' => $request['company_id'] ?? 0
        ]);
    }

    /**
     * create Econtract Applications
     *
     * @param array $request The request data containing the create application details
     * @param int $prospectId
     * @param int $prospectServiceId
     *
     * @return void
     */
    private function createEcontractApplications($request, $prospectId, $prospectServiceId): void
    {
        $this->eContractApplications::create([
                    'crm_prospect_id' => $prospectId,
                    'service_id' => $prospectServiceId,
                    'quota_requested' => 0,
                    'person_incharge' => $request['pic_name'],
                    'cost_quoted' => 0,
                    'status' => 'Pending Proposal',
                    'remarks' => '',
                    'created_by' => $request["created_by"] ?? 0,
                    'company_id' => $request['company_id'] ?? 0
        ]);
    }

    /**
     * create Service
     *
     * @param array $request The request data containing the create service data
     *
     * @return void
     */
    private function createService($services, $prospect, $sector, $request)
    {
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

            $prospectId = $prospect->id;
            $prospectServiceId = $prospectService->id;

            $this->uploadAttachment($request, $prospectId, $prospectServiceId);

            if($service->service_id == 1) {
               $this->createDirectrecruitmentApplications($request, $prospectId, $prospectServiceId);
            }
            if($service->service_id == 3) {
                $this->createTotalManagementApplications($request, $prospectId, $prospectServiceId);
            }
            if($service->service_id == 2) {
                $this->createEcontractApplications($request, $prospectId, $prospectServiceId);
            }
        }
    }

    /**
     * Create the CRM
     *
     * @param $request The request data containing the create CRM data
     *
     * @return bool|array Return an array of validation errors or boolean based on the processing result
     */
    public function create($request): bool|array
    {        
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $prospect  = $this->createCrmProspect($request);

        if ($this->hasCustomerLogin($request['company_id'])) {
            $role = $this->checkCustomerRole($request['company_id']);
            if(is_null($role)) {
                $role = $this->createRole($request);
                $this->assignAccess($role);
            }

            $res = $this->authServices->create(
                ['name' => $request['pic_name'],
                'email' => $request['email'],
                'role_id' => $role['id'],
                'user_id' => $request['created_by'],
                'status' => 1,
                'password' => Str::random(8),
                'reference_id' => $prospect['id'],
                'user_type' => "Customer",
                'subsidiary_companies' => $request['subsidiary_companies'] ?? [],
                'company_id' => $request['company_id']
            ]);
        }

        $sector = $this->sectors->findOrFail($request['sector_type']);
        if (isset($request['prospect_service']) && !empty($request['prospect_service'])) {
            $services = json_decode($request['prospect_service']);
            $this->createService($services, $prospect, $sector, $request);
        }

        if (isset($request['login_credential']) && !empty($request['login_credential'])) {
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
            $request['prospect_id'] = $prospect['id'];
            $prospect['account_receivable_tax_type'] = $this->getTaxRateValue($prospect['account_receivable_tax_type']);
            $prospect['account_payable_tax_type'] = $this->getTaxRateValue($prospect['account_payable_tax_type']);            
            $createContactXero = $this->invoiceServices->createContacts($request);
        }
        return true;
    }

    /**
     * get tax name from XeroTaxRates
     *
     * @param integer $taxTypeId 
     *
     * @return mixed $result['name']
     */
    private function getTaxRateValue($taxTypeId)
    {
        if(!empty($taxTypeId)){
            $result = $this->xeroTaxRates->select('name')->find($taxTypeId);
            return $result['name'];
        }        
    }

    /**
     * update the CRM Prospect
     *
     * @param array $request The request data containing the update data
     *
     * @return void
     */
    private function updateCrmProspect($request, $prospect)
    {
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
        $prospect['account_payable_tax_type'] = $request['account_payable_tax_type'] ?? $prospect['account_payable_tax_type'];
        $prospect['modified_by'] = $request['modified_by'] ?? $prospect['modified_by'];
        $prospect->save();
    }

    /**
     * Update the CRM
     *
     * @param $request The request data containing the update CRM data
     *
     * @return bool|array An array of validation errors or boolean based on the processing result
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $prospect = $this->crmProspect::where('company_id', $request['company_id'])->find($request['id']);
        if(is_null($prospect)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $this->updateCrmProspect($request, $prospect);

        if (isset($request['login_credential']) && !empty($request['login_credential'])) {
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

        if (isset($request['pic_name']) && !empty($request['pic_name'])) {
            $this->user->where('user_type', 'Customer')
                ->where('reference_id', $request['id'])
                ->update([
                    'name' => $request['pic_name']
                ]);
        }

        if (\DB::getDriverName() !== 'sqlite') {
            $request['prospect_id'] = $prospect['id'];
            $request['ContactID'] = $prospect['xero_contact_id'];
            $request['account_receivable_tax_type'] = $this->getTaxRateValue($prospect['account_receivable_tax_type']);
            $request['account_payable_tax_type'] = $this->getTaxRateValue($prospect['account_payable_tax_type']);
            $createContactXero = $this->invoiceServices->createContacts($request);
            if (isset($createContactXero->original['Contacts'][0]['ContactID']) && !empty($createContactXero->original['Contacts'][0]['ContactID'])) {
                $prospect->xero_contact_id = $createContactXero->original['Contacts'][0]['ContactID'];
                $prospect->save();
            } else if (isset($createContactXero->original['contact']['contact_id']) && !empty($createContactXero->original['contact']['contact_id'])) {
                $prospectData = $this->crmProspect->findOrFail($prospect['id']);
                $prospectData->xero_contact_id = $createContactXero->original['contact']['contact_id'];
                $prospectData->save();
            }
        }

        return true;
    }

    /**
     * Delete the Attachment
     *
     * @param $request The request data containing the company_id, id key
     *
     * @return bool Returns true if the deletion is successfully, otherwise false.
     */
    public function deleteAttachment($request): bool
    {
        return $this->crmProspectAttachment::join('crm_prospects', function ($join) use ($request) {
            $join->on('crm_prospects.id', '=', 'crm_prospect_attachments.file_id')
                 ->whereIn('crm_prospects.company_id', $request['company_id']);
        })->where('crm_prospect_attachments.id', $request['id'])->delete();
    }

    /**
     * List the CRM dropdown
     *
     * @param $request The request data containing the company_id, service_id key
     *
     * @return mixed Returns the list of CRM
     */
    public function dropDownCompanies($request): mixed
    {
        return $this->crmProspect
        ->leftJoin('crm_prospect_services', 'crm_prospect_services.crm_prospect_id', 'crm_prospects.id')
        ->whereIn('crm_prospects.company_id', $request['company_id'])
        ->where('crm_prospects.status', 1)
        ->where(function ($query) use ($request) {
            $serviceId = $request['service_id'] ?? '';
            if(!empty($serviceId)) {
                $query->where('crm_prospect_services.service_id', $serviceId);
            }
        })
        ->select('crm_prospects.id', 'crm_prospects.company_name')
        ->distinct('crm_prospects.id', 'crm_prospects.company_name')
        ->get();
    }

    /**
     * Get the Prospect Details
     *
     * @param $request The request data containing the id, company_id key
     *
     * @return mixed Returns the Prospect data
     */
    public function getProspectDetails($request): mixed
    {
        return $this->crmProspect->where('id', $request['id'])->whereIn('company_id', $request['company_id'])
            ->select('id', 'company_name', 'contact_number', 'email', 'pic_name')
            ->get();
    }

    /**
     * List the system type
     *
     * @return mixed Returns the list of system type
     */
    public function systemList(): mixed
    {
        return $this->systemType->where('status', 1)
            ->select('id', 'system_name')
            ->get();
    }

    /**
     * Import the CRM data
     *
     * @param $request
     * @param $file
     *
     * @return mixed Returns true if the import is successfully, otherwise false.
     */
    public function crmImport($request, $file): mixed
    {
        $params = $request->all();
        $params['created_by'] = 1;
        $params['modified_by'] = 1;

        $row = Excel::import(new CrmImport($params, $this, ''), $file);
        return true;

    }

    /**
     * Checks if the company has customer login feature.
     *
     * @param integer $companyId .
     * @return bool Returns true if the company has customer login, false otherwise.
     */
    private function hasCustomerLogin($companyId): bool
    {
        $featureCheck = $this->companyModulePermission->where('company_id', $companyId)
        ->where('module_id', Config::get('services.CUSTOMER_LOGIN'))
        ->count();

        return $featureCheck > 0;
    }

    /**
     * Checks if the company has customer role.
     *
     * @param integer $companyId .
     * @return mixed Returns role if the company have the role customer, returns null otherwise.
     */
    private function checkCustomerRole($companyId): mixed
    {
        return $this->role->where('role_name', 'Customer')
        ->where('company_id', $companyId)
        ->whereNull('deleted_at')
        ->where('status',1)
        ->first('id');
    }

    /**
     * Assigning access to the customer role.
     *
     * @param integer $role The role details .
     * @return bool Returns true if the access for customer role is created, otherwise false.
     */
    private function assignAccess($role): bool
    {
        foreach (Config::get('services.SERVICES_MODULES') as $moduleId) {
            $this->rolePermission->create([
                'role_id'       => $role->id,
                'module_id'     => $moduleId,
                'permission_id' => Config::get('services.VIEW_PERMISSION'),
                'created_by'    => $role->created_by ?? 0,
                'modified_by'   => $role->created_by ?? 0
            ]);
        }

        return true;
    }
}


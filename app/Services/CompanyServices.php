<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAttachments;
use App\Models\UserCompany;
use App\Models\User;
use App\Models\FeeRegistration;
use App\Models\CompanyModulePermission;
use App\Models\RolePermission;
use App\Models\XeroSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CompanyServices
{
    /**
     * @var Company
     */
    private Company $company;

    /**
     * @var CompanyAttachments
     */
    private CompanyAttachments $companyAttachments;

    /**
     * @var UserCompany
     */
    private $userCompany;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var feeRegistration
     */
    private FeeRegistration $feeRegistration;

    /**
     * @var companyModulePermission
     */
    private CompanyModulePermission $companyModulePermission;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var RolePermission
     */
    private RolePermission $rolePermission;

    /**
     * @var XeroSettings
     */
    private XeroSettings $xeroSettings;

    /**
     * CompanyServices constructor
     * @param Company $company
     * @param CompanyAttachments $companyAttachments
     * @param UserCompany $userCompany
     * @param User $user
     * @param FeeRegistration $feeRegistration
     * @param CompanyModulePermission $companyModulePermission
     * @param Storage $storage
     * @param RolePermission $rolePermission
     * @param XeroSettings $xeroSettings
     */
    public function __construct(
        Company $company,
        CompanyAttachments $companyAttachments,
        UserCompany $userCompany,
        User $user,
        FeeRegistration $feeRegistration,
        Storage $storage,
        CompanyModulePermission $companyModulePermission,
        RolePermission $rolePermission,
        XeroSettings $xeroSettings
    ) 
    {
        $this->company = $company;
        $this->companyAttachments = $companyAttachments;
        $this->userCompany = $userCompany;
        $this->user = $user;
        $this->feeRegistration = $feeRegistration;
        $this->companyModulePermission = $companyModulePermission;
        $this->storage = $storage;
        $this->rolePermission = $rolePermission;
        $this->xeroSettings = $xeroSettings;
    }

    /**
     * @return array
     */
    public function assignModuleValidation(): array
    {
        return [
            'company_id' => 'required',
            'modules' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function assignFeatureValidation(): array
    {
        return [
            'company_id' => 'required',
            'features' => 'required'
        ];
    }

    public function accountSystemUpdateValidation(): array
    {
        return [
            'company_id' => 'required',
            'title' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function accountSystemDeleteValidation(): array
    {
        return [
            'company_id' => 'required',
            'title' => 'required'
        ];
    }

    /**
     * Creates the validation rules for updating the ZOHO Account of a company.
     *
     * @return array The array containing the validation rules.
     */
    public function zohoAccountUpdateValidation(): array
    {
        return [
            'company_id' => 'required',
            'title' => 'required',
            'url' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'tenant_id' => 'required',
            'access_token' => 'required',
            'refresh_token' => 'required',
            'redirect_url' => 'required'
        ];
    }

    /**
     * Creates the validation rules for updating the XERO Account of a company.
     *
     * @return array The array containing the validation rules.
     */
    public function xeroAccountUpdateValidation(): array
    {
        return [
            'company_id' => 'required',
            'title' => 'required',
            'url' => 'required',
            'client_id' => 'required',
            'client_secret' => 'required',
            'tenant_id' => 'required',
            'access_token' => 'required',
            'refresh_token' => 'required'
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->company
            ->where(function ($query) use ($request) {
                $query->where('company_name', 'like', '%'.$request['search'].'%')
                ->orWhere('register_number', 'like', '%'.$request['search'].'%')
                ->orWhere('pic_name', 'like', '%'.$request['search'].'%');
            })
            ->select('id', 'company_name', 'register_number', 'country', 'state', 'pic_name', 'status', 'parent_id', 'parent_flag')
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->company->with('attachments')->findOrFail($request['id']);
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->company->rules);
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $companyDetails = $this->company->create([
            'company_name' => $request['company_name'] ?? '',
            'register_number' => $request['register_number'] ?? '',
            'country' => $request['country'] ?? '',
            'state' => $request['state'] ?? '',
            'pic_name' => $request['pic_name'] ?? '',
            'role' => $request['role'] ?? 'Admin',
            'status' => $request['status'] ?? 1,
            'parent_id' => $request['parent_id'] ?? 0,
            'system_color' => $request['system_color'] ?? '',
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);

        if (isset($request['parent_id']) && !empty($request['parent_id'])) {
            $this->company->where('id', $request['parent_id'])->update(['parent_flag' => 1]);
        }

        foreach(Config::get('services.STANDARD_FEE_NAMES') as $index => $fee ) {
            $this->feeRegistration::create([
                'item_name' => $fee, 
                'cost' => Config::get('services.STANDARD_FEE_COST')[$index], 
                'fee_type' => 'Standard', 
                'created_by' => $request["created_by"], 
                'company_id' => $companyDetails->id
            ]);
        }

        if (request()->hasFile('attachment') && isset($companyDetails->id)) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/company/logo/' . $companyDetails->id. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->companyAttachments->updateOrCreate(
                    [
                        "file_id" => $companyDetails->id
                    ],
                    [
                    "file_name" => $fileName,
                    "file_type" => 'Logo',
                    "file_url" =>  $fileUrl,
                    'created_by' => $request['created_by'] ?? 0,
                    'modified_by' => $request['created_by'] ?? 0
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
        $validator = Validator::make($request->toArray(), $this->company->updationRules($request['id']));
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $company = $this->company->findOrFail($request['id']);
        $company->company_name = $request['company_name'] ?? $company->company_name;
        $company->register_number = $request['register_number'] ?? $company->register_number;
        $company->country = $request['country'] ?? $company->country;
        $company->state = $request['state'] ?? $company->state;
        $company->pic_name = $request['pic_name'] ?? $company->pic_name;
        $company->status = $request['status'] ?? $company->status;
        $company->system_color = $request['system_color'] ?? $company->system_color;
        $company->modified_by = $request['modified_by'] ?? $company->modified_by;
        $company->save();

        if (request()->hasFile('attachment') && isset($company->id)) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/company/logo/' . $company->id. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->companyAttachments->updateOrCreate(
                    [
                        "file_id" => $company->id
                    ],
                    [
                    "file_name" => $fileName,
                    "file_type" => 'Logo',
                    "file_url" =>  $fileUrl,
                    'modified_by' => $request['modified_by'] ?? 0
                ]);
            }
        }

        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function updateStatus($request): bool
    {
        $company = $this->company->findOrFail($request['id']);
        $company->status = $request['status'] ?? $company->status;
        $company->modified_by = $request['modified_by'] ?? $company->modified_by;
        $company->save();
        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function subsidiaryDropDown($request): mixed
    {
        return $this->company
            ->where('parent_id', 0)
            ->where('id', '!=', $request['current_company_id'])
            ->where('parent_flag', '!=', 1)
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * @param $request
     * @return bool
     */
    public function assignSubsidiary($request): bool
    {
        $this->company->whereIn('id', $request['subsidiary_company'])
            ->update([
                'parent_id' => $request['parent_company_id'],
                'modified_by' => $request['modified_by']
            ]);

        $this->company->where('id', $request['parent_company_id'])->update(['parent_flag' => 1]);

        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function parentDropDown($request): mixed
    {
        return $this->company
            ->where('parent_id', 0)
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function listUserCompany($request): mixed
    {
        return $this->userCompany
            ->with(['company' => function ($query) {
                $query->select(['id', 'company_name']);
            }])
            ->where('user_id', $request['user_id'])
            ->select('company_id')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function updateCompanyId($request): mixed
    {
        $companyDetail = $this->company->find($request['current_company_id']);
        $newCompanyDetail = $this->company->find($request['company_id']);
        if ($companyDetail->parent_flag == 1) {
            $subsidiaryCompanyIds = $this->company->where('parent_id', $request['current_company_id'])
                                ->select('id')
                                ->get()->toArray();
            $subsidiaryCompanyIds = array_column($subsidiaryCompanyIds, 'id');
            if (!in_array($request['company_id'], $subsidiaryCompanyIds)) {
                return [
                    'InvalidUser' => true
                ];
            }
        } else if ($companyDetail->parent_flag == 0 && $newCompanyDetail->parent_flag == 0) {
            if (is_null($newCompanyDetail) || $newCompanyDetail->parent_id != $companyDetail->parent_id) {
                return [
                    'InvalidUser' => true
                ];
            }
        } else if ($companyDetail->parent_flag == 0 && $newCompanyDetail->parent_flag == 1) {
            if (is_null($newCompanyDetail) || $request['company_id'] != $companyDetail->parent_id) {
                return [
                    'InvalidUser' => true
                ];
            }
        }

        $userDetails = $this->user->findOrFail($request['user_id']);
        $userDetails->company_id = $request['company_id'] ?? $userDetails->company_id;
        $userDetails->save();
        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function subsidiaryDropdownBasedOnParent($request): mixed
    {
        return $this->company
            ->where('parent_id', $request['company_id'])
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function dropdown($request): mixed
    {
        return $this->company
            ->where('status', 1)
            ->select('id', 'company_name')
            ->get();
    }

    /**
     *
     * @param $request
     * @return bool
     */    
    public function deleteAttachment($request): bool
    {   
        $data = $this->companyAttachments->find($request['attachment_id']);
        if (is_null($data)) {
            return false;
        }
        $data->delete();

        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function moduleList($request) 
    {
        return $this->companyModulePermission->leftJoin('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->where('modules.feature_flag', 0)
            ->select('modules.id', 'modules.module_name', 'company_module_permission.id as company_module_permission_id')
            ->get();
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function assignModule($request): bool|array
    {
        $validator = Validator::make($request, $this->assignModuleValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $serviceCheck = $this->checkForServiceModules($request['modules']);
        $invoiceCheck = $this->checkForInvoiceModule($request['modules']);
        if ($serviceCheck != $invoiceCheck) {
            return [
                'invoiceError' => true
            ];
        }

        $existingModules = $this->companyModulePermission->where('company_id', $request['company_id'])
            ->select('module_id')
            ->get()
            ->toArray();
        $existingModules = array_column($existingModules, 'module_id');
        $diffModules = array_diff($existingModules,$request['modules']);

        $this->companyModulePermission
            ->join('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->where('modules.feature_flag', 0)
            ->delete();

        foreach ($request['modules'] as $moduleId) {
            $this->companyModulePermission->create([
                'company_id'    => $request['company_id'],
                'module_id'     => $moduleId,
                'created_by'    => $request['created_by'] ?? 0,
                'modified_by'   => $request['created_by'] ?? 0
            ]);   
        }

        $this->rolePermission->join('roles', 'roles.id', 'role_permission.role_id')
            ->where('roles.company_id', $request['company_id'])
            ->whereIn('role_permission.module_id', $diffModules)
            ->delete();

        return true;
    }

    /**
     * returns the features owned by a particular company
     * 
     * @param $request
     * @return mixed
     */
    public function featureList($request): mixed
    {
        return $this->companyModulePermission->leftJoin('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->where('modules.feature_flag', 1)
            ->select('modules.id', 'modules.module_name as feature_name', 'company_module_permission.id as company_feature_permission_id')
            ->get();
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function assignFeature($request): bool|array
    {
        $validator = Validator::make($request, $this->assignFeatureValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $this->companyModulePermission
            ->join('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->where('modules.feature_flag', 1)
            ->delete();

        foreach ($request['features'] as $featureId) {
            $this->companyModulePermission->create([
                'company_id'    => $request['company_id'],
                'module_id'     => $featureId,
                'created_by'    => $request['created_by'] ?? 0,
                'modified_by'   => $request['created_by'] ?? 0
            ]);   
        }

        return true;
    }

    /*
     * list the account system title list.
     *
     * @return array
     */
    public function accountSystemTitleList(): array
    {
        return Config::get('services.COMPANY_ACCOUNT_SYSTEM_TITLE');
    }

    /**
     * show a account system.
     *
     * @param array $request The request data containing account system detail.
     * @return mixed Returns account system data
     */
    public function accountSystemShow($request): mixed
    {
        return $this->xeroSettings
            ->where('company_id', $request['company_id'])
            ->select('id', 'title', 'url', 'client_id', 'client_secret', 'tenant_id', 'access_token', 'refresh_token', 'redirect_url', 'remarks')
            ->get();
    }

    /**
     * updte a account system.
     *
     * @param array $request The request data containing account system details.
     * @return mixed Returns true if the system is updated successfully.
     *              Returns error if validation error is exist
     *              Returns InvalidTitle if Title is not exist
     */
    public function accountSystemUpdate($request): mixed
    {
        $validator = Validator::make($request, $this->accountSystemUpdateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $settingsTitle = Config::get('services.COMPANY_ACCOUNT_SYSTEM_TITLE');
        if (!in_array($request['title'], $settingsTitle)) {
            return [
                'InvalidTitle' => true
            ];
        }

        if ($request['title'] == Config::get('services.COMPANY_ACCOUNT_SYSTEM_TITLE')[0]) {
            $validationResult = $this->validateXEROAccountRequest($request);
        } else if ($request['title'] == Config::get('services.COMPANY_ACCOUNT_SYSTEM_TITLE')[1]) {
            $validationResult = $this->validateZOHOAccountRequest($request);
        }

        if (is_array($validationResult)) {
            return $validationResult;
        }

        $this->xeroSettings->updateOrCreate(
            [
                'company_id' => $request['company_id'],
                'title' => $request['title']
            ],
            [
                'url' => $request['url'] ?? null,
                'client_id' => $request['client_id'] ?? null, 
                'client_secret' => $request['client_secret'] ?? null, 
                'tenant_id' => $request['tenant_id'] ?? null, 
                'access_token' => $request['access_token'] ?? null, 
                'refresh_token' => $request['refresh_token'] ?? null,
                'redirect_url' => $request['redirect_url'] ?? null,
                'remarks' => $request['remarks'] ?? null,
                'created_by'    => $request['created_by'] ?? 0,
                'modified_by'   => $request['created_by'] ?? 0
            ]
        );

        return true;
    }

    /**
     * delete a account system.
     *
     * @param array $request The request data containing account system details.
     * @return bool
     */    
    public function accountSystemDelete($request): bool|array
    {   
        $validator = Validator::make($request, $this->accountSystemDeleteValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return $this->xeroSettings->where('company_id', $request['company_id'])->where('title', $request['title'])->delete();
    }

    /**
     * Checks if the modules array contains any of the service modules
     *
     * @param array $modules array of modules which are assigned to the Company
     *
     * @return boolean Returns true if the modules array contains service modules, otherwise false
     */
    private function checkForServiceModules($modules)
    {
        return !empty(array_intersect($modules, Config::get('services.SERVICES_MODULES')));
    }

    /**
     * Checks if the modules array contains invoice module
     *
     * @param array $modules array of modules which are assigned to the Company
     *
     * @return boolean Returns true if the modules array contains invoice module, otherwise false
     */
    private function checkForInvoiceModule($modules)
    {
        return in_array(Config::get('services.INVOICE_MODULE_ID'), $modules);
    }

    /**
     * Validate the given request data for updating ZOHO Account.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function validateZOHOAccountRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->zohoAccountUpdateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data for updating ZOHO Account.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function validateXEROAccountRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->xeroAccountUpdateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }
}
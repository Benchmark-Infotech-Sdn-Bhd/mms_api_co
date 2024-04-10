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
use App\Models\CompanyRenewalNotification;
use App\Models\RenewalNotification;

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
     * @var CompanyRenewalNotification
     */
    private CompanyRenewalNotification $companyRenewalNotification;

    /**
     * @var RenewalNotification
     */
    private RenewalNotification $renewalNotification;

    /**
     * CompanyServices constructor
     *
     * @param Company $company Instance of the Company class
     * @param CompanyAttachments $companyAttachments Instance of the CompanyAttachments class
     * @param UserCompany $userCompany Instance of the UserCompany class
     * @param User $user Instance of the User class
     * @param FeeRegistration $feeRegistration Instance of the FeeRegistration class
     * @param CompanyModulePermission $companyModulePermission Instance of the CompanyModulePermission class
     * @param Storage $storage Instance of the Storage class
     * @param RolePermission $rolePermission Instance of the RolePermission class
     * @param XeroSettings $xeroSettings Instance of the XeroSettings class
     * @param CompanyRenewalNotification $companyRenewalNotification Instance of the CompanyRenewalNotification class
     * @param RenewalNotification $renewalNotification Instance of the RenewalNotification class
     *
     * @return void
     *
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
        XeroSettings $xeroSettings,
        CompanyRenewalNotification $companyRenewalNotification,
        RenewalNotification $renewalNotification
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
        $this->companyRenewalNotification = $companyRenewalNotification;
        $this->renewalNotification = $renewalNotification;
    }

    /**
     * validate the assign module request data
     *
     * @return array The validation rules for the input data.
     */
    public function assignModuleValidation(): array
    {
        return [
            'company_id' => 'required',
            'modules' => 'required'
        ];
    }

    /**
     * validate the assign feature request data
     *
     * @return array The validation rules for the input data.
     */
    public function assignFeatureValidation(): array
    {
        return [
            'company_id' => 'required',
            'features' => 'required'
        ];
    }

    /**
     * validate the account system update request data
     *
     * @return array The validation rules for the input data.
     */
    public function accountSystemUpdateValidation(): array
    {
        return [
            'company_id' => 'required',
            'title' => 'required'
        ];
    }

    /**
     * validate the account system delete request data
     *
     * @return array The validation rules for the input data.
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
     * Creates the validation rules for Save Email Configuration Details of a company.
     *
     * @return array The array containing the validation rules.
     */
    public function emailConfigurationSaveValidation(): array
    {
        return [
            'company_id' => 'required',
            'notification_type' => 'required',
            'notification_settings' => 'required'
        ];
    }


    /**
     * Validate the given request data.
     *
     * @param $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->company->rules);
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
     * @param $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->company->updationRules($request['id']));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * List the company
     *
     * @param $request The request data containing the 'search' key
     *
     * @return mixed Returns the paginated list of company.
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
     * Show the company detail
     *
     * @param $request The request data containing the 'id' key
     *
     * @return mixed Returns the company data
     */
    public function show($request): mixed
    {
        return $this->company->with('attachments')->findOrFail($request['id']);
    }

    /**
     * create the company
     *
     * @param array $request The request data containing the create company data
     *
     * @return mixed Returns the created company data
     */
    private function createCompany($request)
    {
        return $this->company->create([
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
    }

    /**
     * Upload attachment of dispatch.
     *
     * @param array $request
     *              attachment (file
     *
     * @param int $companyDetailsId
     *
     * @return void
     */
    private function uploadAttachment($request, $companyDetailsId): void
    {
        if (request()->hasFile('attachment') && isset($companyDetailsId)) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/company/logo/' . $companyDetailsId. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->companyAttachments->updateOrCreate(
                    [
                        "file_id" => $companyDetailsId
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
    }

    /**
     * Create the company
     *
     * @param $request The request data containing the create company details
     *
     * @return bool|array Returns An array of validation errors or boolean based on the processing result
     */
    public function create($request): bool|array
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $companyDetails = $this->createCompany($request);

        $parentId = $request['parent_id'] ?? '';
        if(!empty($parentId)) {
            $this->company->where('id', $parentId)->update(['parent_flag' => 1]);
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
        $this->uploadAttachment($request, $companyDetails->id);
        return true;
    }

    /**
     * update the company
     *
     * @param array $request The request data containing the update company data
     *
     * @return void
     */
    private function updateCompany($request, $company)
    {
        $company->company_name = $request['company_name'] ?? $company->company_name;
        $company->register_number = $request['register_number'] ?? $company->register_number;
        $company->country = $request['country'] ?? $company->country;
        $company->state = $request['state'] ?? $company->state;
        $company->pic_name = $request['pic_name'] ?? $company->pic_name;
        $company->status = $request['status'] ?? $company->status;
        $company->system_color = $request['system_color'] ?? $company->system_color;
        $company->modified_by = $request['modified_by'] ?? $company->modified_by;
        $company->save();
    }

    /**
     * Update the company details
     *
     * @param $request The request data containing the company update details
     *
     * @return bool|array Returns An array of validation errors or boolean based on the processing result
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        $company = $this->company->findOrFail($request['id']);
        $this->updateCompany($request, $company);
        $this->uploadAttachment($request, $company->id);
        return true;
    }

    /**
     * Update company status
     *
     * @param $request
     *
     * @return bool Returns true if the update status is successfully, otherwise false.
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
     * List the subsidiary company
     *
     * @param $request The request data containing the current_company_id key
     *
     * @return mixed Returns the subsidiary company
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
     * Assign Subsidiary company
     *
     * @param $request The request data containing the subsidiary_company, parent_company_id, modified_by key
     *
     * @return bool Returns true if the assign subsidiary is successfully, otherwise false.
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
     * List the parent company
     *
     * @param $request
     *
     * @return mixed Returns the parent company list
     */
    public function parentDropDown($request): mixed
    {
        return $this->company
            ->where('parent_id', 0)
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * List user company
     *
     * @param $request The request data containing the user_id key
     *
     * @return mixed Returns the list of user company
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
     * Update user company id based on the request data
     *
     * @param $request The request data containing the update company id details
     *
     * @return mixed Return an array of validation errors or boolean based on the processing result
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
     * list subsidiary company based on given request data
     *
     * @param $request The request data containing the company_id key
     *
     * @return mixed Returns the subsidiary company list
     */
    public function subsidiaryDropdownBasedOnParent($request): mixed
    {
        return $this->company
            ->where('parent_id', $request['company_id'])
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * List the active company
     *
     * @param $request
     *
     * @return mixed Returns the active company list
     */
    public function dropdown($request): mixed
    {
        return $this->company
            ->where('status', 1)
            ->select('id', 'company_name')
            ->get();
    }

    /**
     * Delete attachment
     *
     * @param $request The request data containing the attachment_id
     *
     * @return bool Returns true if the deletion is successfully, otherwise false.
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
     * List the company module
     *
     * @param $request The request data containing the company_id key
     *
     * @return mixed Returns the company module list
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
     * Assign Module
     *
     * @param $request The request data containing the assign module details
     *
     * @return bool|array Return an array of validation errors or boolean based on the processing result
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
     * Assign Feature
     *
     * @param $request The request data containing the assign feature details
     *
     * @return bool|array Return an array of validation errors or boolean based on the processing result
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
     * update a account system.
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

    /*
     * list the email configuration notification list.
     *
     * 
     * @param array $request The request data containing the company_id key
     *
     * @return mixed Returns the company notification list
     */
    public function emailConfigurationNotificationList($request)
    {
        return $this->renewalNotification->select('id','notification_name','status')->get();
    }

    /**
     * Show the Email Configuration Details.
     *
     * @param array $request The request data containing the company_id key.
     * @return mixed Returns email configuration data
     */
    public function emailConfigurationShow($request): mixed
    {
        return $this->companyRenewalNotification->where('company_id', $request['company_id'])->get();
    }

    /**
     * Save the Email Configuration Detail.
     *
     * @param array $request The request data containing email Configuration details.
     *                       The array should have the following keys:
     *                      - company_id: ID of the company.
     *                      - notification_type: type of the notification.
     *                      - notification_settings: it containing the notification settings value
     *                      - created_by: Created user ID
     * 
     * @return mixed Returns true if the details is saved successfully.
     *              Returns error if validation error is exist
     *              Returns InvalidNotificationType if notification type is not exist
     */
    public function emailConfigurationSave($request): mixed
    {
        $validator = Validator::make($request, $this->emailConfigurationSaveValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if (!in_array($request['notification_type'], Config::get('services.COMPANY_NOTIFICATION_TYPE'))) {
            return [
                'InvalidNotificationType' => true
            ];
        }

        if($request['notification_type'] == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]){
            $this->updateRenewalNotificationSettings($request);
        } else if($request['notification_type'] == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]){
            $this->updateExpiredNotificationSettings($request);
        }
        return true;
    }
    /**
     * Update the Renewal Notification Settings
     *
     * @param array $request The request data containing email Configuration Renewal details.
     *                       The array should have the following keys:
     *                      - company_id: ID of the company.
     *                      - notification_type: type of the notification.
     *                      - notification_settings: it containing the notification settings value
     *                      - created_by: Created user ID
     *
     * @return void
     */
    private function updateRenewalNotificationSettings($request){
        $notificationSettings = json_decode($request['notification_settings']);
        foreach ($notificationSettings as $settings) {
            $this->companyRenewalNotification->updateOrCreate(
                [
                    'company_id' => $request['company_id'],
                    'notification_id' => $settings->notification_id
                ],
                [
                    'renewal_notification_status' => $settings->renewal_notification_status,
                    'renewal_duration_in_days' => $settings->renewal_duration_in_days,
                    'renewal_frequency_cycle' => $settings->renewal_frequency_cycle,
                    'created_by'    => $request['created_by'] ?? 0,
                    'modified_by'   => $request['created_by'] ?? 0
                ]
            );
        }
    }
    /**
     * Update the Expired Notification Settings
     *
     * @param array $request The request data containing email Configuration Expired details.
     *                       The array should have the following keys:
     *                      - company_id: ID of the company.
     *                      - notification_type: type of the notification.
     *                      - notification_settings: it containing the notification settings value
     *                      - created_by: Created user ID
     *
     * @return void
     */
    private function updateExpiredNotificationSettings($request){
        $notificationSettings = json_decode($request['notification_settings']);
        foreach ($notificationSettings as $settings) {
            $this->companyRenewalNotification->updateOrCreate(
                [
                    'company_id' => $request['company_id'],
                    'notification_id' => $settings->notification_id
                ],
                [
                    'expired_notification_status' => $settings->expired_notification_status,
                    'expired_duration_in_days' => $settings->expired_duration_in_days,
                    'expired_frequency_cycle' => $settings->expired_frequency_cycle,
                    'created_by'    => $request['created_by'] ?? 0,
                    'modified_by'   => $request['created_by'] ?? 0
                ]
            );
        }
    }
}

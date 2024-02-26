<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Config;

class ModulesServices
{
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const USER_TYPE_SUPER_ADMIN = 'Super Admin';

    /**
     * @var Module
     */
    private Module $module;

    /**
     * Constructor method.
     * 
     * @param Module $module Instance of the Module class.
     * 
     * @return void
     */
    public function __construct(
        Module     $module
    )
    {
        $this->module = $module;
    }

    /**
     * Returns a list of super admin module | company module permission based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array list of super admin module | company module permission based on the given search request.
     */
    public function dropDown($request): mixed
    {
        if (isset($request['user_type']) && $request['user_type'] == self::USER_TYPE_SUPER_ADMIN) {
            return $this->showSuperAdminModule();
        } else {
            return $this->showCompanyModulePermission($request);
        }
    }

    /**
     * Returns a list of module based on the given search request.
     * 
     * @param array $request The array containing user type.
     * @return mixed Returns an array is "empty" user type has not allowed. Otherwise, returns a list of module.
     */
    public function featureDropDown($request): mixed
    {
        if($this->isSuperAdminUser($request)) {
            return $this->module->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
                ->whereNotIn('id', Config::get('services.SUPER_ADMIN_MODULES'))
                ->where('feature_flag', self::DEFAULT_INTEGER_VALUE_ONE)
                ->select('id', 'module_name')
                ->get();
        } 
        return [];
    }

    /**
     * Checks if the user is a super admin.
     *
     * @param array $user The user data.
     * @return bool Returns true if the user is a super admin, false otherwise.
     */
    private function isSuperAdminUser($user): bool
    {
        return $user['user_type'] == self::USER_TYPE_SUPER_ADMIN;
    }
    
    /**
     * Show the super admin module.
     * 
     * @return mixed Returns the module.
     */
    private function showSuperAdminModule()
    {
        return $this->module->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereNotIn('id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->where('feature_flag', self::DEFAULT_INTEGER_VALUE_ZERO)
            ->select('id', 'module_name')
            ->get();
    }
    
    /**
     * Show the company module permission.
     * 
     * @param array $request The request data containing company id
     * @return mixed Returns the company module permission.
     */
    private function showCompanyModulePermission($request)
    {
        return $this->module::join('company_module_permission', function ($join) use ($request) {
                $join->on('company_module_permission.module_id', '=', 'modules.id')->where('company_module_permission.company_id', $request['company_id'])->whereNull('company_module_permission.deleted_at');
            })
            ->where('modules.status', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereNotIn('modules.id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->where('modules.feature_flag', self::DEFAULT_INTEGER_VALUE_ZERO)
            ->select('modules.id', 'modules.module_name')
            ->get();
    }
}
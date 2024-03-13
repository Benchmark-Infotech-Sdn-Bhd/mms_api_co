<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Config;

class ModulesServices
{
    /**
     * @var Module
     */
    private Module $module;

    /**
     * ModulesServices constructor.
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @return mixed
     */
    public function dropDown($request): mixed
    {
        if(isset($request['user_type']) && $request['user_type'] == 'Super Admin'){
            return $this->module->where('status', 1)
            ->whereNotIn('id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->where('feature_flag', 0)
            ->select('id', 'module_name')
            ->orderBy('id','ASC')
            ->get();
        }else{
            return $this->module::join('company_module_permission', function ($join) use ($request) {
                $join->on('company_module_permission.module_id', '=', 'modules.id')
                     ->where('company_module_permission.company_id', $request['company_id'])
                     ->whereNull('company_module_permission.deleted_at');
            })->where('modules.status', 1)
            ->whereNotIn('modules.id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->where('modules.feature_flag', 0)
            ->select('modules.id', 'modules.module_name')
            ->orderBy('modules.id','ASC')
            ->get();
        }
    }
    /**
     * @return mixed
     */
    public function featureDropDown($request): mixed
    {
        if($this->isSuperAdminUser($request)) {
            return $this->module->where('status', 1)
            ->whereNotIn('id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->where('feature_flag', 1)
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
        return $user['user_type'] == 'Super Admin';
    }
}
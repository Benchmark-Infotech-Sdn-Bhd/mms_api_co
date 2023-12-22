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
            ->select('id', 'module_name')
            ->get();
        }else{
            return $this->module::join('company_module_permission', function ($join) use ($request) {
                $join->on('company_module_permission.module_id', '=', 'modules.id')
                     ->where('company_module_permission.company_id', $request['company_id'])
                     ->whereNull('company_module_permission.deleted_at');
            })->where('modules.status', 1)
            ->whereNotIn('modules.id', Config::get('services.SUPER_ADMIN_MODULES'))
            ->select('modules.id', 'modules.module_name')
            ->get();
        }
    }
}
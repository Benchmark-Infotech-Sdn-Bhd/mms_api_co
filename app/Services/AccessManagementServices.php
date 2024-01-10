<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\Role;
use App\Models\CompanyModulePermission;
use Illuminate\Support\Facades\Config;

class AccessManagementServices
{
    /**
     * @var RolePermission
     */
    private RolePermission $rolePermission;
    /**
     * @var companyModulePermission
     */
    private CompanyModulePermission $companyModulePermission;
    /**
     * @var Role
     */
    private Role $role;

    /**
     * AccessManagementServices constructor.
     * @param RolePermission $rolePermission
     * @param CompanyModulePermission $companyModulePermission
     * @param Role $role
     */
    public function __construct(RolePermission $rolePermission, CompanyModulePermission $companyModulePermission, Role $role)
    {
        $this->rolePermission   = $rolePermission;
        $this->companyModulePermission = $companyModulePermission;
        $this->role = $role;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required|json'
        ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required|json'
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request) 
    {
        if($request['user_type'] == Config::get('services.ROLE_TYPE_ADMIN') && !is_int($request['role_id'])){
            return $this->companyModulePermission->leftJoin('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->select('modules.id', 'modules.module_name', 'company_module_permission.id as role_permission_id')
            ->get();
        } else {
            return $this->rolePermission
            ->join('roles', 'roles.id', 'role_permission.role_id')
            ->join('modules', 'modules.id', 'role_permission.module_id')
            ->leftJoin('permissions', function ($join) {
                $join->on('permissions.id', '=', 'role_permission.permission_id');
            })
            ->where('roles.company_id', $request['company_id'])
            ->where('role_permission.role_id', $request['role_id'])
            ->select('modules.id', 'modules.module_name')
            ->selectRaw('GROUP_CONCAT(role_permission.permission_id) as permission_ids')
            ->selectRaw('GROUP_CONCAT(permissions.permission_name) as permission_name')
            ->groupBy('modules.id', 'modules.module_name')
            ->get();
        }
    }


    /**
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {
        if(isset($request['modules']) && !empty($request['modules'])) {
            $modules = json_decode($request['modules']);
            $moduleIds = array_unique(array_column($modules, 'module_id'));

            $roleCheck =  $this->rolePermission->join('roles', 'roles.id', 'role_permission.role_id')
                    ->where('roles.company_id', $request['company_id'])
                    ->where('role_permission.role_id', $request['role_id'])
                    ->count();
            if($roleCheck > 0) {
                return [
                    'roleError' => true
                ];
            }

            $companyModules = $this->companyModulePermission->where('company_id', $request['company_id'])
                                ->select('module_id')
                                ->get()
                                ->toArray();
            $companyModules = array_column($companyModules, 'module_id');
            $diffModules = array_diff($moduleIds, $companyModules);
            if(count($diffModules) > 0) {
                return [
                    'moduleError' => true
                ];
            }

            foreach ($modules as $module) {
                $this->rolePermission->create([
                    'role_id'       => $request['role_id'],
                    'module_id'     => $module->module_id,
                    'permission_id' => $module->permission_id ?? 1,
                    'created_by'    => $request['created_by'] ?? 0,
                    'modified_by'   => $request['created_by'] ?? 0
                ]);
            }
        }
        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {
        $modules = json_decode($request['modules']);
        $moduleIds = array_unique(array_column($modules, 'module_id'));

        $companyModules = $this->companyModulePermission->where('company_id', $request['company_id'])
                            ->select('module_id')
                            ->get()
                            ->toArray();
        $companyModules = array_column($companyModules, 'module_id');
        $diffModules = array_diff($moduleIds, $companyModules);
        if(count($diffModules) > 0) {
            return [
                'moduleError' => true
            ];
        }
        $role = $this->role::where('company_id', $request['company_id'])->find($request['role_id']);
        if(is_null($role)){
            return [
                'InvalidUser' => true
            ];
        }
        $this->rolePermission->where('role_id', $request['role_id'])->delete();
        return $this->create($request);
    }
}
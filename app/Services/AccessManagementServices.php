<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RolePermission;

class AccessManagementServices
{
    /**
     * @var `RolePermission`
     */
    private RolePermission $rolePermission;
    /**
     * @var Role
     */
    private Role $role;

    /**
     * AccessManagementServices constructor.
     * @param RolePermission $rolePermission
     * @param Role $role
     */
    public function __construct(RolePermission $rolePermission, Role $role)
    {
        $this->rolePermission   = $rolePermission;
        $this->role   = $role;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required'
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request) 
    {
        // return $this->rolePermission->leftJoin('modules', 'modules.id', 'role_permission.module_id')
        //     ->where('role_permission.role_id', $request['role_id'])
        //     ->select('modules.id', 'modules.module_name', 'role_permission.id as role_permission_id')
        //     ->get();
        // return $this->rolePermission
        //     ->where('role_permission.role_id', $request['role_id'])
        //     ->with(['modules' => function($query) {
        //         $query->select('id', 'module_name');
        //     }, 'permissions' => function($query) {
        //         $query->select('id', 'permission_name');
        //     }])
        //     ->select('id as role_permission_id', 'role_id', 'module_id', 'permission_id')
        //     ->get();
            return $this->role
            ->whereIn('id', $request['role_id'])
            ->with(['rolePermissions' => function($query) {
                $query->select('id', 'role_id', 'module_id', 'permission_id');
            }, 'rolePermissions.modules' => function($query) {
                $query->select('id', 'module_name');
            }, 'rolePermissions.permissions' => function($query) {
                $query->select('id', 'permission_name');
            }])
            ->select('id')
            ->get();
    }


    /**
     * @param $request
     * @return bool
     */
    public function create($request): bool
    {
        $modules = json_decode($request['modules']);
        foreach ($modules as $module) {
            foreach ($module->permission as $key => $permission) {
                $this->rolePermission->create([
                    'role_id'       => $request['role_id'],
                    'module_id'     => $module->module_id,
                    'permission_id' => $permission ?? 1,
                    'created_by'    => $request['created_by'] ?? 0,
                    'modified_by'   => $request['created_by'] ?? 0
                ]);   
            }
        }

        // print_r($modules);exit;
        // foreach ($request['modules'] as $moduleId) {
        //     $this->rolePermission->create([
        //         'role_id'       => $request['role_id'],
        //         'module_id'     => $moduleId,
        //         'permission_id' => $module->permission_id ?? 1,
        //         'created_by'    => $request['created_by'] ?? 0,
        //         'modified_by'   => $request['created_by'] ?? 0
        //     ]);   
        // }
        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function update($request): bool
    {
        $this->rolePermission->where('role_id', $request['role_id'])->delete();
        return $this->create($request);
    }
}
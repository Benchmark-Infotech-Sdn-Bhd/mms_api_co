<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\Module;
use Illuminate\Support\Facades\Config;

class AccessManagementServices
{
    /**
     * @var `RolePermission`
     */
    private RolePermission $rolePermission;

    /**
     * @var `Module`
     */
    private Module $module;

    /**
     * AccessManagementServices constructor.
     * @param RolePermission $rolePermission
     * @param Module $module
     */
    public function __construct(RolePermission $rolePermission, Module $module)
    {
        $this->rolePermission   = $rolePermission;
        $this->module           = $module;
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
        return $this->rolePermission->leftJoin('modules', 'modules.id', 'role_permission.module_id')
            ->where('role_permission.role_id', $request['role_id'])
            ->select('modules.id', 'modules.module_name', 'role_permission.id as role_permission_id')
            ->get();
    }


    /**
     * @param $request
     * @return bool
     */
    public function create($request): bool
    {
        foreach ($request['modules'] as $moduleId) {
            $this->rolePermission->create([
                'role_id'       => $request['role_id'],
                'module_id'     => $moduleId,
                'permission_id' => $module->permission_id ?? 1,
                'created_by'    => $request['created_by'] ?? 0,
                'modified_by'   => $request['created_by'] ?? 0
            ]);   
        }
        return true;
    }

    /**
     * @param $request
     * @return bool
     */
    public function update($request): bool
    {
        $role = $this->role->findOrFail($request['id']);
        $role->role_name    = $request['name'] ?? $role->role_name;
        $role->system_role  = $request['system_role'] ?? $role->system_role;
        $role->status       = $request['status'] ?? $role->status;
        $role->parent_id    = $request['parent_id'] ?? $role->parent_id;
        $role->modified_by  = $request['modified_by'] ?? $role->modified_by;
        $role->save();
        return true;
    }
}
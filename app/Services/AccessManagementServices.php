<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\CompanyModulePermission;
use Illuminate\Support\Facades\Config;

class AccessManagementServices
{
    /**
     * @var `RolePermission`
     */
    private RolePermission $rolePermission;
    /**
     * @var companyModulePermission
     */
    private CompanyModulePermission $companyModulePermission;

    /**
     * AccessManagementServices constructor.
     * @param RolePermission $rolePermission
     * @param CompanyModulePermission $companyModulePermission
     */
    public function __construct(RolePermission $rolePermission, CompanyModulePermission $companyModulePermission)
    {
        $this->rolePermission   = $rolePermission;
        $this->companyModulePermission = $companyModulePermission;
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
        if($request['user_type'] == Config::get('services.ROLE_TYPE_ADMIN') && is_null($request['role_id'])){
            return $this->companyModulePermission->leftJoin('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $request['company_id'])
            ->select('modules.id', 'modules.module_name', 'company_module_permission.id as role_permission_id')
            ->get();
        } else {
            return $this->rolePermission->leftJoin('modules', 'modules.id', 'role_permission.module_id')
            ->where('role_permission.role_id', $request['role_id'])
            ->select('modules.id', 'modules.module_name', 'role_permission.id as role_permission_id')
            ->get();
        }        
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
        $this->rolePermission->where('role_id', $request['role_id'])->delete();
        return $this->create($request);
    }
}
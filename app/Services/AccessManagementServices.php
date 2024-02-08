<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\Role;
use App\Models\CompanyModulePermission;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;

class AccessManagementServices
{
    const ERROR_ROLE_EXISTS = ['roleError' => true];
    const ERROR_MODULE_EXISTS = ['moduleError' => true];

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
     * Class constructor.
     *
     * @param RolePermission $rolePermission The RolePermission instance.
     * @param CompanyModulePermission $companyModulePermission The CompanyModulePermission instance.
     * @param Role $role The Role instance.
     */
    public function __construct(RolePermission $rolePermission, CompanyModulePermission $companyModulePermission, Role $role)
    {
        $this->rolePermission = $rolePermission;
        $this->companyModulePermission = $companyModulePermission;
        $this->role = $role;
    }

    /**
     * Creates the validation rules for creating a new entity.
     *
     * @return array The array containing the validation rules.
     */
    public function createValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required|json'
        ];
    }

    /**
     * Returns the validation rules for the update action.
     *
     * @return array The validation rules for the update action.
     *
     * The returned array has the following structure:
     * [
     *     'role_id' => 'required',
     *     'modules' => 'required'
     * ]
     *
     * The 'role_id' field is required, meaning it must be present in the request data.
     * The 'modules' field is also required, meaning it must be present in the request data.
     */
    public function updateValidation(): array
    {
        return [
            'role_id' => 'required',
            'modules' => 'required|json'
        ];
    }

    /**
     * Lists permissions based on the user type.
     *
     * @param array $request The request data.
     *   - user_type (int) The type of the user.
     *   - company_id (int) The ID of the company.
     *   - role_id (int) The ID of the role.
     *
     * @return array The final result containing the permissions.
     */
    public function list($request)
    {
        if ($request['user_type'] == Config::get('services.ROLE_TYPE_ADMIN') && !is_int($request['role_id'])) {
            $result = $this->getCompanyPermission($request['company_id']);
        } else {
            $result = $this->getUserPermission($request['company_id'], $request['role_id']);
        }

        //return $this->getFinalResult($result);
        return $result;
    }

    /**
     * Get the company permission.
     *
     * @param int $companyId The ID of the company.
     * @return Builder The company permission query builder.
     */
    private function getCompanyPermission($companyId)
    {
        return $this->companyModulePermission->leftJoin('modules', 'modules.id', 'company_module_permission.module_id')
            ->where('company_module_permission.company_id', $companyId)
            ->select('modules.id', 'modules.module_name', 'company_module_permission.id as role_permission_id')
            ->get();
    }

    /**
     * Get the user's permissions.
     *
     * @param int $companyId The ID of the company.
     * @param int $roleId The ID of the role.
     * @return Builder The Builder instance.
     */
    private function getUserPermission($companyId, $roleId)
    {
        return $this->rolePermission
            ->join('roles', 'roles.id', 'role_permission.role_id')
            ->join('modules', 'modules.id', 'role_permission.module_id')
            ->leftJoin('permissions', function ($join) {
                $join->on('permissions.id', '=', 'role_permission.permission_id');
            })
            ->where('roles.company_id', $companyId)
            ->where('role_permission.role_id', $roleId)
            ->select('modules.id', 'modules.module_name')
            ->selectRaw('GROUP_CONCAT(role_permission.permission_id) as permission_ids')
            ->selectRaw('GROUP_CONCAT(permissions.permission_name) as permission_name')
            ->groupBy('modules.id', 'modules.module_name')
            ->get();
    }

    /**
     * Get the final result.
     *
     * @param $result - The query result.
     *
     * @return mixed The final result after applying the select statement to the query result.
     */
    private function getFinalResult($result)
    {
        return $result->select('modules.id', 'modules.module_name', 'role_permission.id as role_permission_id')
            ->get();
    }

    /**
     * Create a new role.
     *
     * @param array $request The request data containing role details.
     * @return mixed Returns true if the role is created successfully.
     *              Returns self::ERROR_ROLE_EXISTS if the role already exists.
     *              Returns self::ERROR_MODULE_EXISTS if there are different modules in the request than in the company.
     */
    public function create($request): mixed
    {
        $modules = json_decode($request['modules']);
        $moduleIds = array_unique(array_column($modules, 'module_id'));

        if ($this->doesRoleExist($request)) {
            return self::ERROR_ROLE_EXISTS;
        }

        $companyModuleIds = $this->getCompanyModuleIds($request['company_id']);

        if ($this->areThereDiffModules($moduleIds, $companyModuleIds)) {
            return self::ERROR_MODULE_EXISTS;
        }

        $this->createRolePermissions($request,$modules);

        return true;
    }

    /**
     * Check if a role exists.
     *
     * @param mixed $request The request data containing the company ID and role ID.
     * @return bool Returns true if the role exists, false otherwise.
     */
    private function doesRoleExist($request): bool
    {
        $roleCount = $this->rolePermission->join('roles', 'roles.id', 'role_permission.role_id')
            ->where('roles.company_id', $request['company_id'])
            ->where('role_permission.role_id', $request['role_id'])
            ->count();

        return $roleCount > 0;
    }

    /**
     * Get the module IDs associated with a company.
     *
     * @param int $companyId The ID of the company.
     * @return array The array containing the module IDs.
     */
    private function getCompanyModuleIds($companyId): array
    {
        $companyModules = $this->companyModulePermission->where('company_id', $companyId)
            ->select('module_id')
            ->get()
            ->toArray();

        return array_column($companyModules, 'module_id');
    }

    /**
     * Check if there are any different modules between the request modules and the company module IDs.
     *
     * @param array $requestModules The array of request modules.
     * @param array $companyModuleIds The array of company module IDs.
     *
     * @return bool Returns true if there are different modules, false otherwise.
     */
    private function areThereDiffModules($requestModules, $companyModuleIds): bool
    {
        $diffModules = array_diff($requestModules, $companyModuleIds);
        return count($diffModules) > 0;
    }

    /**
     * Create role permissions.
     *
     * @param array $request The request data containing modules, role_id, permission_id, created_by, and modified_by.
     * @return void
     */
    private function createRolePermissions($request,$modules): void
    {
        foreach ($modules as $module) {
            $this->rolePermission->create([
                'role_id' => $request['role_id'],
                'module_id' => $module->module_id,
                'permission_id' => $module->permission_id ?? 1,
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0
            ]);
        }
    }

    /**
     * Updates the role permission and creates new ones if needed.
     *
     * @param mixed $request The request data.
     * @return mixed The result of the update operation. If there is any error, it returns an associative array with an error flag.
     */
    public function update($request): mixed
    {
        $modules = json_decode($request['modules']);
        $moduleIds = array_unique(array_column($modules, 'module_id'));

        $currentCompanyModules = $this->getCurrentCompanyModules($request);

        $diffModules = $this->compareModules($moduleIds, $currentCompanyModules);
        if (count($diffModules) > 0) {
            return ['moduleError' => true];
        }

        if (!$this->isValidRole($request)) {
            return ['InvalidUser' => true];
        }

        $this->rolePermission->where('role_id', $request['role_id'])->delete();

        return $this->create($request);
    }

    /**
     * Get the current company modules.
     *
     * @param mixed $request The request data containing the company_id.
     * @return array The array of module ids.
     */
    private function getCurrentCompanyModules($request): array
    {
        $currentCompanyModules = $this->companyModulePermission
            ->where('company_id', $request['company_id'])
            ->select('module_id')
            ->get()
            ->toArray();

        return array_column($currentCompanyModules, 'module_id');
    }

    /**
     * Compares two arrays of modules and returns the modules that are in $requestModules but not in $currentCompanyModules.
     *
     * @param array $requestModules The array of requested modules.
     * @param array $currentCompanyModules The array of modules that belong to the current company.
     * @return array An array containing the modules that are in $requestModules but not in $currentCompanyModules.
     */
    private function compareModules($requestModules, $currentCompanyModules): array
    {
        return array_diff($requestModules, $currentCompanyModules);
    }

    /**
     * Checks if a given role is valid.
     *
     * @param array $request An array containing the request data.
     *                      - company_id: The ID of the company.
     *                      - role_id: The ID of the role.
     * @return bool Returns true if the role is valid, false otherwise.
     */
    private function isValidRole($request): bool
    {
        $role = $this->role::where('company_id', $request['company_id'])
            ->find($request['role_id']);

        return !is_null($role);
    }
}

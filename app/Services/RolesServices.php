<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Config;

class RolesServices
{
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const USER_TYPE_SUPER_ADMIN_UPPER = 'Admin';
    public const USER_TYPE_SUPER_ADMIN = 'admin';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_ADMIN = ['adminError' => true];
    public const ERROR_ADMIN_USER = ['adminUserError' => true];
    public const ERROR_SUBSIDIARY = ['subsidiaryError' => true];

    /**
     * @var Role
     */
    private Role $role;

    /**
     * @var Company
     */
    private Company $company;

    /**
     * Constructor method.
     * @param Role $role Instance of the Role class.
     * @param Company $company Instance of the Company class.
     * 
     * @return void
     */
    public function __construct(
        Role        $role, 
        Company     $company
    )
    {
        $this->role = $role;
        $this->company = $company;
    }

    /**
     * Creates the validation rules for create a new role.
     *
     * @return array The array containing the validation rules.
     */
    public function createValidation(): array
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250',
        ];
    }

    /**
     * Creates the validation rules for updating the role.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250'
        ];
    }

    /**
     * Returns a paginated list of role based on the given search request.
     * 
     * @param array $request The search request parameters and company id.
     * @return mixed Returns the paginated list of role.
     */
    public function list($request): mixed
    {
        return $this->role
            ->whereIn('company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('id', 'role_name', 'status', 'editable')
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the role.
     * 
     * @param array $request The request data containing role id, company id
     * @return mixed Returns the role.
     */
    public function show($request): mixed
    {
        return $this->role::whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Creates a new role from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "adminUserError": A array returns adminUserError if special permission has not allowed.
     * - "isSubmit": A boolean indicating if the role was successfully updated.
     */
    public function create($request): bool|array
    {
        $validationResult = $this->createRoleValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $specialPermissionvalidationResult = $this->createSpecialPermissionValidateRequest($request);
        if (is_array($specialPermissionvalidationResult)) {
            return $specialPermissionvalidationResult;
        }

        $roleDetails = $this->createRole($request);
        
        if ($request['special_permission'] == self::DEFAULT_INTEGER_VALUE_ONE) {
            $subsidiaryCompanyIds = $this->showCompany($request);
            $subsidiaryCompanyIds = array_column($subsidiaryCompanyIds, 'id');
            $this->createSpecialPermission($subsidiaryCompanyIds, $roleDetails);
        }

        return true;
    }

    /**
     * Updates the role from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if role is null.
     * - "isSubmit": A boolean indicating if the role was successfully updated.
     */
    public function update($request): bool|array
    {
        $role = $this->showRole($request);
        if (is_null($role)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $this->updateRole($role, $request);
        
        return true;
    }

    /**
     * Delete the role.
     * 
     * @param array $request The request data containing the role ID and company ID.
     * @return boolean indicating if the role was successfully deleted.
     */
    public function delete($request): bool
    {
        return $this->showRole($request)->delete();
    }

    /**
     * Returns a list of role based on the given search request.
     * 
     * @param array $companyId The array of company ids
     * @return mixed Returns the list of role.
     */
    public function dropDown($companyId): mixed
    {
        return $this->role->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereIn('company_id', $companyId)
            ->select('id', 'role_name', 'special_permission', 'editable')
            ->get();
    }

    /**
     * Updates the role status with the given request.
     * 
     * @param array $request The array containing role id, status.
     * @return array Returns an array with the following keys:
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
     */
    public function updateStatus($request) : array
    {
        $role = $this->showRole($request);
        if (is_null($role)) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $role->status = $request['status'];
        $role->modified_by = $request['modified_by'];
        return  [
            "isUpdated" => $role->save() == self::DEFAULT_INTEGER_VALUE_ONE,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search'])) {
            $query->where('role_name', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createRoleValidateRequest($request): array|bool
    {
        if ($request['name'] == self::USER_TYPE_SUPER_ADMIN_UPPER || $request['name'] == self::USER_TYPE_SUPER_ADMIN) {
            return self::ERROR_ADMIN;
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createSpecialPermissionValidateRequest($request): array|bool
    {
        if ($request['special_permission'] == self::DEFAULT_INTEGER_VALUE_ONE) {
            if ($request['user_type'] != self::USER_TYPE_SUPER_ADMIN_UPPER) {
                return self::ERROR_ADMIN_USER;
            }

            $companyDetail = $this->company->findOrFail($request['company_id']);
            if ($companyDetail->parent_id != self::DEFAULT_INTEGER_VALUE_ZERO) {
                return self::ERROR_SUBSIDIARY;
            }
        }

        return true;
    }
    
    /**
     * Creates a new role from the given request data.
     * 
     * @param array $request The array containing role data.
     *                      The array should have the following keys:
     *                      - role_name: The role name of the role.
     *                      - system_role: The system role of the role.
     *                      - status: The status of the role.
     *                      - parent_id: The parent id of the role.
     *                      - company_id: The company id of the role.
     *                      - special_permission: The special permission of the role.
     *                      - created_by: The ID of the role who created the application.
     *                      - modified_by: (int) The updated role modified by.
     * 
     * @return role The newly created role object.
     */
    private function createRole($request)
    {
        return $this->role->create([
            'role_name'     => $request['name'] ?? '',
            'system_role'   => $request['system_role'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'status'        => $request['status'] ?? self::DEFAULT_INTEGER_VALUE_ONE,
            'parent_id'     => $request['parent_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'company_id'   => $request['company_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'special_permission' => $request['special_permission'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }
    
    /**
     * Show the company.
     * 
     * @param array $request The request data containing company id
     * @return mixed Returns the company.
     */
    private function showCompany($request)
    {
        return $this->company->where('parent_id', $request['company_id'])
            ->select('id')
            ->get()
            ->toArray();
    }
     
    /**
     * Creates a new special permission role from the given request data.
     * 
     * @param array $subsidiaryCompanyIds The array containing subsidiary company ids.
     * @param array $roleDetails The array containing role data.
     *                      The array should have the following keys:
     *                      - role_name: The role name of the role.
     *                      - system_role: The system role of the role.
     *                      - status: The status of the role.
     *                      - parent_id: The parent id of the role.
     *                      - subsidiaryCompanyId: The subsidiary company id of the role.
     *                      - parent_role_id: The parent role id of the role.
     *                      - created_by: The ID of the role who created the application.
     *                      - modified_by: (int) The updated role modified by.
     * 
     * @return void
     */
    private function createSpecialPermission($subsidiaryCompanyIds, $roleDetails)
    {
        foreach ($subsidiaryCompanyIds as $subsidiaryCompanyId) {
            $this->role->create([
                'role_name'     => $roleDetails->role_name,
                'system_role'   => $roleDetails->system_role,
                'status'        => $roleDetails->status,
                'parent_id'     => $roleDetails->parent_id,
                'created_by'    => $roleDetails->created_by,
                'modified_by'   => $roleDetails->modified_by,
                'company_id'    => $subsidiaryCompanyId ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                'special_permission' => self::DEFAULT_INTEGER_VALUE_ZERO,
                'parent_role_id' => $roleDetails->id
            ]);
        }
    }
    
    /**
     * Show the role.
     * 
     * @param array $request The request data containing company id, role id
     * @return mixed Returns the role.
     */
    private function showRole($request)
    {
        return $this->role::where('company_id', $request['company_id'])->find($request['id']);
    }
    
    /**
     * Updates the role data with the given request.
     * 
     * @param object $role The role object to be updated.
     * @param array $request The array containing role data.
     *                      The array should have the following keys:
     *                      - name: The updated name.
     *                      - system_role: The updated system type.
     *                      - status: The updated status.
     *                      - parent_id: The updated parent id.
     *                      - modified_by: The updated role modified by.
     * 
     * @return void
     */
    private function updateRole($role, $request)
    {
        $role->role_name    = $request['name'] ?? $role->role_name;
        $role->system_role  = $request['system_role'] ?? $role->system_role;
        $role->status       = $request['status'] ?? $role->status;
        $role->parent_id    = $request['parent_id'] ?? $role->parent_id;
        $role->modified_by  = $request['modified_by'] ?? $role->modified_by;
        $role->save();
    }
}

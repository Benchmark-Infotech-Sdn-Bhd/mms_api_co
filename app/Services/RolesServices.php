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
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250',
        ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:250'
        ];
    }

    /**
     * @param $request
     * @return mixed
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
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->role::whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * @param $request
     * @return bool|array
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
     * @param $request
     * @return bool|array
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
     * @param $request
     * @return bool
     */
    public function delete($request): bool
    {
        return $this->showRole($request)->delete();
    }

    /**
     * @param $companyId
     * @return mixed
     */
    public function dropDown($companyId): mixed
    {
        return $this->role->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereIn('company_id', $companyId)
            ->select('id', 'role_name', 'special_permission', 'editable')
            ->get();
    }

    /**
     * @param $request
     * @return array
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
     */
    private function applySearchFilter($query, $request)
    {
        if (isset($request['search']) && !empty($request['search'])) {
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

    private function showCompany($request)
    {
        return $this->company->where('parent_id', $request['company_id'])
            ->select('id')
            ->get()->toArray();
    }

    private function createSpecialPermission($subsidiaryCompanyIds, $roleDetails)
    {
        foreach ($subsidiaryCompanyIds as $subsidiaryCompanyId) {
            $subRole = $this->role->create([
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

    private function showRole($request)
    {
        return $this->role::where('company_id', $request['company_id'])->find($request['id']);
    }

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

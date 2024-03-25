<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use App\Models\Role;

class EmployeeServices
{
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const MESSAGE_EMPLOYEE_NOT_CREATED = 'Employee not created';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_EMPLOYEE_NOT_UPDATED = 'Employee not updated';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const USER_TYPE_EMPLOYEE = 'Employee';
    public const MESSAGE_UPDATE_STATUS_FAILURE = '“You are not allowed to update user status due to an inactive branch assigned, Kindly “Reactive the branch associated with this user” or ”assign to a new branch to the user”';

    public const ERROR_INVALID_USER = ['InvalidUser' => true];
    public const ERROR_ROLE = ['roleError' => true];

    /**
     * @var Employee
     */
    private Employee $employee;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * @var Role
     */
    private Role $role;

    /**
     * @var User
     */
    private User $user;

    /**
     * @var Company
     */
    private Company $company;

    /**
     * @var Branch
     */
    private Branch $branch;

    /**
     * Constructor method.
     *
     * @param Employee $employee Instance of the employee class.
     * @param ValidationServices $validationServices Instance of the validation services class.
     * @param AuthServices $authServices Instance of the auth services class.
     * @param Role $role Instance of the role class.
     * @param User $user Instance of the user class.
     * @param Company $company Instance of the company class.
     * @param Branch $branch Instance of the branch class.
     *
     * @return void.
     */
    public function __construct(
        Employee               $employee,
        ValidationServices     $validationServices,
        AuthServices           $authServices,
        Role                   $role,
        User                   $user,
        Company                $company,
        Branch                 $branch
    )
    {
        $this->employee = $employee;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->role = $role;
        $this->user = $user;
        $this->company = $company;
        $this->branch = $branch;
    }

    /**
     * Creates a new employee from the given request data.
     *
     * @param array $request The array containing employee data.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "roleValidate": An array of roleValidate errors, if invalid role.
     * - "companiesValidation" An array of companiesValidation errors, if invalid subsidiary copanies.
     * - "branchValidation": An array of branchValidation errors, if invalid branch.
     * - "isCreated": A boolean indicating if the employee was successfully created.
     */
    public function create($request) : mixed
    {
        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $roleValidation = $this->createRoleValidation($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $subsidiaryCompaniesValidation = $this->createSubsidiaryCompaniesValidation($request);
        if (is_array($subsidiaryCompaniesValidation)) {
            return $subsidiaryCompaniesValidation;
        }

        $branchValidation = $this->createBranchValidation($request);
        if (is_array($branchValidation)) {
            return $branchValidation;
        }

        $employee = $this->createEmployee($request);

        $res = $this->createEmployeeAuth($employee, $request);

        if ($res) {
            return $employee;
        }

        $employee->delete();

        return [
            "isCreated" => false,
            "message"=> self::MESSAGE_EMPLOYEE_NOT_CREATED
        ];
    }

    /**
     * Updates the employee from the given request data.
     *
     * @param array $request The array containing employee data.
     * @return array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "dataNotFound": A array returns dataNotFound if employeeCompany is null.
     * - "employeeNotFound": A array returns employeeNotFound if employeeAuth is null.
     * "isUpdated": Indicates whether the data was updated. Always set to `false`.
     */
    public function update($request): array
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $employee = $this->showEmployeeCompany($request);
        if (is_null($employee)) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $res = $this->updateEmployeeAuth($employee, $request);
        if (!$res) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_EMPLOYEE_NOT_UPDATED
            ];
        }

        return $this->updateEmployee($employee, $request);
    }

    /**
     * Delete the employee
     *
     * @param array $request The request data containing the employee id and company id.
     * @return array The result of the delete operation containing the deletion status and message.
     */
    public function delete($request) : array
    {
        $validationResult = $this->deleteValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $employee = $this->showDeleteEmployeeCompany($request);
        if (is_null($employee)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $res = $this->deleteAuth($request);
        if (!$res) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        return [
            "isDeleted" => $employee->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Show the employee with related branch and companies.
     *
     * @param array $request The request data containing employee id, company id
     * @return array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - Returns the employee with related branch and companies
     */
    public function show($request): array
    {
        $validationResult = $this->showValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $emp = $this->showEmployeeWithBranchAndUser($request);
        $companies = $this->showUserWithCompanies($emp->user->id);
        if (isset($emp) && isset($emp['id'])) {
            $user = $this->showAuthUserWithRole($emp['id']);
            $emp['email'] = $user['email'];
            $emp['role_id'] = $user['role_id'];
        }

        return [
            'employeeDetails' => $emp,
            'User' => $companies
        ];
    }

    /**
     * Updates the employee status from the given request data.
     *
     * @param array $request The request data containing employee id, company id and status.
     *
     * @return array "isUpdated": Returns an array with 'error' as key and validation error messages as value if status updation fails. | Returns true if employee status was successfully updated.
     */
    public function updateStatus($request): array
    {
        $validationResult = $this->updateStatusValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $employee = $this->showEmployeeWithBranch($request);
        if (is_null($employee)) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        if ($request['status'] == self::DEFAULT_INTEGER_VALUE_ONE) {
            if (is_null($employee['branches']) || ($employee['branches']['status'] == self::DEFAULT_INTEGER_VALUE_ZERO)) {
                return [
                    "isUpdated" => false,
                    "message"=> self::MESSAGE_UPDATE_STATUS_FAILURE
                ];
            }
        }

        $employee->status = $request['status'];
        return  [
            "isUpdated" => $employee->save() == self::DEFAULT_INTEGER_VALUE_ONE,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Returns a paginated list of employee with related branch, roles and user_role_type.
     *
     * @param array $request The search request parameters and company id.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - Returns a paginated list of employee with related branch, roles and user_role_type
     */
    public function list($request): mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->employee->join('branch', 'branch.id', '=', 'employee.branch_id')
            ->join('users', function ($join) {
                $join->on('employee.id', '=', 'users.reference_id')
                ->where('users.user_type', self::USER_TYPE_EMPLOYEE);
            })
            ->join('user_role_type','users.id','=','user_role_type.user_id')
            ->join('roles','user_role_type.role_id','=','roles.id')
            ->where(function ($query) use ($request) {
                $this->applyCompanyFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })->select('employee.id','employee.employee_name','users.email','employee.position','branch.branch_name','employee.salary','employee.status','employee.created_at')
            ->selectRaw("(CASE WHEN (roles.status = 1) THEN roles.role_name ELSE null END) as role_name")
            ->distinct()
            ->orderBy('employee.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Returns a list of employee.
     *
     * @param id $companyId The id of the employee company.
     * @return mixed Returns a list of employee.
     */
    public function dropdown($companyId): mixed
    {
        return $this->employee->where('status', self::DEFAULT_INTEGER_VALUE_ONE)
            ->whereIn('company_id', $companyId)
            ->whereNull('deleted_at')
            ->select('id','employee_name')
            ->orderBy('employee.created_at','DESC')
            ->get();
    }

    /**
     * Updates the employee status.
     *
     * @param $request The request data containing branch id, status.
     * @return array Returns an array with the following keys:
     * - "isUpdated": Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if employee status updated successfully.
     */
    public function updateStatusBasedOnBranch($request): array
    {
        $employee = $this->employee->where('branch_id', $request['branch_id'])
            ->update(['status' => $request['status']]);

        return  [
            "isUpdated" => $employee,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Returns a list of supervisor with related employee, transportation, roles and user_role_type.
     *
     * @param array $request The request data containing company id
     * @return mixed Returns a list of supervisor with related employee, transportation, roles and user_role_type.
     */
    public function supervisorList($request): mixed
    {
        $role = $this->showSupervisorRole($request);

        $employee = $this->user
            ->leftJoin('employee', 'employee.id', '=', 'users.reference_id')
            ->leftJoin('transportation as supervisorTransportation', function($query) {
                $query->on('supervisorTransportation.id','=','users.reference_id')
                ->where('supervisorTransportation.assigned_supervisor', self::DEFAULT_INTEGER_VALUE_ONE);
            })
            ->join('user_role_type','users.id','=','user_role_type.user_id')
            ->join('roles','user_role_type.role_id','=','roles.id')
            ->where(function ($query) use ($role) {
                $this->applySupervisorListSearchFilter($query, $role);
            })
            ->select('users.id')
            ->selectRaw('IF(users.user_type = "Employee", employee.employee_name, supervisorTransportation.driver_name) as supervisor_name, IF(users.user_type = "Employee", "employee", "driver") as supervisor_type')
            ->distinct('users.id', 'users.user_type', 'employee.employee_name', 'supervisorTransportation.driver_name')
            ->orderBy('users.id','DESC')
            ->get();

        return $employee;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,$this->employee->rules))) {
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data containing role id, company id.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createRoleValidation($request): array|bool
    {
        $roleDetails = $this->findRole($request['role_id']);
        if (is_null($roleDetails)) {
            return self::ERROR_INVALID_USER;
        }

        if ($roleDetails->special_permission == self::DEFAULT_INTEGER_VALUE_ZERO && count($request['subsidiary_companies']) > self::DEFAULT_INTEGER_VALUE_ZERO) {
            return self::ERROR_ROLE;
        }

        if ($request['company_id'] != $roleDetails->company_id) {
            return self::ERROR_INVALID_USER;
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data containing role id, subsidiary companies id.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createSubsidiaryCompaniesValidation($request): array|bool
    {
        if (count($request['subsidiary_companies']) > self::DEFAULT_INTEGER_VALUE_ZERO) {
            $subsidiaryCompanyIds = $this->showCompany($request['company_id']);
            $subsidiaryCompanyIds = array_column($subsidiaryCompanyIds, 'id');
            $diffCompanyIds = array_diff($request['subsidiary_companies'], $subsidiaryCompanyIds);
            if (count($diffCompanyIds) > self::DEFAULT_INTEGER_VALUE_ZERO) {
                return self::ERROR_INVALID_USER;
            }
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data containing branch id, company id.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createBranchValidation($request): array|bool
    {
        $barnchDetails = $this->findBranch($request['branch_id']);
        if (is_null($barnchDetails)) {
            return self::ERROR_INVALID_USER;
        }

        if($request['company_id'] != $barnchDetails->company_id) {
            return self::ERROR_INVALID_USER;
        }

        return true;
    }

    /**
     * Creates a new employee from the given request data.
     *
     * @param array $request The array containing employee data.
     *                      The array should have the following keys:
     *                      - employee_name: The employee name of the employee.
     *                      - gender: The gender of the employee.
     *                      - date_of_birth: The date of birth of the employee.
     *                      - ic_number: The ic number of the employee.
     *                      - passport_number: The passport number of the employee.
     *                      - contact_number: The contact number of the employee.
     *                      - address: The address of the employee.
     *                      - postcode: The postcode of the employee.
     *                      - position: The position of the employee.
     *                      - branch_id: The branch id of the employee.
     *                      - salary: The salary of the employee.
     *                      - status: The status of the employee.
     *                      - city: The city of the employee.
     *                      - state: The state of the employee.
     *                      - company_id: The company id of the employee.
     *                      - created_by: The ID of the user who created the employee.
     *                      - modified_by: The updated employee modified by.
     *
     * @return employee The newly created employee object.
     */
    private function createEmployee($request)
    {
        return $this->employee->create([
            'employee_name' => $request['employee_name'] ?? '',
            'gender' => $request['gender'] ?? '',
            'date_of_birth' => $request['date_of_birth'] ?? '',
            'ic_number' => (int)$request['ic_number'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'passport_number' => $request['passport_number'] ?? '',
            'contact_number' => (int)$request['contact_number'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'address' => $request['address'] ?? '',
            'postcode' => (int)$request['postcode'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'position' => $request['position'] ?? '',
            'branch_id' => (int)$request['branch_id'],
            'salary' => (float)$request['salary'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'status' => self::DEFAULT_INTEGER_VALUE_ONE,
            'city' => $request['city'] ?? '',
            'state' => $request['state'] ?? '',
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'company_id' => $request['company_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    /**
     * Creates a new auth services from the given request data.
     *
     * @param array $request The array containing services data.
     *                      The array should have the following keys:
     *                      - employee_name: The employee name of the service.
     *                      - email: The email of the service.
     *                      - role_id: The role id of the service.
     *                      - user_id: The user id of the service.
     *                      - status: The status of the service.
     *                      - password: The password of the service.
     *                      - reference_id: The reference id of the service.
     *                      - user_type: The user type of the service.
     *                      - subsidiary_companies: The subsidiary companies of the service.
     *                      - company_id: The company id of the service.
     *
     * @return bool authServices The newly created authServices.
     */
    private function createEmployeeAuth($employee, $request)
    {
        return $this->authServices->create(
            ['name' => $request['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['created_by'],
            'status' => self::DEFAULT_INTEGER_VALUE_ONE,
            'password' => Str::random(8),
            'reference_id' => $employee['id'],
            'user_type' => self::USER_TYPE_EMPLOYEE,
            'subsidiary_companies' => $request['subsidiary_companies'],
            'company_id' => $request['company_id']
        ]);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,$this->employee->rulesForUpdation($request['id'])))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Show the role.
     *
     * @param int $role_id The id of the role
     * @return mixed Returns the role.
     */
    private function findRole($role_id)
    {
        return $this->role->find($role_id);
    }

    /**
     * Show the company.
     *
     * @param int $company_id The id of the company
     * @return mixed Returns the company.
     */
    private function showCompany($company_id)
    {
        return $this->company->where('parent_id', $company_id)
            ->select('id')
            ->get()
            ->toArray();
    }

    /**
     * Show the branch.
     *
     * @param int $branch_id The id of the branch
     * @return mixed Returns the branch.
     */
    private function findBranch($branch_id)
    {
        return $this->branch->find($branch_id);
    }

    /**
     * Show the employee.
     *
     * @param array $request The request data containing employee id, company id
     * @return mixed Returns the employee.
     */
    private function showEmployeeCompany($request)
    {
        return $this->employee->where('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Updates the auth services from the given request data.
     *
     * @param object $employee The employee object to be updated.
     * @param array $request The array containing services data.
     *                      The array should have the following keys:
     *                      - employee_name: The updated employee name.
     *                      - email: The updated email.
     *                      - role_id: The updated role id.
     *                      - user_id: The updated user id.
     *                      - reference_id: The updated reference id.
     *
     * @return bool authServices The updated authServices.
     */
    private function updateEmployeeAuth($employee, $request)
    {
        return $this->authServices->update(
            ['name' => $request['employee_name'] ?? $employee['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['modified_by'],
            'reference_id' => $request['id']
        ]);
    }

    /**
     * Updates the employee from the given request data.
     *
     * @param object $employee The employee object to be updated.
     * @param array $request The array containing employee data.
     *                      The array should have the following keys:
     *                      - id: The updated id.
     *                      - employee_name: The updated employee name.
     *                      - gender: The updated gender.
     *                      - date_of_birth: The updated date of birth.
     *                      - ic_number: The updated ic number.
     *                      - passport_number: The updated passport number.
     *                      - contact_number: The updated contact number.
     *                      - address: The updated address.
     *                      - postcode: The updated postcode.
     *                      - position: The updated position.
     *                      - branch_id: The updated branch id.
     *                      - salary: The updated salary.
     *                      - status: The updated status.
     *                      - city: The updated city.
     *                      - state: The updated state.
     *                      - modified_by: The updated employee modified by.
     *
     * @return array "isUpdated": Returns an array with 'error' as key and validation error messages as value if updation fails. | Returns true if employee was successfully updated.
     */
    private function updateEmployee($employee, $request)
    {
        return [
            "isUpdated" => $employee->update([
                'id' => $request['id'],
                'employee_name' => $request['employee_name'] ?? $employee['employee_name'],
                'gender' => $request['gender'] ?? $employee['gender'],
                'date_of_birth' => $request['date_of_birth'] ?? $employee['date_of_birth'],
                'ic_number' => (int)$request['ic_number'] ?? $employee['ic_number'],
                'passport_number' => $request['passport_number'] ?? $employee['passport_number'],
                'contact_number' => (int)$request['contact_number'] ?? $employee['contact_number'],
                'address' => $request['address'] ?? $employee['address'],
                'postcode' => (int)$request['postcode'] ?? $employee['postcode'],
                'position' => $request['position'] ?? $employee['position'],
                'branch_id' => (int)$request['branch_id'] ?? $employee['branch_id'],
                'salary' => (float)$request['salary'] ?? $employee['salary'],
                'status' => $employee['status'],
                'city' => $request['city'] ?? $employee['city'],
                'state' => $request['state'] ?? $employee['state'],
                'modified_by'   => $request['modified_by'] ?? $employee['modified_by']
            ]),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function deleteValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Show the employee.
     *
     * @param array $request The request data containing employee id, company id
     * @return mixed Returns the employee.
     */
    private function showDeleteEmployeeCompany($request)
    {
        return $this->employee->where('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Delete the auth services.
     *
     * @param array $request The array containing reference id.
     * @return mixed array Returns an 'error' if deleted fails. | Returns true if auth services was successfully deleted.
     */
    private function deleteAuth($request)
    {
        return $this->authServices->delete(['reference_id' => $request['id']]);
    }

    /**
     * Show the employee with related branches and user.
     *
     * @param array $request The request data containing employee id
     * @return mixed Returns the employee with related branches and user.
     */
    private function showEmployeeWithBranchAndUser($request)
    {
        return $this->employee->with(['branches', 'user'])->find($request['id']);
    }

    /**
     * Show the user with related companies.
     *
     * @param int $id The id of the user.
     * @return mixed Returns the user with related companies.
     */
    private function showUserWithCompanies($id)
    {
        return $this->user->with('companies')->findOrFail($id);
    }

    /**
     * Show the auth services.
     *
     * @param int $id The id of the auth services.
     * @return mixed Returns the auth services.
     */
    private function showAuthUserWithRole($id)
    {
        return $this->authServices->userWithRolesBasedOnReferenceId(['id' => $id]);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function showValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateStatusValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Show the employee with related branches.
     *
     * @param array $request The request data containing company id, employee id
     * @return mixed Returns the employee with related branches.
     */
    private function showEmployeeWithBranch($request)
    {
        return $this->employee->with('branches')->whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request,['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Apply the "company" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyCompanyFilter($query, $request)
    {
        $query->whereIn('employee.company_id', $request['company_id'])->whereNull('employee.deleted_at');
    }

    /**
     * Apply the search filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The search request parameters and status, branch id and role_id.
     *
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('employee.employee_name', 'like', "%{$request['search_param']}%")
            ->orWhere('employee.ic_number', 'like', '%'.$request['search_param'].'%')
            ->orWhere('employee.passport_number', 'like', '%'.$request['search_param'].'%')
            ->orWhere('users.email', 'like', '%'.$request['search_param'].'%');
        }
        if (isset($request['status'])) {
            $query->where('employee.status',$request['status']);
        }
        if (isset($request['branch_id'])) {
            $query->where('employee.branch_id',$request['branch_id']);
        }
        if (isset($request['role_id'])) {
            $query->where('roles.id',$request['role_id']);
        }
    }

    /**
     * Show the supervisor role.
     *
     * @param array $request The request data containing company id
     * @return mixed Returns the supervisor role.
     */
    private function showSupervisorRole($request)
    {
        return $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
            ->whereIn('company_id', $request['company_id'])
            ->whereNull('deleted_at')
            ->where('status',self::DEFAULT_INTEGER_VALUE_ONE)
            ->first('id');
    }

    /**
     * Apply the "supervisor" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $role The role data containing the role id
     *
     * @return void
     */
    private function applySupervisorListSearchFilter($query, $role)
    {
        $query->where('roles.id',$role->id ?? self::DEFAULT_INTEGER_VALUE_ZERO)
            ->whereNull('employee.deleted_at')
            ->whereNull('supervisorTransportation.deleted_at');
    }
}

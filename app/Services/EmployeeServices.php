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
use App\Models\Transportation;

class EmployeeServices
{
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const MESSAGE_EMPLOYEE_NOT_CREATED = 'Employee not created';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_EMPLOYEE_NOT_UPDATED = 'Employee not updated';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
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
     * @var Transportation
     */
    private Transportation $transportation;

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
     * @param Transportation $transportation Instance of the transportation class.
     * @param Company $company Instance of the company class.
     * @param Branch $branch Instance of the branch class.
     */
    public function __construct(
        Employee               $employee,
        ValidationServices     $validationServices,
        AuthServices           $authServices,
        Role                   $role, 
        User                   $user, 
        Transportation         $transportation, 
        Company                $company, 
        Branch                 $branch
    )
    {
        $this->employee = $employee;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->role = $role;
        $this->user = $user;
        $this->transportation = $transportation;
        $this->company = $company;
        $this->branch = $branch;
    }

    /**
     * @param $request
     * @return mixed
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
     * @param $request
     * @return array
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
     * @param $request
     * @return array
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
     * @param $request
     * @return array
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
     * @param $request
     * @return array
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
     * @param $request
     * @return mixed
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
                    ->on('employee.employee_name', '=', 'users.name');
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
     * @param $companyId
     * @return mixed
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
     * @param $request
     * @return array
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
     * $param $request
     * @return mixed
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
            'user_type' => "Employee",
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

    private function findRole($role_id)
    {
        return $this->role->find($role_id);
    }

    private function showCompany($company_id)
    {
        return $this->company->where('parent_id', $company_id)
            ->select('id')
            ->get()
            ->toArray();
    }

    private function findBranch($branch_id)
    {
        return $this->branch->find($branch_id);
    }

    private function showEmployeeCompany($request)
    {
        return $this->employee->where('company_id', $request['company_id'])->find($request['id']);
    }

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

    private function showDeleteEmployeeCompany($request)
    {
        return $this->employee->where('company_id', $request['company_id'])->find($request['id']);
    }

    private function deleteAuth($request)
    {
        return $this->authServices->delete(['reference_id' => $request['id']]);
    }

    private function showEmployeeWithBranchAndUser($request)
    {
        return $this->employee->with(['branches', 'user'])->find($request['id']);
    }

    private function showUserWithCompanies($id)
    {
        return $this->user->with('companies')->findOrFail($id);
    }

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

    private function applyCompanyFilter($query, $request)
    {
        $query->whereIn('employee.company_id', $request['company_id'])->whereNull('employee.deleted_at');
    }

    private function applySearchFilter($query, $request)
    {
        if (isset($request['search_param']) && !empty($request['search_param'])) {
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

    private function showSupervisorRole($request)
    {
        return $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
            ->whereIn('company_id', $request['company_id'])
            ->whereNull('deleted_at')
            ->where('status',self::DEFAULT_INTEGER_VALUE_ONE)
            ->first('id');
    }

    private function applySupervisorListSearchFilter($query, $role)
    {
        $query->where('roles.id',$role->id ?? self::DEFAULT_INTEGER_VALUE_ZERO)
            ->whereNull('employee.deleted_at')
            ->whereNull('supervisorTransportation.deleted_at');
    }
}

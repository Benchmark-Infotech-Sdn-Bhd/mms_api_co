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
    public const USER_TYPE_EMPLOYEE = 'Employee';
    public const MESSAGE_EMPLOYEE_NOT_CREATED = 'Employee not created';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_EMPLOYEE_NOT_UPDATED = 'Employee not updated';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';

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
     * Constructs a new instance of the class.
     * 
     * @param Employee $employee The employee object.
     * @param ValidationServices $validationServices The validation services object.
     * @param AuthServices $authServices The auth services object.
     * @param Role $role The role object.
     * @param User $user The user object.
     * @param Transportation $transportation The transportation object.
     * @param Company $company The company object.
     * @param Branch $branch The branch object.
     */
    public function __construct(
        Employee $employee,
        ValidationServices $validationServices,
        AuthServices $authServices,
        Role $role, 
        User $user, 
        Transportation $transportation, 
        Company $company, 
        Branch $branch
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
        if (!($this->validationServices->validate($request,$this->employee->rules))) {
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        $roleDetails = $this->role->find($request['role_id']);
        if (is_null($roleDetails)) {
            return [
                'InvalidUser' => true
            ];
        }

        if ($roleDetails->special_permission == 0 && count($request['subsidiary_companies']) > 0) {
            return [
                'roleError' => true
            ];
        }

        if ($request['company_id'] != $roleDetails->company_id) {
            return [
                'InvalidUser' => true
            ];
        }

        if (count($request['subsidiary_companies']) > 0) {
            $subsidiaryCompanyIds = $this->company->where('parent_id', $request['company_id'])
                                    ->select('id')
                                    ->get()->toArray();
            $subsidiaryCompanyIds = array_column($subsidiaryCompanyIds, 'id');
            $diffCompanyIds = array_diff($request['subsidiary_companies'], $subsidiaryCompanyIds);
            if (count($diffCompanyIds) > 0) {
                return [
                    'InvalidUser' => true
                ];
            }
        }

        $barnchDetails = $this->branch->find($request['branch_id']);
        if (is_null($barnchDetails)) {
            return [
                'InvalidUser' => true
            ];
        } else if($request['company_id'] != $barnchDetails->company_id) {
            return [
                'InvalidUser' => true
            ];
        }

        $employee = $this->employee->create([
            'employee_name' => $request['employee_name'] ?? '',
            'gender' => $request['gender'] ?? '',
            'date_of_birth' => $request['date_of_birth'] ?? '',
            'ic_number' => (int)$request['ic_number'] ?? 0,
            'passport_number' => $request['passport_number'] ?? '',
            'contact_number' => (int)$request['contact_number'] ?? 0,
            'address' => $request['address'] ?? '',
            'postcode' => (int)$request['postcode'] ?? 0,
            'position' => $request['position'] ?? '',
            'branch_id' => (int)$request['branch_id'],
            'salary' => (float)$request['salary'] ?? 0,
            'status' => 1,
            'city' => $request['city'] ?? '',
            'state' => $request['state'] ?? '',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0,
            'company_id' => $request['company_id'] ?? 0
        ]);

        $res = $this->authServices->create(
            ['name' => $request['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['created_by'],
            'status' => 1,
            'password' => Str::random(8),
            'reference_id' => $employee['id'],
            'user_type' => "Employee",
            'subsidiary_companies' => $request['subsidiary_companies'],
            'company_id' => $request['company_id']
        ]);
        if ($res) {
            return $employee;
        }
        $employee->delete();

        return [
            "isCreated" => false,
            "message"=> "Employee not created"
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        if (!($this->validationServices->validate($request,$this->employee->rulesForUpdation($request['id'])))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $employee = $this->employee->where('company_id', $request['company_id'])->find($request['id']);
        if (is_null($employee)) {
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }

        $res = $this->authServices->update(
            ['name' => $request['employee_name'] ?? $employee['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['mod ied_by'],
            'reference_id' => $request['id']
        ]);
        if (!$res) {
            return [
                "isUpdated" => false,
                "message"=> "Employee not updated"
            ];
        }

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
            "message" => "Updated Successfully"
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function delete($request) : array
    {

        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        
        $employee = $this->employee->whereIn('company_id', $request['company_id'])->find($request['id']);
        if (is_null($employee)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $res = $this->authServices->delete(['reference_id' => $request['id']]);
        if (!$res) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        return [
            "isDeleted" => $employee->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function show($request): array
    {
        if (!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $emp = $this->employee->with(['branches', 'user'])->find($request['id']);
        $companies = $this->user->with('companies')->findOrFail($emp->user->id);
        if (isset($emp) && isset($emp['id'])) {
            $user = $this->authServices->userWithRolesBasedOnReferenceId(['id' => $emp['id']]);
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

        if (!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $employee = $this->employee->with('branches')->whereIn('company_id', $request['company_id'])->find($request['id']);
        if (is_null($employee)) {
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }

        if ($request['status'] == 1) {
            if (is_null($employee['branches']) || ($employee['branches']['status'] == 0)) {
                return [
                    "isUpdated" => false,
                    "message"=> '“You are not allowed to update user status due to an inactive branch assigned, Kindly “Reactive the branch associated with this user” or ”assign to a new branch to the user”'
                ];
            }
        }

        $employee->status = $request['status'];
        return  [
            "isUpdated" => $employee->save() == 1,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        if (isset($request['search_param']) && !empty($request['search_param'])) {
            if (!($this->validationServices->validate($request,['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return $this->employee->join('branch', 'branch.id', '=', 'employee.branch_id')
        ->join('users', function ($join) {
            $join->on('employee.id', '=', 'users.reference_id')
                ->on('employee.employee_name', '=', 'users.name');
        })
        ->join('user_role_type','users.id','=','user_role_type.user_id')
        ->join('roles','user_role_type.role_id','=','roles.id')
        ->whereIn('employee.company_id', $request['company_id'])
        ->whereNull('employee.deleted_at')
        ->where(function ($query) use ($request) {
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
        return $this->employee->where('status', 1)
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
            "message" => "Updated Successfully"
        ];
    }

    /**
     * $param $request
     * @return mixed
     */
    public function supervisorList($request): mixed
    {
        $role = $this->role->where('role_name', Config::get('services.EMPLOYEE_ROLE_TYPE_SUPERVISOR'))
                ->whereIn('company_id', $request['company_id'])
                ->whereNull('deleted_at')
                ->where('status',1)
                ->first('id');

        $employee = $this->user
        ->leftJoin('employee', 'employee.id', '=', 'users.reference_id')
        ->leftJoin('transportation as supervisorTransportation', function($query) {
            $query->on('supervisorTransportation.id','=','users.reference_id')
            ->where('supervisorTransportation.assigned_supervisor', 1);
        })
        ->join('user_role_type','users.id','=','user_role_type.user_id')
        ->join('roles','user_role_type.role_id','=','roles.id')
        ->where('roles.id',$role->id ?? 0)
        ->whereNull('employee.deleted_at')
        ->whereNull('supervisorTransportation.deleted_at')
        ->select('users.id')
        ->selectRaw('IF(users.user_type = "Employee", employee.employee_name, supervisorTransportation.driver_name) as supervisor_name, IF(users.user_type = "Employee", "employee", "driver") as supervisor_type')
        ->distinct('users.id', 'users.user_type', 'employee.employee_name', 'supervisorTransportation.driver_name')
        ->orderBy('users.id','DESC')
        ->get();
        return $employee;
    }
}

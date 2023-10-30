<?php

namespace App\Services;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserProfileServices
{
	private User $user;
    private Employee $employee;
    private Role $role;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    /**
     * UserProfileServices constructor.
     * @param User $user
     * @param Employee $employee
     * @param Role $role
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     */
    public function __construct(User $user, Employee $employee, Role $role, ValidationServices $validationServices,
    AuthServices $authServices)
    {
        $this->user = $user;
        $this->employee = $employee;
        $this->role = $role;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function adminShow($request) : mixed
    {
    	if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $user = $this->user->find($request['id']);        
        return $user;
    }

    /**
     * @param $request
     * @return bool
     */
    public function adminUpdate($request)
    {
    	if(!($this->validationServices->validate($request,['id' => 'required','name' => 'required','email' => 'required|email|max:150|unique:users,email,'.$request['id'].',id,deleted_at,NULL']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = $this->user->where('id',$request['id'])->first();
        
        $user->update([
            'name' => $request['name'] ?? $user['name'],
            'email' => $request['email'] ?? $user['email'],
            'modified_by' => $request['user_id'] ?? $user['modified_by']
        ]);

        return  [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return bool
     */
    public function adminResetPassword($request)
    {
    	if(!($this->validationServices->validate($request,['id' => 'required', 'current_password' => 'required', 'new_password' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = $this->user->where('id',$request['id'])->first();
        
        $user->update([
            'password' => Hash::make($request['new_password']),
            'modified_by' => $user['modified_by']
        ]);

        return  [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return array
     */
    public function employeeShow($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $emp = $this->employee->with(['branches', 'user'])->findOrFail($request['id']);
        $companies = $this->user->with('companies')->findOrFail($emp->user->id);
        if(isset($emp) && isset($emp['id'])){
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
    public function employeeUpdate($request) : array
    {
        if(!($this->validationServices->validate($request,$this->employee->rulesForProfileUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $employee = $this->employee->find($request['id']);
        if(is_null($employee)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $res = $this->authServices->update(
            ['name' => $request['employee_name'] ?? $employee['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['modified_by'],
            'reference_id' => $request['id']
        ]);
        if(!$res){
            return [
                "isUpdated" => false,
                "message"=> "Employee not updated"
            ];
        }
        return [
            "isUpdated" => $employee->update([
                'employee_name' => $request['employee_name'] ?? $employee['employee_name'],
                'contact_number' => (int)$request['contact_number'] ?? $employee['contact_number'],
                'address' => $request['address'] ?? $employee['address'],
                'city' => $request['city'] ?? $employee['city'],
                'state' => $request['state'] ?? $employee['state'],
                'modified_by'   => $request['modified_by'] ?? $employee['modified_by']
            ]),
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return bool
     */
    public function employeeResetPassword($request)
    {
    	if(!($this->validationServices->validate($request,['id' => 'required', 'current_password' => 'required', 'new_password' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $employee = $this->employee->find($request['id']);
        if(is_null($employee)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }

        $user = $this->user->where('reference_id',$request['id'])->first();
        
        $user->update([
            'password' => Hash::make($request['new_password']),
            'modified_by' => $user['modified_by']
        ]);

        return  [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }

}

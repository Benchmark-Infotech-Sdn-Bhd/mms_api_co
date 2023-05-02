<?php

namespace App\Services;

use App\Models\Employee;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;

class EmployeeServices
{
    private Employee $employee;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    /**
     * EmployeeServices constructor.
     * @param Employee $employee
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     */
    public function __construct(Employee $employee,ValidationServices $validationServices,
    AuthServices $authServices)
    {
        $this->employee = $employee;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->employee->rules))){
            return [
              'validate' => $this->validationServices->errors()
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
            'modified_by'   => $request['created_by'] ?? 0
        ]);
        $res = $this->authServices->create(
            ['name' => $request['employee_name'],
            'email' => $request['email'],
            'role_id' => (int)$request['role_id'],
            'user_id' => $request['created_by'],
            'status' => 1,
            // 'password' => Str::random(8),
            'password' => 'Test123',
            'reference_id' => $employee['id'],
            'user_type' => "Employee"
        ]);
        if($res){
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
    public function update($request) : array
    {
        if(!($this->validationServices->validate($request,$this->employee->rulesForUpdation($request['id'])))){
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
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $res = $this->authServices->delete(['reference_id' => $request['id']]);
        if(!$res){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $employee = $this->employee->find($request['id']);
        if(is_null($employee)){
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
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->employee->with('branches')->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->employee->orderBy('employee.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $employee = $this->employee->with('branches')->find($request['id']);
        if(is_null($employee)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        if($request['status'] == 1){
            if(is_null($employee['branches']) || ($employee['branches']['status'] == 0)){
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
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->employee->join('branch', 'branch.id', '=', 'employee.branch_id')
        ->join('users', 'employee.id', '=', 'users.reference_id')
        ->join('user_role_type','users.id','=','user_role_type.user_id')
        ->join('roles','user_role_type.role_id','=','roles.id')
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
        })->select('employee.id','employee.employee_name','users.email','employee.position','branch.branch_name','roles.role_name','employee.salary','employee.status','employee.created_at')
        ->distinct()
        ->orderBy('employee.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @return mixed
     */
    public function dropdown() : mixed
    {
        return $this->employee->select('id','employee_name')->orderBy('employee.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatusBasedOnBranch($request) : array
    {
        $employee = $this->employee->where('branch_id', $request['branch_id'])
        ->update(['status' => $request['status']]);
        return  [
            "isUpdated" => $employee,
            "message" => "Updated Successfully"
        ];
    }
}

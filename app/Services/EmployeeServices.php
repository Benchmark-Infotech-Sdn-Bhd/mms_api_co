<?php

namespace App\Services;

use App\Models\Employee;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class EmployeeServices
{
    private Employee $employee;
    private ValidationServices $validationServices;
    /**
     * EmployeeServices constructor.
     * @param Employee $employee
     * @param ValidationServices $validationServices
     */
    public function __construct(Employee $employee,ValidationServices $validationServices)
    {
        $this->employee = $employee;
        $this->validationServices = $validationServices;
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
        return $this->employee->create([
            'employee_name' => $request['employee_name'] ?? '',
            'gender' => $request['gender'] ?? '',
            'date_of_birth' => $request['date_of_birth'] ?? '',
            'ic_number' => $request['ic_number'] ?? '',
            'passport_number' => $request['passport_number'] ?? '',
            'email' => $request['email'] ?? '',
            'address' => $request['address'] ?? '',
            'postcode' => $request['postcode'] ?? '',
            'position' => $request['position'] ?? '',
            'branch_id' => $request['branch_id'],
            'role_id' => $request['role_id'],
            'salary' => $request['salary'] ?? 0,
            'status' => $request['status'] ?? 1,
            'city' => $request['city'] ?? '',
            'state' => $request['state'] ?? '',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
    }
    /**
     * @param $request
     * @return array
     */
    public function update($request) : array
    {
        if(!($this->validationServices->validate($request,$this->employee->rulesForUpdation))){
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
        return  [
            "isUpdated" => $employee->update([
                'id' => $request['id'],
                'employee_name' => $request['employee_name'] ?? $employee['employee_name'],
                'gender' => $request['gender'] ?? $employee['gender'],
                'date_of_birth' => $request['date_of_birth'] ?? $employee['date_of_birth'],
                'ic_number' => $request['ic_number'] ?? $employee['ic_number'],
                'passport_number' => $request['passport_number'] ?? $employee['passport_number'],
                'email' => $request['email'] ?? $employee['email'],
                'address' => $request['address'] ?? $employee['address'],
                'postcode' => $request['postcode'] ?? $employee['postcode'],
                'position' => $request['position'] ?? $employee['position'],
                'branch_id' => $request['branch_id'] ?? $employee['branch_id'],
                'role_id' => $request['role_id'] ?? $employee['role_id'],
                'salary' => $request['salary'] ?? $employee['salary'],
                'status' => $request['status'] ?? $employee['status'],
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
        return $this->employee->findOrFail($request['id']);
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
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required']))){
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
            if(!($this->validationServices->validate($request,['search_param' => 'required|regex:/^[a-zA-Z ]*$/|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->employee->join('branch', 'branch.id', '=', 'employee.branch_id')
        ->join('roles', 'roles.id', '=', 'employee.role_id')
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('employee.employee_name', 'like', "%{$request['search_param']}%")
                ->orWhere('employee.ic_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('employee.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('employee.email', 'like', '%'.$request['search_param'].'%');
            }
            if (isset($request['status'])) {
                $query->where('employee.status',$request['status']);
            }
            if (isset($request['branch_id'])) {
                $query->where('employee.branch_id',$request['branch_id']);
            }
            if (isset($request['role_id'])) {
                $query->where('employee.role_id',$request['role_id']);
            }
        })->select('employee.id','employee.employee_name','employee.email','employee.position','branch.branch_name','roles.role_name','employee.salary','employee.status')
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
}

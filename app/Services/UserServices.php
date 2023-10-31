<?php

namespace App\Services;
use App\Models\User;
use App\Models\CRMProspect;
use App\Models\Employee;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserServices
{
	private User $user;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var Employee
     */
    private Employee $employee;
    /**
     * UserServices constructor.
     * @param User $user
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     */
    public function __construct(User $user,ValidationServices $validationServices,
    AuthServices $authServices,CRMProspect $crmProspect, Employee $employee)
    {
        $this->user = $user;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->crmProspect = $crmProspect;
        $this->employee = $employee;
    }

    /**
     * @return array
     */
    public function resetPasswordValidation(): array
    {
        return [
            'id' => 'required',
            'new_password' => 'required',
            'current_password' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateEmployeeValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'address' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateCustomerValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'address' => 'required'
        ];
    }
     /**
     * @return array
     */
    public function updateAdminValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function adminList($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return $this->user
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('users.name', 'like', "%{$request['search_param']}%")
                ->orWhere('users.email', 'like', '%'.$request['search_param'].'%');
            }
        })
        ->where('users.user_type', Config::get('services.ROLE_TYPE_ADMIN'))
        ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.user_type', 'users.status')
        ->distinct()
        ->orderBy('users.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
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
     * @return array
     */
    public function adminUpdateStatus($request) : array
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = $this->user->find($request['id']);
        if(is_null($user)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $user->status = $request['status'];
        return  [
            "isUpdated" => $user->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function resetPassword($request): array|bool
    {
    	$validator = Validator::make($request, $this->resetPasswordValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = $this->user->findOrFail($request['id']);
        if(Hash::check($request['current_password'], $user->password)) {
            $user->password = Hash::make($request['new_password']);
            $user->modified_by = $request['modified_by'];
            $user->save();
            return true;
        } else {
            return [
                'currentPasswordError' => true
            ];
        }
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function updateUser($request): array|bool
    {
        $userDetails = $this->user->findOrFail($request['id']);
        if($userDetails->user_type == 'Employee') {
            $validator = Validator::make($request, $this->updateEmployeeValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            $employeeDetails = $this->employee->findOrFail($userDetails->reference_id);
            $employeeDetails->employee_name = $request['name'] ?? $employeeDetails->employee_name;
            $employeeDetails->contact_number = $request['contact_number'] ?? $employeeDetails->contact_number;
            $employeeDetails->address = $request['address'] ?? $employeeDetails->address;
            $employeeDetails->state = $request['state'] ?? $employeeDetails->state;
            $employeeDetails->city = $request['city'] ?? $employeeDetails->city;
            $employeeDetails->modified_by = $request['modified_by'];
            $employeeDetails->save();
        } else if($userDetails->user_type == 'Customer') {
            $validator = Validator::make($request, $this->updateCustomerValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            $customerDetails = $this->crmProspect->findOrFail($userDetails->reference_id);
            $customerDetails->pic_name = $request['name'] ?? $customerDetails->pic_name;
            $customerDetails->pic_contact_number = $request['contact_number'] ?? $customerDetails->pic_contact_number;
            $customerDetails->address = $request['address'] ?? $customerDetails->address;
            $customerDetails->modified_by = $request['modified_by'];
            $customerDetails->save();
        } else if($userDetails->user_type == 'Admin') {
            $validator = Validator::make($request, $this->updateAdminValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            $userDetails->name = $request['name'] ?? $userDetails->name;
            $userDetails->modified_by = $request['modified_by'];
            $userDetails->save();
        }
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function showUser($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $userDetails = $this->user->findOrFail($request['id']);
        if($userDetails->user_type == 'Admin' || $userDetails->user_type == 'Super Admin') {
            return $userDetails;
        } else if($userDetails->user_type == 'Employee') {
            return $this->user->with(['employee' => function ($employeeQuery) {
                $employeeQuery->select('id', 'employee_name', 'gender', 'contact_number', 'address', 'state', 'city', 'branch_id');
            }, 'employee.branches' => function ($branchQuery) {
                $branchQuery->select('id', 'branch_name');
            }])->findOrFail($request['id']);
        } else if($userDetails->user_type == 'Customer') {
            return $this->user->with(['customer' => function ($customerQuery) {
                $customerQuery->select('id', 'pic_name', 'pic_contact_number', 'address');
            }])->findOrFail($request['id']);
        }
    }
}

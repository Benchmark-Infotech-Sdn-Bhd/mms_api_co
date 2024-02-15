<?php

namespace App\Services;
use App\Models\User;
use App\Models\CRMProspect;
use App\Models\Employee;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserServices
{
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';

    public const ERROR_INVALID_USER = ['InvalidUser' => true];
    public const ERROR_CURRENT_PASSWORD = ['currentPasswordError' => true];

    public const USER_TYPE_EMPLOYEE = 'Employee';
    public const USER_TYPE_CUSTOMER = 'Customer';
    public const USER_TYPE_ADMIN = 'Admin';
    public const USER_TYPE_SUPER_ADMIN = 'Super Admin';

    /**
     * @var User
     */
	private User $user;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var AuthServices
     */
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
     *
     * @param User $user Instance of the User class
     * @param ValidationServices $validationServices Instance of the ValidationServices class
     * @param AuthServices $authServices Instance of the AuthServices class
     *
     * @return void
     */
    public function __construct(
        User                $user,
        ValidationServices  $validationServices,
        AuthServices        $authServices,
        CRMProspect         $crmProspect,
        Employee            $employee
    )
    {
        $this->user = $user;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->crmProspect = $crmProspect;
        $this->employee = $employee;
    }

    /**
     *  validate the reset password request data
     *
     * @return array  The validation rules for the input data.
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
     *  validate the update employee request data
     *
     * @return array  The validation rules for the input data.
     */
    public function updateEmployeeValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11',
            'city' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'address' => 'required'
        ];
    }
    /**
     *  validate the update customer request data
     *
     * @return array  The validation rules for the input data.
     */
    public function updateCustomerValidation(): array
    {
        return [
            'id' => 'required',
            'contact_number' => 'required|regex:/^[0-9]+$/|max:11'
        ];
    }
     /**
      *  validate the update admin request data

     * @return array  The validation rules for the input data.
     */
    public function updateAdminValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/'
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAdminListRequest($request): array|bool
    {
        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAdminShowRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAdminUpdateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','name' => 'required','email' => 'required|email|max:150|unique:users,email,'.$request['id'].',id,deleted_at,NULL']))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAdminUpdateStatusRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateResetPasswordRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->resetPasswordValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateEmployeeRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateEmployeeValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }

        return true;
    }

    /**
     * Retrieve the user record based on requested data.
     *
     *
     * @param array $request
     *              company_id (array) ID of the user company
     *              id (int) ID of the event
     *
     * @return mixed Returns the user data

     */
    private function getUser($request)
    {
        return $this->user->where('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateCustomerRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateCustomerValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateAdminRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateAdminValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }

        return true;
    }

    /**
     * List the admin user
     *
     * @param $request The request data containing the search_param
     *
     * @return mixed Returns the paginated list of admin user.
     *
     */
    public function adminList($request) : mixed
    {
        $validationResult = $this->validateAdminListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->user
            ->leftJoin('company', 'company.id', 'users.company_id')
            ->where(function ($query) use ($request) {
                if (!empty($request['search_param'])) {
                    $query->where('users.name', 'like', "%{$request['search_param']}%")
                    ->orWhere('users.email', 'like', '%'.$request['search_param'].'%');
                }
            })
            ->where('users.user_type', Config::get('services.ROLE_TYPE_ADMIN'))
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'users.user_type', 'users.status', 'company.id as company_id', 'company.company_name')
            ->distinct()
            ->orderBy('users.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the Admin User Detail
     *
     * @param $request The request data containing the id
     *
     * @return mixed Returns the admin user record.
     */
    public function adminShow($request) : mixed
    {
    	$validationResult = $this->validateAdminShowRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->user->with(['company' => function ($query) {
                $query->select('id', 'company_name');
            }])->find($request['id']);
    }

    /**
     * Update the admin user
     *
     * @param $request The request data containing the admin user update data
     *
     * @return array  Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function adminUpdate($request)
    {
    	$validationResult = $this->validateAdminUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = $this->user->where('id',$request['id'])->first();

        $user->update([
            'name' => $request['name'] ?? $user['name'],
            'email' => $request['email'] ?? $user['email'],
            'modified_by' => $request['user_id'] ?? $user['modified_by']
        ]);

        return  [
            "isUpdated" => true,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * Update admin user status
     *
     * @param $request The request data containing the id and status
     *
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function adminUpdateStatus($request) : array
    {
        $validationResult = $this->validateAdminUpdateStatusRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = $this->user->find($request['id']);

        if(is_null($user)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $user->status = $request['status'];
        return  [
            "isUpdated" => $user->save() == 1,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
    /**
     * Reset Password
     *
     * @param $request The request data containing the reset password data
     *
     * @return array|bool  returns An array of validation errors or boolean based on the processing result
     */
    public function resetPassword($request): array|bool
    {
    	$validationResult = $this->validateResetPasswordRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = $this->getUser($request);

        if (is_null($user)) {
            return self::ERROR_INVALID_USER;
        }

        if (Hash::check($request['current_password'], $user->password)) {
            $user->password = Hash::make($request['new_password']);
            $user->modified_by = $request['modified_by'];
            $user->save();
            return true;
        } else {
            return self::ERROR_CURRENT_PASSWORD;
        }
    }

    /**
     * Update user
     *
     * @param $request  The request data containing the update user data
     *
     * @return array|bool returns An array of validation errors or boolean based on the processing result
     */
    public function updateUser($request): array|bool
    {
        $userDetails = $this->getUser($request);
        if(is_null($userDetails)) {
            return self::ERROR_INVALID_USER;
        }
        if($userDetails->user_type == self::USER_TYPE_EMPLOYEE) {
            $validationResult = $this->validateUpdateEmployeeRequest($request);
            if (is_array($validationResult)) {
                return $validationResult;
            }
            $this->updateEmployeeData($userDetails, $request);
        } else if($userDetails->user_type == self::USER_TYPE_CUSTOMER) {
            $validationResult = $this->validateUpdateCustomerRequest($request);
            if (is_array($validationResult)) {
                return $validationResult;
            }
            $this->updateCustomerData($userDetails, $request);
        } else {
            $validationResult = $this->validateUpdateAdminRequest($request);
            if (is_array($validationResult)) {
                return $validationResult;
            }
        }
        $userDetails->name = $request['name'] ?? $userDetails->name;
        $userDetails->modified_by = $request['modified_by'];
        $userDetails->save();
        return true;
    }

    /**
     * update Employee record based on request data.
     *
     * @param object $userDetails user object
     * @param array $request The request containing the name, contact_number, address, state, city, modified_by
     *
     * @return void
     *
     */
    private function updateEmployeeData($userDetails, $request)
    {
        $employeeDetails = $this->employee->findOrFail($userDetails->reference_id);
        $employeeDetails->employee_name = $request['name'] ?? $employeeDetails->employee_name;
        $employeeDetails->contact_number = $request['contact_number'] ?? $employeeDetails->contact_number;
        $employeeDetails->address = $request['address'] ?? $employeeDetails->address;
        $employeeDetails->state = $request['state'] ?? $employeeDetails->state;
        $employeeDetails->city = $request['city'] ?? $employeeDetails->city;
        $employeeDetails->modified_by = $request['modified_by'];
        $employeeDetails->save();
    }

    /**
     * update customer record based on request data.
     *
     * @param object $userDetails user object
     * @param array $request The request containing the contact_number, modified_by
     *
     * @return void
     *
     */
    private function updateCustomerData($userDetails, $request)
    {
        $customerDetails = $this->crmProspect->findOrFail($userDetails->reference_id);
        $customerDetails->pic_contact_number = $request['contact_number'] ?? $customerDetails->pic_contact_number;
        $customerDetails->modified_by = $request['modified_by'];
        $customerDetails->save();
    }

    /**
     * Show the user data based on the user type
     *
     * @param $request The request data containing the id
     *
     * @return mixed Returns the user record
     *
     */
    public function showUser($request) : mixed
    {
        $validationResult = $this->validateAdminShowRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $userDetails = $this->user->findOrFail($request['id']);

        if($userDetails->user_type == self::USER_TYPE_ADMIN || $userDetails->user_type == self::USER_TYPE_SUPER_ADMIN) {
            return $userDetails;
        } else if($userDetails->user_type == self::USER_TYPE_EMPLOYEE) {
            return $this->user->with(['employee' => function ($employeeQuery) {
                $employeeQuery->select('id', 'employee_name', 'gender', 'contact_number', 'address', 'state', 'city', 'branch_id');
            }, 'employee.branches' => function ($branchQuery) {
                $branchQuery->select('id', 'branch_name');
            }])->findOrFail($request['id']);
        } else {
            return $this->user->with(['customer' => function ($customerQuery) {
                $customerQuery->select('id', 'pic_name', 'pic_contact_number', 'address');
            }])->findOrFail($request['id']);
        }
    }
}

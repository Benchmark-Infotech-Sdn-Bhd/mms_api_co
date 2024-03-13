<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRoleType;
use App\Models\Company;
use App\Models\UserCompany;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\EmailServices;
use Illuminate\Support\Str;
use App\Models\PasswordResets;
use Illuminate\Support\Facades\Config;

class AuthServices extends Controller
{
    private EmailServices $emailServices;
    private User $user;
    private UserRoleType $userRoleType;
    private PasswordResets $passwordResets;
    private Company $company;
    private UserCompany $userCompany;
    private Role $role;

    /**
     * Constructor method for the class.
     *
     * @param User $user An instance of the User class.
     * @param UserRoleType $userRoleType
     * @param EmailServices $emailServices An instance of the EmailServices class.
     * @param PasswordResets $passwordResets An instance of the PasswordResets class.
     * @param Company $company An instance of the Company class.
     * @param UserCompany $userCompany An instance of the UserCompany class.
     * @param Role $role An instance of the Role class.
     */
    public function __construct(User $user, UserRoleType $userRoleType, EmailServices $emailServices, PasswordResets $passwordResets, Company $company, UserCompany $userCompany, Role $role)
    {
        $this->user = $user;
        $this->userRoleType = $userRoleType;
        $this->emailServices = $emailServices;
        $this->passwordResets = $passwordResets;
        $this->company = $company;
        $this->userCompany = $userCompany;
        $this->role = $role;
    }

    /**
     * Validates the login credentials.
     *
     * @return array The validation rules for the login form.
     */
    public function loginValidation(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    /**
     * @return array
     */
    public function registerValidation(): array
    {
        return [
            'user_type' => 'required',
            'name' => 'required',
            'email' => 'required|email|max:150|unique:users,email,NULL,id,deleted_at,NULL'
        ];
    }

    /**
     * Retrieves the validation rules for the forgot password functionality.
     *
     * These rules specify that the email field is required and must be a valid email address.
     *
     * @return array An array containing the validation rules for the email field.
     */
    public function forgotPasswordValidation(): array
    {
        return [
            'email' => 'required|email'
        ];
    }

    /**
     * Retrieves the validation rules for updating the forgot password functionality.
     *
     * These rules specify that the email field is required and must be a valid email address,
     * the password field is required, and the token field is required.
     *
     * @return array An array containing the validation rules for the email, password, and token fields.
     */
    public function forgotPasswordUpdateValidation(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'token' => 'required'
        ];
    }

    /**
     * Creates a new user based on the provided request data.
     *
     * This method creates a new user with the given data and performs additional operations based on the user type:
     * - If the user type is "Admin" or "Super User", a random password is generated and assigned to the user.
     * - The user is created in the database using the User model.
     * - If the user type is not "Admin", additional operations are performed:
     *   - The user role type is created using the UserRoleType model, with the provided role ID.
     *   - If the role details have special permission and subsidiary companies are provided,
     *     the user role type is created for each subsidiary company.
     *   - The user is associated with the provided subsidiary companies using the UserCompany model.
     * - If the "pic_flag" is set to 1, the pic_name and role fields are updated in the company with the provided company ID.
     * - Finally, a registration email is sent to the user using the EmailServices class.
     *
     * @param array $request The request data containing the user details.
     *     - $request['name']: The name of the user.
     *     - $request['email']: The email address of the user.
     *     - $request['password']: The password of the user. Required only for "Admin" and "Super User" user types.
     *     - $request['user_id']: The ID of the user creating the new user. Optional.
     *     - $request['user_type']: The type of the user.
     *     - $request['reference_id']: The reference ID of the user. Optional.
     *     - $request['company_id']: The ID of the company associated with the user. Optional.
     *     - $request['pic_flag']: The flag indicating whether the user is a PIC. Optional.
     *     - $request['role_id']: The ID of the role assigned to the user.
     *     - $request['status']: The status of the user. Optional.
     *     - $request['subsidiary_companies']: An array of subsidiary companies associated with the user. Optional.
     *
     * @return bool Returns true if the user was successfully created.
     */
    public function create($request)
    {
        $request = $this->processPassword($request);
        $user = $this->createUser($request);
        if ($request['user_type'] != 'Admin') {
            $this->assignRoles($request, $user);
        }
        if (isset($request['pic_flag']) && $request['pic_flag'] == 1) {
            $this->updateCompany($request);
        }
        $this->sendRegistrationEmail([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password']
        ]);
        return true;
    }

    /**
     * Processes the password based on user type.
     *
     * If the user type is 'Admin' or 'Super User', a new password is generated using the Str::random method.
     * Otherwise, the original password is returned unchanged.
     *
     * @param array $request The request data containing the user type and password.
     *
     * @return array The modified request data with the updated password (if applicable).
     */
    private function processPassword(array $request): array
    {
        if ($request['user_type'] == 'Admin' || $request['user_type'] == 'Super User') {
            $request['password'] = Str::random(8);
        }
        // if (DB::getDriverName() === 'sqlite') {
        //     $request['password'] = 'Welcome@123';
        // }
        return $request;
    }

    /**
     * Creates a new user based on the provided request data.
     *
     * Creates a new user record in the database with the provided request data. The required fields
     * include 'name', 'email', and 'password'. The 'created_by' and 'modified_by' fields will be set to
     * the provided 'user_id' value. The 'user_type', 'reference_id', 'company_id', and 'pic_flag' fields
     * are optional and have default values if not provided.
     *
     * @param array $request An array containing the request data for creating a new user.
     * @return User The newly created User instance.
     */
    private function createUser(array $request): User
    {
        return $this->user->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'created_by' => $request['user_id'],
            'modified_by' => $request['user_id'],
            'user_type' => $request['user_type'] ?? '',
            'reference_id' => $request['reference_id'] ?? 0,
            'company_id' => $request['company_id'] ?? 0,
            'pic_flag' => isset($request['pic_flag']) ?? 0
        ]);
    }

    /**
     * Assigns roles to a user based on the given request data.
     *
     * This method creates user role types and associates them with the user.
     * It also handles special permissions and subsidiary companies.
     *
     * @param array $request An array containing the request data.
     * @param User $user The User object representing the user.
     * @return void
     */
    private function assignRoles(array $request, User $user): void
    {
        $this->userRoleType->create($this->prepareRoleData($request, $user));

        $roleDetails = $this->role->find($request['role_id']);
        if ($roleDetails->special_permission == 1 && count($request['subsidiary_companies']) > 0) {
            foreach ($request['subsidiary_companies'] as $subsidiaryCompany) {
                $subRole = $this->role->where('parent_role_id', $request['role_id'])
                    ->where('company_id', $subsidiaryCompany)
                    ->first(['id']);
                $this->userRoleType->create($this->prepareRoleData($request, $user, $subRole));
            }
        }
        $request['subsidiary_companies'][] = $request['company_id'];
        foreach ($request['subsidiary_companies'] as $subsidiaryCompany) {
            $this->userCompany->create([
                'user_id' => $user->id,
                'company_id' => $subsidiaryCompany ?? 0,
                'created_by' => $request['user_id'] ?? 0,
                'modified_by' => $request['user_id'] ?? 0
            ]);
        }
    }

    /**
     * Prepares the role data for storage.
     *
     * This method takes in an array of request data, a user object, and an optional role object.
     * It returns an array containing the prepared role data for storage.
     *
     * @param array $request The request data containing role information.
     * @param User $user The user object associated with the role.
     * @param mixed|null $role The role object to be associated with the user (optional).
     * @return array The prepared role data for storage, including user ID, role ID, status,
     *              creation and modification information.
     */
    private function prepareRoleData(array $request, User $user, $role = null): array
    {
        return [
            'user_id' => $user->id,
            'role_id' => $role ? $role->id : $request['role_id'],
            'status' => $request['status'] ?? 1,
            'created_by' => $request['user_id'] ?? 0,
            'modified_by' => $request['user_id'] ?? 0
        ];
    }

    /**
     * Updates a company with the provided request data.
     *
     * @param array $request The request data containing the company's id, name, and user type.
     * @return void
     */
    private function updateCompany(array $request): void
    {
        $this->company->where('id', $request['company_id'])
            ->update([
                'pic_name' => $request['name'] ?? '',
                'role' => $request['user_type'] ?? ''
            ]);
    }

    /**
     * Sends a registration email to the user using the provided data.
     *
     * @param array $data The data containing the user's name, email, and password.
     * @return void
     */
    private function sendRegistrationEmail(array $data): void
    {
        $this->emailServices->sendRegistrationMail($data['name'], $data['email'], $data['password']);
    }

    /**
     * Updates a user's information and role based on the provided request data.
     *
     * This method finds a user based on the provided reference ID and updates the user's information and role based on the provided request data.
     * If the user or the user role is not found, the method returns false.
     *
     * @param array $request The request data containing the reference ID and the updated user information.
     * @return bool True if the user's information and role were successfully updated, false otherwise.
     */
    public function update($request)
    {
        $user = $this->findUserByReferenceId($request['reference_id']);
        if (is_null($user)) {
            return false;
        }

        $userRole = $this->findUserRoleByUserId($user['id']);
        if (is_null($userRole)) {
            return false;
        }

        $this->updateUser($user, $request);
        $this->updateUserRole($userRole, $request);

        return true;
    }

    /**
     * Finds a user by their reference ID.
     *
     * This method searches for a user in the database based on their reference ID and returns the first matching record.
     *
     * @param int|string $referenceId The reference ID of the user to search for.
     *
     * @return User|null The User model instance representing the found user, or null if no user is found.
     */
    private function findUserByReferenceId($referenceId)
    {
        return $this->user->where('reference_id', $referenceId)->first();
    }

    /**
     * Retrieves the user role by the given user ID.
     *
     * This method queries the user role type model to find the user role associated with the specified user ID.
     *
     * @param int $userId The ID of the user.
     * @return \Illuminate\Database\Eloquent\Model|null The user role type model instance or null if no user role is found.
     */
    private function findUserRoleByUserId($userId)
    {
        return $this->userRoleType->where('user_id', $userId)->first();
    }

    /**
     * Updates the user's details based on the given request data.
     *
     * This method takes a user object and a request object as parameters and updates the user's name,
     * email, and modified_by fields based on the request data. If any of the request data is not provided,
     * the corresponding user field will remain unchanged.
     *
     * @param User $user The user object to be updated.
     * @param array $request The request data containing the updated user details.
     * @return void
     */
    private function updateUser($user, $request)
    {
        $user->update([
            'name' => $request['name'] ?? $user['name'],
            'email' => $request['email'] ?? $user['email'],
            'modified_by' => $request['user_id'] ?? $user['modified_by']
        ]);
    }

    /**
     * Updates the role of a user.
     *
     * This method updates the role of a user based on the provided userRole object and request data.
     *
     * @param $userRole - The userRole object representing the user's current role.
     * @param array $request An array containing the request data, including the new role_id and modified_by fields.
     * @return void
     */
    private function updateUserRole($userRole, $request)
    {
        $userRole->update([
            'role_id' => $request['role_id'] ?? $userRole['role_id'],
            'modified_by' => $request['user_id'] ?? $userRole['modified_by']
        ]);
    }

    /**
     * Deletes a user and its associated role type based on the given reference ID.
     *
     * Deletes a user and its associated role type from the database using the provided reference ID.
     * If the user or the user's role type does not exist, the method returns false.
     * Otherwise, the user and the user's role type are deleted from the database and the method returns true.
     *
     * @param array $request An array containing the request data, including the 'reference_id' that specifies the user to be deleted.
     * @return bool True if the user and the user's role type are successfully deleted, false otherwise.
     */
    public function delete($request)
    {
        $user = $this->user->where('reference_id', $request['reference_id'])->first();
        if (is_null($user)) {
            return false;
        }
        $userRoleType = $this->userRoleType->where('user_id', $user['id'])->first();
        if (is_null($userRoleType)) {
            return false;
        }
        $userRoleType->delete();
        $user->delete();
        return true;
    }

    /**
     * Retrieves a user based on the provided request.
     *
     * If the user's ID is found and they are not a "Super Admin" user, the user will be extended
     * with company and role information. Otherwise, the "system_color" and "logo_url" fields will be set to null.
     *
     * @param array $request The request object containing the user's ID.
     * @return mixed The user object with extended information or with null values for "system_color" and "logo_url".
     */
    public function show($request): mixed
    {
        $user = $this->user->find($request['id']);
        if ($user['id'] && $user['user_type'] != 'Super Admin') {
            $user = $this->userWithCompany($user);
            $user = $this->userWithRoles($user);
        } else {
            $user['system_color'] = null;
            $user['logo_url'] = null;
        }
        return $user;
    }

    /**
     * Retrieves the user with roles based on the given reference ID.
     *
     * This method queries the storage for a user with the given reference ID and user type "Employee".
     * If a user is found, it calls the userWithRoles method to retrieve the user with their assigned roles.
     *
     * @param array $request The request data containing the reference ID.
     *
     * @return mixed The user object with roles, or null if no user is found.
     */
    public function userWithRolesBasedOnReferenceId($request): mixed
    {
        $user = $this->user->where('reference_id', $request['id'])->where('user_type', 'Employee')->first();
        if ($user['id']) {
            $user = $this->userWithRoles($user);
        }
        return $user;
    }

    /**
     * Retrieves the user with their corresponding roles.
     *
     * This method retrieves the user's role from the `user_role_type` table,
     * joining it with the `roles` table using a inner join. It checks that the role is active (`status = 1`)
     * and belongs to the user's company. If a role is found, the `role_id` is assigned to the user,
     * otherwise, it assigns a null value to `role_id`. The modified user array is then returned.
     *
     * @param array $user An array containing the user's information, including `id` and `company_id`.
     * @return mixed The modified user array with the added `role_id` field. If no role is found, `role_id` will be null.
     */
    public function userWithRoles($user): mixed
    {
        $roles = $this->userRoleType->join('roles', function ($join) use ($user) {
            $join->on('roles.id', '=', 'user_role_type.role_id');
            $join->where('roles.status', '=', 1);
            $join->where('roles.company_id', $user['company_id']);
        })
            ->where('user_id', $user['id'])->get()->first();
        if (is_null($roles)) {
            $user['role_id'] = null;
        } else {
            $user['role_id'] = $roles['role_id'];
        }
        return $user;
    }

    /**
     * Retrieves the user details with the associated company information.
     *
     * This method takes a user array as input and returns a modified version of the user array
     * that includes the system color and company logo URL if available. If the company or its attachments
     * are not found, the system color and logo URL will be set to null in the returned user array.
     *
     * @param array $user An array of user details, including the company ID.
     * @return mixed The modified user array
     */
    public function userWithCompany($user): mixed
    {
        $company = $this->company->with(['attachments' => function ($query) {
            $query->select('file_id', 'file_url');
        }])->findOrFail($user['company_id']);
        if (is_null($company) || is_null($company['attachments'])) {
            $user['system_color'] = null;
            $user['logo_url'] = null;
        } else {
            $user['system_color'] = $company['system_color'];
            if (DB::getDriverName() !== 'sqlite') {
                $user['logo_url'] = $company['attachments']['file_url'];
            }
        }
        return $user;
    }

    /**
     * Sends a forgot password email to the user with the provided email address.
     *
     * This method retrieves the user information based on the given email address from the "users" table,
     * generates a random token for password reset using the Hash::make method, and saves it in the "password_resets" table.
     * It then sends an email to the user containing the reset token and a link to the password reset page.
     *
     * @param array $request An array containing the request data, including the email address of the user.
     *
     * @return bool Returns true if the email is sent successfully, otherwise false.
     */
    public function forgotPassword($request)
    {
        $data = $this->user->where('email', $request['email'])->first(['id', 'name']);
        if (isset($data->id)) {
            $token = Hash::make(rand(100000, 999999));
            $this->passwordResets->create([
                'email' => $request['email'],
                'token' => $token
            ]);
            $params = [
                'name' => $data->name,
                'email' => $request['email'],
                'token' => $token,
                'url' => Config::get('services.FRONTEND_URL')
            ];
            $this->emailServices->sendForgotPasswordMail($params);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the user's password based on the provided request data.
     *
     * This method checks if the provided email and token match the stored data in the password resets table.
     * If there is a match, it updates the user's password with a hashed version of the new password.
     * It also deletes the entry from the password resets table.
     *
     * @param array $request The request data containing the email, token, and password fields.
     * @return bool True if the password was successfully updated, otherwise false.
     */
    public function forgotPasswordUpdate($request)
    {
        $data = $this->passwordResets->where('token', $request['token'])->first(['id', 'email', 'token']);
        if (isset($data['email']) && $data['email'] == $request['email']) {
            $this->user->where('email', $request['email'])->update([
                'password' => Hash::make($request['password'])
            ]);
            $this->passwordResets->where('token', $data['token'])->delete();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves the company IDs associated with a user.
     *
     * This method takes a user object as a parameter and returns an array of company IDs.
     * If the user is a super user and their company has no parent company, the method retrieves
     * the IDs of all child companies and adds them to the array. Otherwise, it adds the company ID
     * of the user to the array.
     *
     * @param $user User object representing the user.
     * @return array An array containing the company IDs.
     */
    public function getCompanyIds($user): array
    {
        //$companyDetails = $this->company->findOrFail($user['company_id']);
        //$companyIds = [];
        // if($companyDetails->parent_id == 0 && $user->user_type == 'Super User') {
        //     $companyIds = $this->company->where('parent_id', $user['company_id'])
        //                     ->select('id')
        //                     ->get()
        //                     ->toArray();
        //     $companyIds = array_column($companyIds, 'id');
        // }
        $companyIds[] = $user['company_id'];
        return $companyIds;
    }
}

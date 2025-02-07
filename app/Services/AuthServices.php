<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRoleType;
use App\Models\Company;
use App\Models\UserCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\EmailServices;
use Illuminate\Support\Str;
use App\Models\PasswordResets;
use Illuminate\Support\Facades\Config;

class AuthServices extends Controller
{
    private EmailServices $emailServices;
    /**
     * @var User
     */
    private $user;
    /**
     * @var UserRoleType
     */
    private $userRoleType;
    /**
     * @var PasswordResets
     */
    private $passwordResets;
    /**
     * @var Company
     */
    private $company;
    /**
     * @var UserCompany
     */
    private $userCompany;
    /**
     * AuthServices constructor.
     * @param User $user
     * @param UserRoleType $uesrRoleType
     * @param EmailServices $emailServices
     * @param PasswordResets $passwordResets
     * @param Company $company
     * @param UserCompany $userCompany
     */
    public function __construct(User $user, UserRoleType $uesrRoleType, EmailServices $emailServices, PasswordResets $passwordResets, Company $company, UserCompany $userCompany)
    {
        $this->user = $user;
        $this->uesrRoleType = $uesrRoleType;
        $this->emailServices = $emailServices;
        $this->passwordResets = $passwordResets;
        $this->company = $company;
        $this->userCompany = $userCompany;
    }

    /**
     * @return array
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
     * @return array
     */
    public function forgotPasswordValidation(): array
    {
        return [
            'email' => 'required|email'
        ];
    }

    /**
     * @return array
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
     * @param $request
     * @return bool
     */
    public function create($request)
    {
        // if($request['user_type'] == 'Super User') {
        //     $company = $this->company->findOrFail($request['company_id']);
        //     if($company['parent_id'] != 0) {
        //         return [
        //             'subsidiaryError' => true
        //         ];
        //     }
        //     $companyCount = $this->company->where('parent_id', $request['company_id'])->count();
        //     if($companyCount == 0) {
        //         return [
        //             'parentError' => true
        //         ];
        //     }
        //     $userCount = $this->user->where('company_id', $request['company_id'])
        //                 ->where('user_type', 'Super User')
        //                 ->count();
        //     if($userCount > 0) {
        //         return [
        //             'userError' => true
        //         ];
        //     }
        // }
        if($request['user_type'] == 'Admin' || $request['user_type'] == 'Super User') {
            $request['password'] = Str::random(8);
        }
        $response = $this->user->create([
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
        if($request['user_type'] != 'Admin' && $request['user_type'] != 'Customer') {
            $this->uesrRoleType->create([
                'user_id' => $response->id,
                'role_id' => $request['role_id'],
                'status'  => $request['status'] ?? 1,
                'created_by' => $request['user_id'] ?? 0,
                'modified_by' => $request['user_id'] ?? 0
            ]);
            array_push($request['subsidiary_companies'], $request['company_id']);
            foreach ($request['subsidiary_companies'] as $subsidiaryCompany) {
                $this->userCompany->create([
                    'user_id' => $response->id,
                    'company_id' => $subsidiaryCompany ?? 0,
                    'role_id' => $request['role_id'] ?? '',
                    'created_by' => $request['user_id'] ?? 0,
                    'modified_by' => $request['user_id'] ?? 0
                ]);
            }
        }
        if(isset($request['pic_flag']) && $request['pic_flag'] == 1) {
            $this->company->where('id', $request['company_id'])
                ->update([
                    'pic_name' => $request['name'] ?? '',
                    'role' => $request['user_type'] ?? ''
                ]);
        }
        $name = $request['name'];
        $email = $request['email'];
        $password = $request['password'];
        $this->emailServices->sendRegistrationMail($name,$email,$password); 
        return true;
    }
    /**
     * @param $request
     * @return bool
     */
    public function update($request)
    {
        $user = $this->user->where('reference_id',$request['reference_id'])->first();
        if(is_null($user)){
            return false;
        }
        $userRole = $this->uesrRoleType->where('user_id',$user['id'])->first();
        if(is_null($userRole)){
            return false;
        }
        $user->update([
            'name' => $request['name'] ?? $user['name'],
            'email' => $request['email'] ?? $user['email'],
            'modified_by' => $request['user_id'] ?? $user['modified_by']
        ]);
        $userRole->update([
            'role_id' => $request['role_id'] ?? $userRole['role_id'],
            'modified_by' => $request['user_id'] ?? $userRole['modified_by']
        ]);
        return true;
    }
    /**
     * @param $request
     * @return bool
     */
    public function delete($request)
    {
        $user = $this->user->where('reference_id',$request['reference_id'])->first();
        if(is_null($user)){
            return false;
        }
        $uesrRoleType = $this->uesrRoleType->where('user_id',$user['id'])->first();
        if(is_null($uesrRoleType)){
            return false;
        }
        $uesrRoleType->delete();
        $user->delete();
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        $user = $this->user->find($request['id']);
        if($user['id']){
            $user = $this->userWithRoles($user);
        }
        return $user;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function userWithRolesBasedOnReferenceId($request) : mixed
    {
        $user = $this->user->where('reference_id',$request['id'])->first();
        if($user['id']){
            $user = $this->userWithRoles($user);
        }
        return $user;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function userWithRoles($user) : mixed
    {
        $roles = $this->uesrRoleType->join('roles',function($join){
            $join->on('roles.id','=','user_role_type.role_id');
            $join->where('roles.status','=',1);
        })
        ->where('user_id',$user['id'])->get()->first();
        if(is_null($roles)){
            $user['role_id'] = null;
        } else {
            $user['role_id'] = $roles['role_id'];
        }
        return $user;
    }

    /**
     * @param $request
     * @return bool
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
     * @param $request
     * @return bool
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
     * @param $user
     * @return array
     */
    public function getCompanyIds($user): array
    {
        $companyDetails = $this->company->findOrFail($user['company_id']);
        $companyIds = [];
        // if($companyDetails->parent_id == 0 && $user->user_type == 'Super User') {
        //     $companyIds = $this->company->where('parent_id', $user['company_id'])
        //                     ->select('id')
        //                     ->get()
        //                     ->toArray();
        //     $companyIds = array_column($companyIds, 'id');
        // }
        array_push($companyIds, $user['company_id']);
        return $companyIds;
    }

    /**
     * @param $user
     * @return array
     */
    public function isCustomer($user): array
    {
        if($companyDetails->parent_id == 0 && $user->user_type == 'Customer') {
            return $customer = ' where created_by = '.$user->id;
        }
    }
}
<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRoleType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\EmailServices;
use Illuminate\Support\Str;

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
     * AuthServices constructor.
     * @param User $user
     * @param UserRoleType $uesrRoleType
     * @param EmailServices $emailServices
     */
    public function __construct(User $user, UserRoleType $uesrRoleType, EmailServices $emailServices)
    {
        $this->user = $user;
        $this->uesrRoleType = $uesrRoleType;
        $this->emailServices = $emailServices;
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
     * @param $request
     * @return bool
     */
    public function create($request)
    {
        if($request['user_type'] == 'Admin') {
            $request['password'] = Str::random(8);
        }
        $response = $this->user->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'created_by' => $request['user_id'],
            'modified_by' => $request['user_id'],
            'user_type' => $request['user_type'] ?? '',
            'reference_id' => $request['reference_id'] ?? 0
        ]);
        if($request['user_type'] != 'Admin') {
            $this->uesrRoleType->create([
                'user_id' => $response->id,
                'role_id' => $request['role_id'],
                'status'  => $request['status'] ?? 1,
                'created_by' => $request['user_id'] ?? 0,
                'modified_by' => $request['user_id'] ?? 0
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
}
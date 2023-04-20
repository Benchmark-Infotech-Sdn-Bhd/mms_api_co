<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRoleType;
use Illuminate\Support\Facades\Hash;

class AuthServices extends Controller
{
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
     */
    public function __construct(User $user, UserRoleType $uesrRoleType)
    {
        $this->user = $user;
        $this->uesrRoleType = $uesrRoleType;
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
            'name' => 'required',
            'email' => 'required|email|max:150|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required',
        ];
    }

    /**
     * @param $request
     * @return bool
     */
    public function create($request)
    {
        $response = $this->user->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'created_by' => $request['user_id'],
            'modified_by' => $request['user_id']
        ]);
        $this->uesrRoleType->create([
            'user_id' => $response->id,
            'role_id' => $request['role_id'],
            'status'  => $request['status'] ?? 1,
            'created_by' => $request['user_id'],
            'modified_by' => $request['user_id']
        ]);
        return true;
    }
}
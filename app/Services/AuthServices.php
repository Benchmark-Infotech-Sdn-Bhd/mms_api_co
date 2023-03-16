<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthServices extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * AuthServices constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required',
        ];
    }

    /**
     * @param $request
     * @return bool
     */
    public function create($request)
    {
        $this->user->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'created_by' => $request['user_id'],
            'modified_by' => $request['user_id']
        ]);
        return true;
    }
}
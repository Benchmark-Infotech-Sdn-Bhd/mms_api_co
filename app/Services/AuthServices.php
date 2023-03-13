<?php


namespace App\Services;

use App\Http\Controllers\Controller;
use App\Mail\AccountCreationMail;
use App\Mail\ForgotMail;
use App\Models\PasswordResets;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class AuthServices extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Request
     */
    private $request;

    /**
     * AuthServices constructor.
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
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
<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthServices;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var AuthServices
     */
    private $authServices;

    /**
     * AuthController constructor.
     * @param AuthServices $authServices
     */

    public function __construct(AuthServices $authServices) 
    {
        $this->authServices = $authServices;
    }

    /**
     * User Login
     *
     * [Get a JWT token via given credentials].
     * @bodyParam email string required The username of the user.
     * @bodyParam password string required The password of the user.
     * @responseFile responses/login.json
     * @responseFile 400 responses/login.400.json
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $this->getRequest($request);
        $validator = Validator::make($credentials, $this->authServices->loginValidation());
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        if (!$token = Auth::attempt($credentials)) {
            return $this->sendError(['message' => 'Invalid Credentials'], 400);
        }
        $user = Auth::user();     
        return $this->respondWithToken($token, $user);
    }

    /**
     * User Register
     *
     * [Register the users].
     * @urlParam BearerToken required The token to access the application.
     * @bodyParam customer_id integer required The customer Id for the user.
     * @bodyParam name string required The name of the user.
     * @bodyParam username string required The username of the user.
     * @bodyParam password string required The password of the user.
     * @responseFile responses/register.json
     * @responseFile 422 responses/register.422.json
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $request = $this->getRequest($request);
        $user = JWTAuth::parseToken()->authenticate();
        $request['user_id'] = $user->id;
        $validator = Validator::make($request, $this->authServices->registerValidation());
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        $this->authServices->create($request);
        return $this->sendSuccess(['message' => 'Successfully User was created']);
    }

    /**
     * User Details
     * [Get the authenticated User Details]
     * @urlParam BearerToken required The token to access the application.
     * @responseFile responses/userDetails.json
     * @responseFile 400 responses/userDetails.400.json
     * @return JsonResponse
     */
    public function user()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                $this->sendError(['message' => 'user not found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $this->sendError(['message' => 'token expired'], 404);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            $this->sendError(['message' => 'token invalid'], 404);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            $this->sendError(['message' => 'token absent'], 404);
        }
        return $this->sendSuccess(compact('user'));
    }

     /**
     * User Logout
     * [Log the user out (Invalidate the token)]
     * @urlParam BearerToken required The token to access the application.
     * @responseFile responses/userLogout.json
     * @responseFile 400 responses/userDetails.400.json
     * @return JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();
        return $this->sendSuccess(['message' => 'Successfully logged out']);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Refresh a token.
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }
}

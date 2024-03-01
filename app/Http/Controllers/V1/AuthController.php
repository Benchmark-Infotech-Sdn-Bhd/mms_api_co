<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthServices;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\EmployeeServices;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var EmployeeServices
     */
    private EmployeeServices $employeeServices;

    /**
     * Constructor
     *
     * @param AuthServices $authServices Instance of Auth Services class
     * @param EmployeeServices $employeeServices Instance of Employee Services class
     */

    public function __construct(AuthServices $authServices, EmployeeServices $employeeServices)
    {
        $this->authServices = $authServices;
        $this->employeeServices = $employeeServices;
    }

    /**
     * Login a user.
     *
     * @param Request $request The login request object.
     *
     * @return JsonResponse The JSON response.
     */
    public function login(Request $request)
    {
        $credentials = $this->getCredentials($request);

        $validator = $this->validateCredentials($credentials);
        if (!empty($validator)) {
            return $validator;
        }

        if (!$token = Auth::attempt($credentials)) {
            $isInvalidUser = $this->errorInvalidCredentials();
            if(!empty($isInvalidUser)) {
                return $isInvalidUser;
            }
        }

        $user = $this->getAuthenticatedUser();
        if(!empty($user['error'])) {
            return $this->sendError(['message' => 'User not found'], 400);
        }
        
        switch (Str::lower($user['user_type'])) {
            case 'employee':
                $this->validateEmployeeUser($user);
                break;
            case Config::get('services.ROLE_TYPE_ADMIN'):
                $this->validateAdminUser($user);
                break;
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get credentials
     *
     * Removes the 'domain_name' key from the request parameters and returns the modified credentials.
     *
     * @param Request $request The request object containing the credentials
     *
     * @return array The modified credentials without the 'domain_name' key
     */
    private function getCredentials(Request $request)
    {
        $credentials = $this->getRequest($request);
        unset($credentials['domain_name']);
        return $credentials;
    }

    /**
     * Validate user credentials
     * @param array $credentials The user credentials to be validated
     *
     * @return array|bool if validator fails return validator errors, otherwise true
     */
    private function validateCredentials(array $credentials)
    {
        $validator = Validator::make($credentials, $this->authServices->loginValidation());
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
    }

    /**
     * Generates an error response for invalid credentials.
     *
     * Sends a JSON response with the message "Invalid Credentials" and a status code of 400.
     *
     * @return void
     */
    private function errorInvalidCredentials()
    {
        return $this->sendError(['message' => 'Invalid Credentials'], 400);
    }

    /**
     * Retrieves the authenticated user.
     *
     * This method retrieves the authenticated user by calling the `Auth::user()` function.
     * If the user is null, it sends an error response.
     * Otherwise, it calls the `show` method of the `authServices` object to fetch the details of the user.
     *
     * @return array The details of the authenticated user. otherwise return array containing error
     */
    private function getAuthenticatedUser()
    {
        $user = Auth::user();
        if (is_null($user)) {
            return [
                "error" => true
            ];
        }
        return $this->authServices->show(['id' => $user['id']]);
    }

    /**
     * Validates an employee user.
     *
     * This method retrieves an employee using the reference ID from the user array, and then checks if the employee is null or inactive. If the employee is null or inactive, it throws an
     * error.
     *
     * @param object $user The user array to validate. The reference_id key must be present.
     *
     * @return void
     */
    private function validateEmployeeUser(object $user)
    {
        $employee = $this->employeeServices->show(['id' => $user['reference_id']]);
        if (is_null($employee) || $this->isEmployeeInactive($employee)) {
            $this->errorInactiveUser();
        }
    }

    /**
     * Checks if an employee is inactive.
     *
     * This method checks if the employee is considered inactive based on the following conditions:
     * - If the 'branches' key of the employee details is null
     * - If the employee status is equal to 0
     * - If the status of the employee's branches is equal to 0
     *
     * @param array &$employee The employee array to check. The 'employeeDetails' key must be present,
     *                        which should contain a 'branches' key and a 'status' key.
     *
     * @return bool Returns true if the employee is considered inactive, false otherwise.
     */
    private function isEmployeeInactive(array &$employee): bool
    {
        return is_null($employee['employeeDetails']['branches']) ||
            $employee['employeeDetails']['status'] == 0 ||
            $employee['employeeDetails']['branches']['status'] == 0;
    }

    /**
     * Validates an admin user.
     *
     * This method checks if the user status is equal to 0 and throws an error if it is inactive.
     *
     * @param array &$user The user array to validate. The status key must be present.
     *
     * @return void
     */
    private function validateAdminUser(array &$user)
    {
        if ($user['status'] == 0) {
            $this->errorInactiveUser();
        }
    }

    /**
     * Throws an error for an inactive user.
     *
     * This method sends an error response indicating that the user's login has been inactivated.
     *
     * @return void
     */
    private function errorInactiveUser()
    {
        $this->sendError(['message' => 'Your login has been inactivated, kindly contact Administrator'], 400);
    }

    /**
     * Registers a new user.
     *
     * This method handles the registration of a new user. It performs the following steps:
     * - Gets the request data using the getRequest method.
     * - Authenticates the user using JWTAuth::parseToken()->authenticate().
     * - Adds the user id to the request data.
     * - Validates the request data using the registerValidation method from the authServices object.
     * - If validation fails, returns a validation error response.
     * - Otherwise, creates the user using the create method from the authServices object.
     * - If the creation process encounters any errors related to subsidiaries, parent company, or existing super user,
     *   it returns the corresponding error response.
     * - Otherwise, returns a success response with a message indicating that the user was created successfully.
     *
     * @param Request $request The registration request.
     *
     * @return JsonResponse The registration response.
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
        // if(isset($response['subsidiaryError'])) {
        //     return $this->sendError(['message' => 'Cannot Create Super User for Subsidiary Company'], 422);
        // } else if(isset($response['parentError'])) {
        //     return $this->sendError(['message' => 'Parent Company only can Create Super User'], 422);
        // } else if(isset($response['userError'])) {
        //     return $this->sendError(['message' => 'This Company alredy has a Super User'], 422);
        // }
        return $this->sendSuccess(['message' => 'Successfully User was created']);
    }

    /**
     * Get the user associated with the authenticated token.
     *
     * This method fetches the user associated with the authenticated token.
     * It first attempts to parse the token and authenticate it using JWTAuth.
     *
     * If the token is successfully validated and the user is found, the method returns a success response
     * containing the user information. Otherwise, it throws an error using the sendError method.
     *
     * @return JsonResponse The user information in a success response.
     *               The structure of the response array:
     *                  - 'user': The user information.
     *                      The structure of the user array:
     *                          - 'id': The unique identifier of the user.
     *                          - 'name': The name of the user.
     *                          - 'email': The email address of the user.
     *                          - ...
     *
     * @throws TokenExpiredException If the token is expired.
     * @throws TokenInvalidException If the token is invalid.
     * @throws JWTException If the token is absent.
     */
    public function user()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                $this->sendError(['message' => 'user not found'], 404);
            }
        } catch (TokenExpiredException $e) {
            $this->handleException($e, 'token expired');
        } catch (TokenInvalidException $e) {
            $this->handleException($e, 'token invalid');
        } catch (JWTException $e) {
            $this->handleException($e, 'token absent');
        }

        return $this->sendSuccess(compact('user'));
    }

    /**
     * Handles an exception.
     *
     * This method logs information about the exception and sends an error response with the specified message and HTTP status code.
     *
     * @param Exception $e The exception to handle.
     * @param string $message The error message to send in the response.
     *
     * @return void
     */
    private function handleException(Exception $e, string $message): void
    {
        Log::info(get_class($e) . ' - ' . $e->getMessage());
        $this->sendError(['message' => $message], 404);
    }

    /**
     * Log out the user.
     *
     * This method logs out the currently authenticated user by calling the `logout` method of the `guard` instance.
     * After logging out, it sends a success response with a message indicating the successful logout.
     *
     * @return JsonResponse The success response, including a message indicating the successful logout.
     */
    public function logout()
    {
        $this->guard()->logout();
        return $this->sendSuccess(['message' => 'Successfully logged out']);
    }

    /**
     * Retrieves the authentication guard instance.
     *
     * This method returns the current authentication guard instance, which
     * provides the functionality for authentication and authorization.
     *
     * @return Guard
     */
    public function guard()
    {
        return Auth::guard();
    }

    /**
     * Refreshes the authentication token.
     *
     * This method retrieves the current user from the authentication service and refreshes the token using the guard. It returns the refreshed token along with the user information.
     *
     * @return JsonResponse The response containing the refreshed token and user information.
     */
    public function refresh()
    {
        $user = Auth::user();
        if (is_null($user)) {
            return $this->sendError(['message' => 'User not found'], 400);
        }
        $user = $this->authServices->show(['id' => $user['id']]);
        return $this->respondWithToken($this->guard()->refresh(), $user);
    }

    /**
     * Handles the forgot password request.
     *
     * This method validates the input credentials, sends a forgot password request using the authentication service,
     * and returns the appropriate response based on the response from the service.
     *
     * @param Request $request The request object received from the client.
     *
     * @return JsonResponse The JSON response containing the message and status code for successful or failed forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        $credentials = $this->getRequest($request);
        $validator = Validator::make($credentials, $this->authServices->forgotPasswordValidation());
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        $response = $this->authServices->forgotPassword($request);
        if ($response) {
            return $this->sendSuccess(['message' => 'Successfully forgot password was created']);
        } else {
            return $this->sendError(['message' => 'Email was not found'], 400);
        }
    }

    /**
     * Update forgotten password.
     *
     * This method updates the forgotten password using the credentials provided in the request.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The HTTP response containing success message if the password was reset successfully, otherwise, error message.
     */
    public function forgotPasswordUpdate(Request $request)
    {
        $credentials = $this->getRequest($request);
        $validator = Validator::make($credentials, $this->authServices->forgotPasswordUpdateValidation());
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
        $response = $this->authServices->forgotPasswordUpdate($credentials);
        if ($response) {
            return $this->sendSuccess(['message' => 'Successfully password was reset']);
        } else {
            return $this->sendError(['message' => 'Invalid Token']);
        }
    }
}

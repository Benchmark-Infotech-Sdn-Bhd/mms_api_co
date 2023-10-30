<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserProfileServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserProfileController extends Controller
{
    /**
     * @var userProfileServices
     */
    private UserProfileServices $userProfileServices;

    /**
     * UserProfileController constructor.
     * @param UserProfileServices $userProfileServices
     */
    public function __construct(UserProfileServices $userProfileServices)
    {
        $this->userProfileServices = $userProfileServices;
    }

    /**
     * Retrieve the admin user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminShow(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->userProfileServices->adminShow($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Update the admin user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminUpdate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $data = $this->userProfileServices->adminUpdate($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * admin user reset password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminResetPassword(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $data = $this->userProfileServices->adminResetPassword($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'reset password failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Retrieve the employee user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function employeeShow(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->userProfileServices->employeeShow($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Update the employee user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function employeeUpdate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $data = $this->userProfileServices->employeeUpdate($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * employee reset password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function employeeResetPassword(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $data = $this->userProfileServices->employeeResetPassword($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'reset password failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

}

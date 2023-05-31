<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * @var userServices
     */
    private UserServices $userServices;

    /**
     * UserController constructor.
     * @param UserServices $userServices
     */
    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }
    
    
    /**
     * Search & Retrieve all the User.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->userServices->adminList($params);
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
     * Retrieve the specified User.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminShow(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->userServices->adminShow($params);
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
     * Show the form for updating a User.
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
            $data = $this->userServices->adminUpdate($params);
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
     * Update the User status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function adminUpdateStatus(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->userServices->adminUpdateStatus($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementProjectServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;
use Exception;

class TotalManagementProjectController extends Controller
{
    /**
     * @var TotalManagementProjectServices
     */
    private $totalManagementProjectServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * TotalManagementProjectController constructor.
     * @param TotalManagementProjectServices $totalManagementProjectServices
     * @param AuthServices $authServices
     */
    public function __construct(TotalManagementProjectServices $totalManagementProjectServices, AuthServices $authServices)
    {
        $this->totalManagementProjectServices = $totalManagementProjectServices;
        $this->authServices = $authServices;
    }
    /**
     * Display list of Project
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementProjectServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Total Management Project'], 400);
        }
    }
    /**
     * Display the Total Management Project
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementProjectServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Total Management Project'], 400);
        }
    }
    /**
     * Add Project
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function add(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->totalManagementProjectServices->add($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Total Manangement Project Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Total Management Project'], 400);
        }
    }
    /**
     * Update Project
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->totalManagementProjectServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Total Management Project Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Total Management Project'], 400);
        }
    }
}
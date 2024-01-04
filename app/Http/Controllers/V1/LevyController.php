<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LevyServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class LevyController extends Controller
{
    /**
     * @var LevyServices
     */
    private $levyServices; 
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * LevyController constructor.
     *  @param AuthServices $authServices
     */
    public function __construct(LevyServices $levyServices, AuthServices $authServices)
    {
        $this->levyServices = $levyServices;
        $this->authServices = $authServices;
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->levyServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Levy Details']);
        }
        
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->levyServices->show($params);
            if(is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Show Levy Details']);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->levyServices->create($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Interview Quota'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Levy Details Created SUccessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Create Levy Details']);
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try{
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->levyServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Interview Quota'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Levy Details Updated SUccessfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage()), true);
            return $this->sendError(['message' => 'Failed to Update Levy Details']);
        }
    }
}

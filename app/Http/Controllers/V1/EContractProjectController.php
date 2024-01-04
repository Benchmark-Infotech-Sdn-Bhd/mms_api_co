<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractProjectServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class EContractProjectController extends Controller
{
    /**
     * @var EContractProjectServices
     */
    private $eContractProjectServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * EContractProjectController constructor.
     * @param EContractProjectServices $eContractProjectServices
     * @param AuthServices $authServices
     */
    public function __construct(EContractProjectServices $eContractProjectServices, AuthServices $authServices)
    {
        $this->eContractProjectServices = $eContractProjectServices;
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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->eContractProjectServices->list($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List E-contract Project'], 400);
        }
    }
    /**
     * Display the E-Contract Project
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
            $response = $this->eContractProjectServices->show($params);
            if(is_null($response) || count($response->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display E-Contract Project'], 400);
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
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->eContractProjectServices->add($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError($response['unauthorizedError']);
            }
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'E-Contract Project Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add E-Contract Project'], 400);
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
            $user = JWTAuth::parseToken()->authenticate();
            //$request['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->eContractProjectServices->update($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError($response['unauthorizedError']);
            }
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'E-Contract Project Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update E-Contract Project'], 400);
        }
    }
    /**
     * delete attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->eContractProjectServices->deleteAttachment($request);
            
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
}
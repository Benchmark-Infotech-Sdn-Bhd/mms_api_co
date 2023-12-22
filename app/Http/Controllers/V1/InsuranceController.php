<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\InsuranceServices;
use Exception;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

class InsuranceController extends Controller
{
    /**
     * @var insuranceServices
     */
    private InsuranceServices $insuranceServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * InsuranceServices constructor.
     * @param InsuranceServices $insuranceServices
     * @param AuthServices $authServices
     */
    public function __construct(InsuranceServices $insuranceServices, AuthServices $authServices)
    {
        $this->insuranceServices = $insuranceServices;
        $this->authServices = $authServices;
    }
	 /**
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $user['company_id'];
            $validation = $this->insuranceServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->insuranceServices->create($request);
            if (isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => $response['unauthorizedError']]);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Insurance creation was failed']);
        }
    }
	 /**
     * Display a listing of the Insurance.
     * @param Request $request
     * @return JsonResponse
     */    
    public function list(Request $request): JsonResponse
    {     
        try {              
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];  
            $response = $this->insuranceServices->list($params); 
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all insurance data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {   
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->insuranceServices->show($request); 
            return $this->sendSuccess($response);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve insurance Data was failed']);
        }  
    } 
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {  
            $user = JWTAuth::parseToken()->authenticate();
            $request['company_id'] = $user['company_id'];  
            $validation = $this->insuranceServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->insuranceServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Insurance update was failed']);
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {     
        try { 
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];  
            $response = $this->insuranceServices->delete($params);
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete insurance was failed']);
        } 
    }
}

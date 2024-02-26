<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentRepatriationServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentRepatriationController extends Controller
{
    /**
     * @var DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices
     */
    private DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices;
    /**
     * @var AuthServices $authServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentOnboardingCountryController constructor method.
     * 
     * @param DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices The instance of Direct Recruitment repatriation services class
     * @param AuthServices $authServices The instance of Authservices class
     */ 
    public function __construct(
        DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices, 
        AuthServices $authServices
    ) 
    {
        $this->directRecruitmentRepatriationServices = $directRecruitmentRepatriationServices;
        $this->authServices = $authServices;
    }
    
    /**
     * Retrieves and returns the list of workers for Repatriation.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment repatriation workers.
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentRepatriationServices->workersList($params);
            if(!empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Update the direct recruitment repatriation.
     *
     * @param Request $request The request object containing the direct recruitment repatriation data.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     */
    public function updateRepatriation(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentRepatriationServices->updateRepatriation($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']); 
            }
            return $this->sendSuccess(['message' => 'Repatriation Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Repatriation'], 400);
        }
    }
    /**
     * Retrieves and returns the list of direct recruitment repatriation workers for export.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment repatriation workers for export.
     */
    public function workersListExport(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentRepatriationServices->workersListExport($params);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
}

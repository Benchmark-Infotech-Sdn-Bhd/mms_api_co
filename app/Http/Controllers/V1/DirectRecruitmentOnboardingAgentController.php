<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentOnboardingAgentServices;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class DirectRecruitmentOnboardingAgentController extends Controller
{
    /**
     * @var DirectRecruitmentOnboardingAgentServices
     */
    private $directRecruitmentOnboardingAgentServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentOnboardingAgentController Constructor
     * @param DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices
     * @param AuthServices $authServices
     */
    
    public function __construct(DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices, AuthServices $authServices)
    {
        $this->directRecruitmentOnboardingAgentServices = $directRecruitmentOnboardingAgentServices;
        $this->authServices = $authServices;
    }
    /**
     * Display list of agent
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
            $response = $this->directRecruitmentOnboardingAgentServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Agent'], 400);
        }
    }
    /**
     * Display the onboarding agent
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
            $response = $this->directRecruitmentOnboardingAgentServices->show($params);
            if(is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Agent'], 400);
        }
    }
    /**
     * Add Agent to Onboarding Process
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->create($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
            } else if(isset($response['agentError'])) {
                return $this->sendError(['message' => 'The Agent already added for this Country and KSM Reference Number'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Agent Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Add Agent'], 400);
        }
    }
    /**
     * Update agent to Onboarding Process
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
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
            } else if(isset($response['editError'])) {
                return $this->sendError(['message' => 'Attestation submission has been processed for this record, users are not allowed to modify the records.'], 422);
            } else if(isset($response['agentError'])) {
                return $this->sendError(['message' => 'The Agent already added for this Country and KSM Reference Number'], 422);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Agent Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Agent'], 400);
        }
    }
    /**
     * Dropdown KSM Referenec Number
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function ksmDropDownBasedOnOnboarding(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->ksmDropDownBasedOnOnboarding($params);
            if(is_null($response) || count($response) == 0) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List KSM Reference Numbers'], 400);
        }
    }
}

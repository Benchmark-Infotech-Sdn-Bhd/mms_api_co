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
     * @var DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices
     */
    private DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices;
    /**
     * @var AuthServices $authServices
     */
    private AuthServices $authServices;

    /**
     * Constructs a new instance of the class.
     *
     * @param DirectRecruitmentOnboardingAgentServices An instance of the DirectRecruitmentOnboardingAgentServices class.
     * @param AuthServices $authServices An instance of the AuthServices class.
     */    
    public function __construct(DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices, AuthServices $authServices)
    {
        $this->directRecruitmentOnboardingAgentServices = $directRecruitmentOnboardingAgentServices;
        $this->authServices = $authServices;
    }
    /**
     * Retrieves and returns the list of direct recruitment onboarding agent.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment onboarding agent.
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
            return $this->sendError(['message' => 'Failed to List Onboarding Agent']);
        }
    }
    /**
     * Show method to display the direct recruitment onboarding agent detail.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response with success or error message.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAgentServices->show($params);
            if (is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Agent']);
        }
    }
    /**
     * Create a new direct recruitment onboarding agent.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response object.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->create($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if (isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
            } else if (isset($response['agentError'])) {
                return $this->sendError(['message' => 'The Agent already added for this Country and KSM Reference Number'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Agent Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Add Agent']);
        }
    }
   /**
     * Update the direct recruitment onboarding agent.
     *
     * @param Request $request The request object containing the direct recruitment onboarding agent data.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->update($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
            } else if (isset($response['editError'])) {
                return $this->sendError(['message' => 'Attestation submission has been processed for this record, users are not allowed to modify the records.'], 422);
            } else if (isset($response['agentError'])) {
                return $this->sendError(['message' => 'The Agent already added for this Country and KSM Reference Number'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Agent Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Agent']);
        }
    }
    /**
     * Retrieves and returns the list of ksm reference number from direct recruitment onboarding countries.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of ksm reference number from direct recruitment onboarding countries.
     */
    public function ksmDropDownBasedOnOnboarding(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAgentServices->ksmDropDownBasedOnOnboarding($params);
            if (is_null($response) || count($response) == 0) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List KSM Reference Numbers']);
        }
    }
}

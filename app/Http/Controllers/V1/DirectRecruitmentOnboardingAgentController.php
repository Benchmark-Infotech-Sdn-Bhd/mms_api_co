<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentOnboardingAgentServices;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentOnboardingAgentController extends Controller
{
    /**
     * @var DirectRecruitmentOnboardingAgentServices
     */
    private $directRecruitmentOnboardingAgentServices;

    /**
     * DirectRecruitmentOnboardingAgentController Constructor
     * @param DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices
     */
    
    public function __construct(DirectRecruitmentOnboardingAgentServices $directRecruitmentOnboardingAgentServices)
    {
        $this->directRecruitmentOnboardingAgentServices = $directRecruitmentOnboardingAgentServices;
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
            $response = $this->directRecruitmentOnboardingAgentServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e-getMessage(), true));
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
            $response = $this->directRecruitmentOnboardingAgentServices->show($params);
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
            $response = $this->directRecruitmentOnboardingAgentServices->create($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
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
            $response = $this->directRecruitmentOnboardingAgentServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Country Quota'], 422);
            }
            return $this->sendSuccess(['message' => 'Agent Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Agent'], 400);
        }
    }
}

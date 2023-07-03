<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentOnboardingCountryController extends Controller
{
    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private $directRecruitmentOnboardingCountryServices;

    /**
     * DirectRecruitmentOnboardingCountryController Constructor
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices
     */
    
    public function __construct(DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices)
    {
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
    }
    /**
     * Display list of countries
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentOnboardingCountryServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Countries'], 400);
        }
    }
    /**
     * Display the onboarding country
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentOnboardingCountryServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Country'], 400);
        }
    }
    /**
     * Add country to Onboarding Process
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
            $response = $this->directRecruitmentOnboardingCountryServices->create($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved Quota'], 400);
            }
            return $this->sendSuccess(['message' => 'Country Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Add Country'], 400);
        }
    }
    /**
     * Update country to Onboarding Process
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
            $response = $this->directRecruitmentOnboardingCountryServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved Quota'], 400);
            }
            return $this->sendSuccess(['message' => 'Country Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Country'], 400);
        }
    }
    /**
     * List KSM Referenec Number
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function ksmReferenceNumberList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Countries'], 400);
        }
    }

    /**
     * Update country to Onboarding Process Status Update
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function onboarding_status_update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } 
            return $this->sendSuccess(['message' => 'Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Status'], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class DirectRecruitmentOnboardingCountryController extends Controller
{
    /**
     * @var DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    /**
     * @var AuthServices $authServices
     */
    private AuthServices $authServices;
    
    /**
     * DirectRecruitmentOnboardingCountryController constructor method.
     * 
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The instance of Direct Recruitment onBoarding country services class
     * @param AuthServices $authServices The instance od Authservices class
     */    
    public function __construct(DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, AuthServices $authServices)
    {
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->authServices = $authServices;
    }
    /**
     * Retrieves and returns the list of direct recruitment onboarding countries.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment onboarding countries.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingCountryServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Countries']);
        }
    }
    
    /**
     * Show method to get direct recruitment onboarding country detail
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
            $response = $this->directRecruitmentOnboardingCountryServices->show($params);
            if (is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Country']);
        }
    }
    /**
     * Create a new direct recruitment onboarding country.
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
            $response = $this->directRecruitmentOnboardingCountryServices->create($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Country Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Add Country']);
        }
    }
    /**
     * Update the direct recruitment onboarding country.
     *
     * @param Request $request The request object containing the direct recruitment onboarding country data.
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
            $response = $this->directRecruitmentOnboardingCountryServices->update($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['editError'])) {
                return $this->sendError(['message' => 'An Agent has been assigned to this record; users cannot edit the records'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Country Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Country']);
        }
    }
    /**
     * Create a new ksm number in direct recruitment onboarding process.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response object with success or error message.
     */
    public function addKSM(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingCountryServices->addKSM($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            } else if (isset($response['ksmNumberError'])) {
                return $this->sendError(['message' => 'The KSM Reference Number for this Country Has Been Added Already'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'KSM Refrence Number Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Country']);
        }
    }
    /**
     * Update the direct recruitment onboarding ksm reference number quota.
     *
     * @param Request $request The request object containing the direct recruitment onboarding ksm reference number quota.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     */
    public function ksmQuotaUpdate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingCountryServices->ksmQuotaUpdate($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['dataError'])) {
                return $this->sendError(['message' => 'Data Not Found'], 422);
            } else if (isset($response['editError'])) {
                return $this->sendError(['message' => 'An Agent has been assigned to this record; users cannot edit the records'], 422);
            } else if (isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            } else if (isset($response['ksmNumberError'])) {
                return $this->sendError(['message' => 'The KSM Reference Number for this Country Has Been Added Already'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Quota Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Record']);
        }
    }
     /**
     * Delete direct recruitment onboarding ksm reference number quota.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response with success or error message.
     */
    public function deleteKSM(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingCountryServices->deleteKSM($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['dataError'])) {
                return $this->sendError(['message' => 'Data Not Found'], 422);
            } else if (isset($response['editError'])) {
                return $this->sendError(['message' => 'An Agent has been assigned to this record; users cannot edit the records'], 422);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Record Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Delete Record']);
        }
    }
    /**
     * Retrieves and returns the list of KSM reference numbers.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of KSM reference numbers.
     */
    public function ksmReferenceNumberList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Countries']);
        }
    }
    /**
     * Retrieves and returns the list of ksm reference number from direct recruitment applications.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of ksm reference number from direct recruitment applications.
     */
    public function ksmDropDownForOnboarding(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingCountryServices->ksmDropDownForOnboarding($params);
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List KSM Reference Numbers']);
        }
    }
}

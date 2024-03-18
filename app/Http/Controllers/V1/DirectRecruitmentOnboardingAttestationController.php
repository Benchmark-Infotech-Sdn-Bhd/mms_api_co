<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentOnboardingAttestationServices;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class DirectRecruitmentOnboardingAttestationController extends Controller
{
    /**
     * @var DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices
     */
    private DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices;
    /**
     * @var AuthServices $authServices
     */
    private AuthServices $authServices;
    
    /**
     * DirectRecruitmentOnboardingAttestationController constructor method.
     * 
     * @param DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices The instance of Direct Recruitment Attestation services class
     * @param AuthServices $authServices The instance od Authservices class
     */
    public function __construct(
        DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices, 
        AuthServices $authServices
    )
    {
        $this->directRecruitmentOnboardingAttestationServices = $directRecruitmentOnboardingAttestationServices;
        $this->authServices = $authServices;
    }
    
    /**
     * Retrieves and returns the list of direct recruitment attestations.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment attestations.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Attestation']);
        }
    }
    /**
     * Display the direct recruitment attestation detail.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing a direct recruitment attestation.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->show($params);
            if (is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Attestation']);
        }
    }
    /**
     * Updates the direct recruitment attestation
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     * @throws Exception If an error occurs during the update operation.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAttestationServices->update($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Attestation Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Attestation']);
        }
    }
    /**
     * Display the direct recruitment onboarding dispatch detail.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing a direct recruitment onboarding dispatch detail.
     */
    public function showDispatch(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->showDispatch($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display the Dispatch']);
        }
    }
    /**
     * Updates the direct recruitment onboarding dispatch
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the result of the update operation.
     * @throws Exception If an error occurs during the update operation.
     */
    public function updateDispatch(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAttestationServices->updateDispatch($params);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Dispatch Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Dispatch']);
        }
    }
     /**
     * Retrieves and returns the list of direct recruitment onboarding embassy.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment onboarding embassy.
     */
    public function listEmbassy(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->listEmbassy($params);
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Embassy Attestation Costing']);
        }
    }
    /**
     * Display the direct recruitment onboarding embassy detail.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing a direct recruitment onboarding embassy detail.
     */
    public function showEmbassyFile(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->showEmbassyFile($params);
            if (isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Embassy Attestation Costing']);
        }
    }
    /**
     * Display the direct recruitment onboarding Embassy Attestation File.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing a direct recruitment onboarding embassy Attestation File.
     */
    public function uploadEmbassyFile(Request $request): JsonResponse
    {
        try {
            $response = $this->directRecruitmentOnboardingAttestationServices->uploadEmbassyFile($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            if ($response == true) {
                return $this->sendSuccess(['message' => 'Embassy Attestation Costing Updated Successfully']);
            } else {
                return $this->sendError(['message' => 'Failed to Update Embassy Attestation Costing']);
            }
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Embassy Attestation Costing']);
        }
    }
    /**
     * Deletes a record using the given request.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the result of the deletion.
     */
    public function deleteEmbassyFile(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAttestationServices->deleteEmbassyFile($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
}

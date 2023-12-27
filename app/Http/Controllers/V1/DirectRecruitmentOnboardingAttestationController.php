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
     * @var DirectRecruitmentOnboardingAttestationServices
     */
    private $directRecruitmentOnboardingAttestationServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentOnboardingAttestationController Constructor
     * @param DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices
     * @param AuthServices $authServices
     */
    
    public function __construct(DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices, AuthServices $authServices)
    {
        $this->directRecruitmentOnboardingAttestationServices = $directRecruitmentOnboardingAttestationServices;
        $this->authServices = $authServices;
    }
    /**
     * Display list of Attestation
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
            $response = $this->directRecruitmentOnboardingAttestationServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Onboarding Attestation'], 400);
        }
    }
    /**
     * Display the onboarding Attestation
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
            $response = $this->directRecruitmentOnboardingAttestationServices->show($params);
            if(is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Onboarding Attestation'], 400);
        }
    }
    /**
     * Update Attestation to Onboarding Process
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
            $response = $this->directRecruitmentOnboardingAttestationServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Attestation Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Attestation'], 400);
        }
    }
    /**
     * Display the Dispatch
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function showDispatch(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->showDispatch($params);
            if(is_null($response) || count($response) == 0) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display the Dispatch'], 400);
        }
    }
    /**
     * Update Dispatch
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function updateDispatch(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentOnboardingAttestationServices->updateDispatch($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess(['message' => 'Dispatch Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Dispatch'], 400);
        }
    }
    /**
     * Display list Embassy
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function listEmbassy(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->listEmbassy($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Embassy Attestation Costing'], 400);
        }
    }
    /**
     * Show the Embassy
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function showEmbassyFile(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentOnboardingAttestationServices->showEmbassyFile($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Embassy Attestation Costing'], 400);
        }
    }
    /**
     * Upload Embassy Attestation File
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function uploadEmbassyFile(Request $request): JsonResponse
    {
        try {
            $response = $this->directRecruitmentOnboardingAttestationServices->uploadEmbassyFile($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            if($response == true) {
                return $this->sendSuccess(['message' => 'Embassy Attestation Costing Updated Successfully']);
            } else {
                return $this->sendError(['message' => 'Failed to Update Embassy Attestation Costing'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Embassy Attestation Costing'], 400);
        }
    }
    /**
     * delete the embassy file.
     *
     * @param Request $request
     * @return JsonResponse
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

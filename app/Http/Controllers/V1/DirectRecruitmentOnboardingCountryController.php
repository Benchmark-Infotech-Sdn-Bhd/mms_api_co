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
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved Quota'], 422);
            } else if(isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
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
            } else if(isset($response['editError'])) {
                return $this->sendError(['message' => 'Agent added for this record, users are not allowed to modify the records.'], 422);
            } else if(isset($response['updateError'])) {
                return $this->sendError(['message' => 'The selected onboarding country has more than one KSM reference numbers. Unable to Update'], 422);
            } else if(isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            }
            return $this->sendSuccess(['message' => 'Country Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Country'], 400);
        }
    }
    /**
     * Add ksm number to Onboarding Process
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function addKSM(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentOnboardingCountryServices->addKSM($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['editError'])) {
                return $this->sendError(['message' => 'Agent added for this record, users are not allowed to modify the records.'], 422);
            } else if(isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            } else if(isset($response['ksmNumberError'])) {
                return $this->sendError(['message' => 'The KSM Reference Number Already Added for this Country'], 422);
            }
            return $this->sendSuccess(['message' => 'Country Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Country'], 400);
        }
    }
    /**
     * Update ksm reference number quota
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function ksmQuotaUpdate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentOnboardingCountryServices->ksmQuotaUpdate($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['editError'])) {
                return $this->sendError(['message' => 'Agent added for this record, users are not allowed to modify the records.'], 422);
            } else if(isset($response['ksmQuotaError'])) {
                return $this->sendError(['message' => 'The number of quota cannot exceed the Approved KSM Quota'], 422);
            } else if(isset($response['ksmNumberError'])) {
                return $this->sendError(['message' => 'The KSM Reference Number Already Added for this Country'], 422);
            }
            return $this->sendSuccess(['message' => 'Quota Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Update Record'], 400);
        }
    }
    /**
     * delete ksm reference number quota
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function deleteKSM(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentOnboardingCountryServices->deleteKSM($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['dataError'])) {
                return $this->sendError(['message' => 'Data Not Found'], 422);
            } else if(isset($response['editError'])) {
                return $this->sendError(['message' => 'Agent added for this record, users are not allowed to modify the records.'], 422);
            }
            return $this->sendSuccess(['message' => 'Record Deleted Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Delete Record'], 400);
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
     * Dropdown KSM Referenec Number
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function ksmDropDownForOnboarding(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentOnboardingCountryServices->ksmDropDownForOnboarding($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List KSM Reference Numbers'], 400);
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

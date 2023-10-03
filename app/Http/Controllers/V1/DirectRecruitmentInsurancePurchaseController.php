<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentInsurancePurchaseServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentInsurancePurchaseController extends Controller
{
    /**
     * @var DirectRecruitmentInsurancePurchaseServices
     */
    private $directRecruitmentInsurancePurchaseServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentInsurancePurchaseController constructor.
     * @param DirectRecruitmentInsurancePurchaseServices $directRecruitmentInsurancePurchaseServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentInsurancePurchaseServices $directRecruitmentInsurancePurchaseServices, AuthServices $authServices) 
    {
        $this->directRecruitmentInsurancePurchaseServices = $directRecruitmentInsurancePurchaseServices;
        $this->authServices = $authServices;
    }
    /**
     * Display list of Insurance Purchase
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentInsurancePurchaseServices->workersList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Insurance Purchase'], 400);
        }
    }
    /**
     * Show the Insurance Purchase Detail
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentInsurancePurchaseServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to Display Insurance Purchase'], 400);
        }
    }
    /**
     * submit insurance purchase
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function submit(Request $request): JsonResponse
    {
        try {
            $response = $this->directRecruitmentInsurancePurchaseServices->submit($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['workerCountError'])) {
                return $this->sendError(['message' => 'Please select all worker names under the selected Calling Visa Number before submitting'], 422);
            } else if(isset($response['visaReferenceNumberCountError'])) {
                return $this->sendError(['message' => 'Please check the calling visa reference number in selected worker name from the listing'], 422);
            } else if($response == true) {
                return $this->sendSuccess(['message' => 'Insurance Purchase Submitted Successfully']);
            } else {
                return $this->sendError(['message' => 'Failed to Submit Insurance Purchase'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Insurance Purchase'], 400);
        }
    }
    /**
     * Display list of Insurance Providers
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function insuranceProviderDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentInsurancePurchaseServices->insuranceProviderDropDown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Insurance Provider'], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentCallingVisaGenerateServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentCallingVisaGenerateController extends Controller
{
    /**
     * @var DirectRecruitmentCallingVisaGenerateServices
     */
    private $directRecruitmentCallingVisaGenerateServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentCallingVisaGenerateController constructor.
     * @param DirectRecruitmentCallingVisaGenerateServices $directRecruitmentCallingVisaGenerateServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentCallingVisaGenerateServices $directRecruitmentCallingVisaGenerateServices, AuthServices $authServices) 
    {
        $this->directRecruitmentCallingVisaGenerateServices = $directRecruitmentCallingVisaGenerateServices;
        $this->authServices = $authServices;
    }
    /**
     * Submit calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generatedStatusUpdate(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $this->directRecruitmentCallingVisaGenerateServices->generatedStatusUpdate($params);
            return $this->sendSuccess(['message' => 'Calling Visa Generated Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Calling Visa Generated Status'], 400);
        }
    }
    /**
     * Display list of workers for particular calling visa reference number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentCallingVisaGenerateServices->workersList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Display list of calling visa reference number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listBasedOnCallingVisa(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentCallingVisaGenerateServices->listBasedOnCallingVisa($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Calling Visa'], 400);
        }
    }
    /**
     * Display previous details based on calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaGenerateServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Details'], 400);
        }
    }
}

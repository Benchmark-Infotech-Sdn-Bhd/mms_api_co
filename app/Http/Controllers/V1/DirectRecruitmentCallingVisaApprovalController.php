<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentCallingVisaApprovalServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentCallingVisaApprovalController extends Controller
{
     /**
     * @var DirectRecruitmentCallingVisaApprovalServices
     */
    private $directRecruitmentCallingVisaApprovalServices;

    /**
     * DirectRecruitmentCallingVisaApprovalController constructor.
     * @param DirectRecruitmentCallingVisaApprovalServices $directRecruitmentCallingVisaApprovalServices
     */
    public function __construct(DirectRecruitmentCallingVisaApprovalServices $directRecruitmentCallingVisaApprovalServices) 
    {
        $this->directRecruitmentCallingVisaApprovalServices = $directRecruitmentCallingVisaApprovalServices;
    }
    /**
     * Display list of calling visa updation status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersListForApproval(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaApprovalServices->workersListForApproval($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Submit calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callingVisaStatusUpdate(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->directRecruitmentCallingVisaApprovalServices->callingVisaStatusUpdate($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['workerCountError'])) {
                return $this->sendError(['message' => 'Worker Count should not exceed to 30'], 400);
            }
            return $this->sendSuccess(['message' => 'Calling Visa Submitted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Calling Visa Status'], 400);
        }
    }
}

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
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaApprovalServices->workersList($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
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
    public function approvalStatusUpdate(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentCallingVisaApprovalServices->approvalStatusUpdate($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['visaReferenceNumberCountError'])) {
                return $this->sendError(['message' => 'Please select same calling visa reference number in worker listing'], 422);
            }
            return $this->sendSuccess(['message' => 'Approval Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Approval Status'], 400);
        }
    }
    /**
     * Display the approval calling visa details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaApprovalServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Calling Visa Approval Details'], 400);
        }
    }
}

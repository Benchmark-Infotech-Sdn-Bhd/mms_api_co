<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DirectRecruitmentApplicationApprovalServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentApplicationApprovalController extends Controller
{
    /**
     * @var DirectRecruitmentApplicationApprovalServices
     */
    private $directRecruitmentApplicationApprovalServices;

    /**
     * DirectRecruitmentApplicationApprovalController constructor.
     * @param DirectRecruitmentApplicationApprovalServices $directRecruitmentApplicationApprovalServices
     */
    public function __construct(DirectRecruitmentApplicationApprovalServices $directRecruitmentApplicationApprovalServices) 
    {
        $this->directRecruitmentApplicationApprovalServices = $directRecruitmentApplicationApprovalServices;
    }
    /**
     * Display a listing of the Approval Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $param = $this->getRequest($request);
            $response = $this->directRecruitmentApplicationApprovalServices->list($param);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Approval Details']);
        }
    }
    /**
     * Display the Approval Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentApplicationApprovalServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Approval Details']);
        }
    }
    /**
     * Create the Approval Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try{
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['created_by'] = $user['id'];
            $response = $this->directRecruitmentApplicationApprovalServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Approval Details Added Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Approval Details']);
        }
    }
    /**
     * Update the Approval Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['modified_by'] = $user['id'];
            $response = $this->directRecruitmentApplicationApprovalServices->update($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Approval Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Approval Details']);
        }
    }

    /**
     * delete the specified Attachment data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $response = $this->directRecruitmentApplicationApprovalServices->deleteAttachment($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
}

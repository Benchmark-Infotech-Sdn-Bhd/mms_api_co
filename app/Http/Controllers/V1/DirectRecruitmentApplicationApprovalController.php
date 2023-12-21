<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DirectRecruitmentApplicationApprovalServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class DirectRecruitmentApplicationApprovalController extends Controller
{
    /**
     * @var DirectRecruitmentApplicationApprovalServices
     */
    private $directRecruitmentApplicationApprovalServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentApplicationApprovalController constructor.
     * @param DirectRecruitmentApplicationApprovalServices $directRecruitmentApplicationApprovalServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentApplicationApprovalServices $directRecruitmentApplicationApprovalServices, AuthServices $authServices) 
    {
        $this->directRecruitmentApplicationApprovalServices = $directRecruitmentApplicationApprovalServices;
        $this->authServices = $authServices;
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
            $user = JWTAuth::parseToken()->authenticate();
            $param['company_id'] = $this->authServices->getCompanyIds($user);
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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentApplicationApprovalServices->show($params);
            if(is_null($response)) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
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
            $user = JWTAuth::parseToken()->authenticate();
            $request['created_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentApplicationApprovalServices->create($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
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
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $request['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentApplicationApprovalServices->update($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
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
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->directRecruitmentApplicationApprovalServices->deleteAttachment($params);
            if(isset($response['InvalidUser'])) {
                return $this->sendError(['message' => 'Unauthorized.']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
}

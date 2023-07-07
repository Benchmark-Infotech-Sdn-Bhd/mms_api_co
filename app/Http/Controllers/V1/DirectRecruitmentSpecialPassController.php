<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentSpecialPassServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentSpecialPassController extends Controller
{
    /**
     * @var DirectRecruitmentSpecialPassServices
     */
    private $directRecruitmentSpecialPassServices;

    /**
     * DirectRecruitmentSpecialPassController constructor.
     * @param DirectRecruitmentSpecialPassServices $directRecruitmentSpecialPassServices
     */
    public function __construct(DirectRecruitmentSpecialPassServices $directRecruitmentSpecialPassServices) 
    {
        $this->directRecruitmentSpecialPassServices = $directRecruitmentSpecialPassServices;
    }
    /**
     * Dispaly list of workers for FOMEMA.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentSpecialPassServices->workersList($params);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Update Special Pass Submission Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSubmission(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentSpecialPassServices->updateSubmission($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Submission Date Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Submission Date'], 400);
        }
    }
    /**
     * Update Special Pass Validity.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateValidity(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentSpecialPassServices->updateValidity($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Validity Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Validity'], 400);
        }
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentRepatriationServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentRepatriationController extends Controller
{
    /**
     * @var DirectRecruitmentRepatriationServices
     */
    private $directRecruitmentRepatriationServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentRepatriationController constructor.
     * @param DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices, AuthServices $authServices) 
    {
        $this->directRecruitmentRepatriationServices = $directRecruitmentRepatriationServices;
        $this->authServices = $authServices;
    }
    /**
     * Dispaly list of workers for Repatriation.
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
            $params['user'] = $user;
            $response = $this->directRecruitmentRepatriationServices->workersList($params);
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
     * Update Purchase Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateRepatriation(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentRepatriationServices->updateRepatriation($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Repatriation Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Repatriation'], 400);
        }
    }
    /**
     * Dispaly list of workers for Repatriation export.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersListExport(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentRepatriationServices->workersListExport($params);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
}

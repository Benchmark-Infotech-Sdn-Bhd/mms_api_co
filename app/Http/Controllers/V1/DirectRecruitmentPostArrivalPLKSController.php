<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentPostArrivalPLKSServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentPostArrivalPLKSController extends Controller
{
    /**
     * @var DirectRecruitmentPostArrivalPLKSServices
     */
    private $directRecruitmentPostArrivalPLKSServices;

    /**
     * DirectRecruitmentPostArrivalPLKSController constructor.
     * @param DirectRecruitmentPostArrivalPLKSServices $directRecruitmentPostArrivalPLKSServices
     */
    public function __construct(DirectRecruitmentPostArrivalPLKSServices $directRecruitmentPostArrivalPLKSServices) 
    {
        $this->directRecruitmentPostArrivalPLKSServices = $directRecruitmentPostArrivalPLKSServices;
    }
    /**
     * Dispaly list of workers for PLKS.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentPostArrivalPLKSServices->workersList($params);
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
    public function updatePLKS(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentPostArrivalPLKSServices->updatePLKS($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'PLKS Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update PLKS Status'], 400);
        }
    }
    /**
     * Dispaly list of workers for PLKS export.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersListExport(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentPostArrivalPLKSServices->workersListExport($params);
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

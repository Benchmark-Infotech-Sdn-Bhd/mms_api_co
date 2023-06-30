<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentRepatriationServices;
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
     * DirectRecruitmentRepatriationController constructor.
     * @param DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices
     */
    public function __construct(DirectRecruitmentRepatriationServices $directRecruitmentRepatriationServices) 
    {
        $this->directRecruitmentRepatriationServices = $directRecruitmentRepatriationServices;
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
    public function updatePLKS(Request $request) : JsonResponse
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
}

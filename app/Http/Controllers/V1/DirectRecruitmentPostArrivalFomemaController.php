<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentPostArrivalFomemaServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentPostArrivalFomemaController extends Controller
{
    /**
     * @var DirectRecruitmentPostArrivalFomemaServices
     */
    private $DirectRecruitmentPostArrivalFomemaServices;

    /**
     * DirectRecruitmentPostArrivalFomemaController constructor.
     * @param DirectRecruitmentPostArrivalFomemaServices $directRecruitmentPostArrivalFomemaServices
     */
    public function __construct(DirectRecruitmentPostArrivalFomemaServices $directRecruitmentPostArrivalFomemaServices) 
    {
        $this->directRecruitmentPostArrivalFomemaServices = $directRecruitmentPostArrivalFomemaServices;
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
            $response = $this->directRecruitmentPostArrivalFomemaServices->workersList($params);
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
    public function purchase(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentPostArrivalFomemaServices->purchase($params);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Purchase Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Purchase Details'], 400);
        }
    }
    /**
     * Update Purchase Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fomemaFit(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentPostArrivalFomemaServices->fomemaFit($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'FOMEMA Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update FOMEMA Status'], 400);
        }
    }
    /**
     * Update Purchase Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fomemaUnfit(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentPostArrivalFomemaServices->fomemaUnfit($request);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'FOMEMA Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update FOMEMA Status'], 400);
        }
    }
}

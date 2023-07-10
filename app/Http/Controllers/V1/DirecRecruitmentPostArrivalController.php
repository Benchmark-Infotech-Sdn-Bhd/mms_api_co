<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirecRecruitmentPostArrivalServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirecRecruitmentPostArrivalController extends Controller
{
    /**
     * @var DirecRecruitmentPostArrivalServices
     */
    private $direcRecruitmentPostArrivalServices;

    /**
     * DirecRecruitmentPostArrivalController constructor.
     * @param DirecRecruitmentPostArrivalServices $direcRecruitmentPostArrivalServices
     */
    public function __construct(DirecRecruitmentPostArrivalServices $direcRecruitmentPostArrivalServices) 
    {
        $this->direcRecruitmentPostArrivalServices = $direcRecruitmentPostArrivalServices;
    }
    /**
     * Dispaly list of workers for post arrival.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postArrivalStatusList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->direcRecruitmentPostArrivalServices->postArrivalStatusList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Status'], 400);
        }
    }
    /**
     * Dispaly list of workers for post arrival.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->direcRecruitmentPostArrivalServices->workersList($params);
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
     * Update post arrival details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePostArrival(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->direcRecruitmentPostArrivalServices->updatePostArrival($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Post Arrival Details Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Post Arrival Details'], 400);
        }
    }
    /**
     * Update JTK submission date.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateJTKSubmission(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->direcRecruitmentPostArrivalServices->UpdateJTKSubmission($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'JTK Submission Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update JTK Submission'], 400);
        }
    }
    /**
     * Update cancellation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCancellation(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->direcRecruitmentPostArrivalServices->updateCancellation($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Cancellation Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Cancellation'], 400);
        }
    }
    /**
     * Update Postponed.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePostponed(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->direcRecruitmentPostArrivalServices->updatePostponed($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Postponed Status Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Postponed Status'], 400);
        }
    }
}

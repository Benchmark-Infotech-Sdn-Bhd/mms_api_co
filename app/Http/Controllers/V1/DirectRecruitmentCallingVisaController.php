<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentCallingVisaServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentCallingVisaController extends Controller
{
    /**
     * @var DirectRecruitmentCallingVisaServices
     */
    private $directRecruitmentCallingVisaServices;

    /**
     * DirectRecruitmentCallingVisaController constructor.
     * @param DirectRecruitmentCallingVisaServices $directRecruitmentCallingVisaServices
     */
    public function __construct(DirectRecruitmentCallingVisaServices $directRecruitmentCallingVisaServices) 
    {
        $this->directRecruitmentCallingVisaServices = $directRecruitmentCallingVisaServices;
    }
    /**
     * Display list of calling visa updation status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callingVisaStatusList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaServices->callingVisaStatusList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Calling Visa Status'], 400);
        }
    }
    /**
     * Submit calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCallingVisa(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentCallingVisaServices->submitCallingVisa($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['workerCountError'])) {
                return $this->sendError(['message' => 'Worker Count should not exceed to 30'], 400);
            }
            return $this->sendSuccess(['message' => 'Calling Visa Submitted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Calling Visa'], 400);
        }
    }
    /**
     * Dispaly list of workers for calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaServices->workersList($params);
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
     * Dispaly the calling visa process details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Calling Visa Process Details'], 400);
        }
    }
    /**
     * Function to Cancel worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelWorker(Request $request) : JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $request['modified_by'] = $user['id'];
            $response = $this->directRecruitmentCallingVisaServices->cancelWorker($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Worker Cancellation Completed Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Cancel Worker'], 400);
        }
    }
}

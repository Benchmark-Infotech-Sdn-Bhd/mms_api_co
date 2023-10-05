<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentArrivalServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentArrivalController extends Controller
{
    /**
     * @var DirectRecruitmentArrivalServices
     */
    private $directRecruitmentArrivalServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentArrivalController constructor.
     * @param DirectRecruitmentArrivalServices $directRecruitmentArrivalServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentArrivalServices $directRecruitmentArrivalServices, AuthServices $authServices) 
    {
        $this->directRecruitmentArrivalServices = $directRecruitmentArrivalServices;
        $this->authServices = $authServices;
    }
    /**
     * Display list of Arrival
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentArrivalServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Arrival'], 400);
        }
    }
    /**
     * Dispaly the arrival details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentArrivalServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Arrival Details'], 400);
        }
    }
    /**
     * Display list of workers for arrival submit
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function workersListForSubmit(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentArrivalServices->workersListForSubmit($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List workers'], 400);
        }
    }
    /**
     * Display list of workers for arrival update
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function workersListForUpdate(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentArrivalServices->workersListForUpdate($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List workers'], 400);
        }
    }
    /**
     * Submit Arrival.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['created_by'] = $user['id'];
            $response = $this->directRecruitmentArrivalServices->submit($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Arrival Submitted Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Submit Arrival'], 400);
        }
    }
    /**
     * update Arrival.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentArrivalServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Arrival Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Arrival'], 400);
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
            $response = $this->directRecruitmentArrivalServices->cancelWorker($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if($response == true ){
                return $this->sendSuccess(['message' => 'Worker Cancellation Completed Successfully']);
            }else{
                return $this->sendError(['message' => 'Failed to Cancel Worker'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Cancel Worker'], 400);
        }
    }
    /**
     * Dispaly the worker detail
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cancelWorkerDetail(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentArrivalServices->cancelWorkerDetail($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Worker Details'], 400);
        }
    }
    /**
     * Dispaly the calling visa reference numbers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function callingvisaReferenceNumberList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentArrivalServices->callingvisaReferenceNumberList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Calling Visa Reference Number'], 400);
        }
    }
    /**
     * update workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateWorkers(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $response = $this->directRecruitmentArrivalServices->updateWorkers($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if($response == true ){
                return $this->sendSuccess(['message' => 'Workers Updated Successfully']);
            }else{
                return $this->sendError(['message' => 'Failed to Update Worker'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Worker'], 400);
        }
    }
     /**
     * Display list of workers for arrival submit
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function arrivalDateDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentArrivalServices->arrivalDateDropDown($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Arrival Dates'], 400);
        }
    }
    
}

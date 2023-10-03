<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentCallingVisaDispatchServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentCallingVisaDispatchController extends Controller
{
    /**
     * @var directRecruitmentCallingVisaDispatchServices
     */
    private $directRecruitmentCallingVisaDispatchServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentCallingVisaDispatchController constructor.
     * @param DirectRecruitmentCallingVisaDispatchServices $directRecruitmentCallingVisaDispatchServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentCallingVisaDispatchServices $directRecruitmentCallingVisaDispatchServices,AuthServices $authServices) 
    {
        $this->directRecruitmentCallingVisaDispatchServices = $directRecruitmentCallingVisaDispatchServices;
        $this->authServices = $authServices;
    }
    /**
     * Update Calling visa dispatch.
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
            $response = $this->directRecruitmentCallingVisaDispatchServices->update($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if($response == true) {
                return $this->sendSuccess(['message' => 'Calling Visa Dispatch Updated Successfully']);
            } else {
                return $this->sendError(['message' => 'Failed to Update Calling Visa Dispatch'], 400);
            }
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Calling Visa Dispatch'], 400);
        }
    }
    /**
     * Display list of workers for particular calling visa reference number.
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
            $response = $this->directRecruitmentCallingVisaDispatchServices->workersList($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Display list of workers based on calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listBasedOnCallingVisa(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->directRecruitmentCallingVisaDispatchServices->listBasedOnCallingVisa($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Display previous details based on calling visa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->directRecruitmentCallingVisaDispatchServices->show($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Details'], 400);
        }
    }
}

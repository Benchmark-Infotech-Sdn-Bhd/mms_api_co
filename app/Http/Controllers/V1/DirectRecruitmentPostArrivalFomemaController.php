<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentPostArrivalFomemaServices;
use App\Services\AuthServices;
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
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentPostArrivalFomemaController constructor.
     * @param DirectRecruitmentPostArrivalFomemaServices $directRecruitmentPostArrivalFomemaServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentPostArrivalFomemaServices $directRecruitmentPostArrivalFomemaServices, AuthServices $authServices) 
    {
        $this->directRecruitmentPostArrivalFomemaServices = $directRecruitmentPostArrivalFomemaServices;
        $this->authServices = $authServices;
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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
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
    /**
     * Update Special Pass Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSpecialPass(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['modified_by'] = $user['id'];
            $this->directRecruitmentPostArrivalFomemaServices->updateSpecialPass($params);
            return $this->sendSuccess(['message' => 'Special Pass Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Special Pass'], 400);
        }
    }
     /**
     * Dispaly list of workers for FOMEMA Export.
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
            $response = $this->directRecruitmentPostArrivalFomemaServices->workersListExport($params);
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
     * Dispaly FOMEMA PLKS Show.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function plksShow(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $response = $this->directRecruitmentPostArrivalFomemaServices->plksShow($params);
            if(isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to show Fomema PLKS details'], 400);
        }
    }
}

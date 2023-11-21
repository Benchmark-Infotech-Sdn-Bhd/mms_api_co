<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\DirectRecruitmentPostponedServices;
use App\Services\AuthServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class DirectRecruitmentPostponedController extends Controller
{
    /**
     * @var DirectRecruitmentPostponedServices
     */
    private $directRecruitmentPostponedServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentPostponedController constructor.
     * @param DirectRecruitmentPostponedServices $directRecruitmentPostponedServices
     * @param AuthServices $authServices
     */
    public function __construct(DirectRecruitmentPostponedServices $directRecruitmentPostponedServices, AuthServices $authServices) 
    {
        $this->directRecruitmentPostponedServices = $directRecruitmentPostponedServices;
        $this->authServices = $authServices;
    }
    /**
     * Dispaly list of workers for Postponed.
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
            $response = $this->directRecruitmentPostponedServices->workersList($params);
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
     * Dispaly list of workers for Postponed.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateExpiry(Request $request) : JsonResponse
    {
        try {
            $response = $this->directRecruitmentPostponedServices->updateCallingVisaExpiry();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
}

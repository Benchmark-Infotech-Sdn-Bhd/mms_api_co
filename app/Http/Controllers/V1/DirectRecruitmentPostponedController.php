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
     * @var DirectRecruitmentPostponedServices $directRecruitmentPostponedServices
     */
    private DirectRecruitmentPostponedServices $directRecruitmentPostponedServices;
    /**
     * @var AuthServices $authServices
     */
    private AuthServices $authServices;

    /**
     * DirectRecruitmentOnboardingCountryController constructor method.
     * 
     * @param DirectRecruitmentPostponedServices $directRecruitmentPostponedServices The instance of Direct Recruitment onBoarding country services class
     * @param AuthServices $authServices The instance od Authservices class
     */ 
    public function __construct(DirectRecruitmentPostponedServices $directRecruitmentPostponedServices, AuthServices $authServices) 
    {
        $this->directRecruitmentPostponedServices = $directRecruitmentPostponedServices;
        $this->authServices = $authServices;
    }
    /**
     * Retrieves and returns the list of direct recruitment workers list.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse The JSON response containing the list of direct recruitment Workers list.
     */
    public function workersList(Request $request) : JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $params['user'] = $user;
            $response = $this->directRecruitmentPostponedServices->workersList($params);
            if (isset($response['error']) && !empty($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
}

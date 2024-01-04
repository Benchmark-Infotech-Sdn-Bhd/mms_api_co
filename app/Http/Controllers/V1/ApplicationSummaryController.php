<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationSummaryServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Services\AuthServices;
use Exception;

class ApplicationSummaryController extends Controller
{
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Class constructor.
     *
     * @param ApplicationSummaryServices $applicationSummaryServices The application summary services.
     * @param AuthServices $authServices The authentication services.
     *
     * @return void
     */
    public function __construct(ApplicationSummaryServices $applicationSummaryServices, AuthServices $authServices)
    {
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->authServices = $authServices;
    }

    /**
     * List the application summaries.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response with the application summaries.
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $param = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $param['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->applicationSummaryServices->list($param);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Application Summary']);
        }
    }

    /**
     * List KSM Reference Number.
     *
     * @param Request $request The request object.
     *
     * @return JsonResponse The JSON response containing the list of KSM reference numbers.
     */
    public function listKsmReferenceNumber(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->applicationSummaryServices->listKsmReferenceNumber($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Ksm Reference Number']);
        }
    }
}

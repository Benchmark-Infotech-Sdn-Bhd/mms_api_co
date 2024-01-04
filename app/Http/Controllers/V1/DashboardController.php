<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DashboardServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class DashboardController extends Controller
{
    /**
     * @var DashboardServices
     */
    private $dashboardServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * DashboardController constructor.
     * @param DashboardServices $dashboardServices
     * @param AuthServices $authServices
     */
    public function __construct(DashboardServices $dashboardServices, AuthServices $authServices)
    {
        $this->dashboardServices = $dashboardServices;
        $this->authServices = $authServices;
    }
    
    /**
     * list
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $response = $this->dashboardServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Dashboard Details'], 400);
        }
    }

}
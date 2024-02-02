<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ServiceServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ServicesController extends Controller
{
    /**
     * @var ServiceServices
     */
    private $serviceServices;

    /**
     * ServicesController constructor
     * @param ServiceServices $serviceServices
     */
    public function __construct(ServiceServices $serviceServices) 
    {
        $this->serviceServices = $serviceServices;
    }

    /**
     * List the Services
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->serviceServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Services']);
        }
    }

    /**
     * Dropdown the services
     * 
     * @return JsonResponse
     */
    public function dropDown(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $user['company_id'];
            $response = $this->serviceServices->dropDown($params);
            return $this->sendSuccess($response);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Faild to List Services']);
        }
    }
}

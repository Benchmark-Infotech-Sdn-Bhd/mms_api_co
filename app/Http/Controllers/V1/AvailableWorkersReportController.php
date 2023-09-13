<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AvailableWorkersReportServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AvailableWorkersReportController extends Controller
{
    /**
     * @var AvailableWorkersReportServices
     */
    private $availableWorkersReportServices;

    /**
     * AvailableWorkersReportController constructor.
     * @param AvailableWorkersReportServices $availableWorkersReportServices
     */
    public function __construct(AvailableWorkersReportServices $availableWorkersReportServices)
    {
        $this->availableWorkersReportServices = $availableWorkersReportServices;
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
            $response = $this->availableWorkersReportServices->list($params);
            if(isset($response['validate'])){
                return $this->validationError($response['validate']); 
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Available Workers Report'], 400);
        }
    }

}
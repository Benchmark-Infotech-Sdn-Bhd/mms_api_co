<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\WorkerStatisticsReportServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class WorkerStatisticsReportController extends Controller
{
    /**
     * @var WorkerStatisticsReportServices
     */
    private $workerStatisticsReportServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * WorkerStatisticsReportController constructor.
     * @param WorkerStatisticsReportServices $workerStatisticsReportServices
     * @param AuthServices $authServices
     */
    public function __construct(WorkerStatisticsReportServices $workerStatisticsReportServices, AuthServices $authServices)
    {
        $this->workerStatisticsReportServices = $workerStatisticsReportServices;
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
            $response = $this->workerStatisticsReportServices->list($params);
            if(isset($response['validate'])){
                return $this->validationError($response['validate']); 
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Worker Statistics Report'], 400);
        }
    }

}
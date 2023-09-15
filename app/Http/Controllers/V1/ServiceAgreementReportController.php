<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ServiceAgreementReportServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ServiceAgreementReportController extends Controller
{
    /**
     * @var ServiceAgreementReportServices
     */
    private $serviceAgreementReportServices;

    /**
     * ServiceAgreementReportController constructor.
     * @param ServiceAgreementReportServices $serviceAgreementReportServices
     */
    public function __construct(ServiceAgreementReportServices $serviceAgreementReportServices)
    {
        $this->serviceAgreementReportServices = $serviceAgreementReportServices;
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
            $response = $this->serviceAgreementReportServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Service Agreement Report'], 400);
        }
    }

}
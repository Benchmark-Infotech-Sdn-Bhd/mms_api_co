<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ApplicationSummaryServices;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Exception;

class ApplicationSummaryController extends Controller
{
    /**
     * @var ApplicationSummaryServices
     */
    private $applicationSummaryServices;

    /**
     * ApplicationSummaryController constructor.
     * @param ApplicationSummaryServices $applicationSummaryServices
     */
    public function __construct(ApplicationSummaryServices $applicationSummaryServices) 
    {
        $this->applicationSummaryServices = $applicationSummaryServices;
    }
    /**
     * Display a listing of the Application Summary.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        try {
            $param = $this->getRequest($request);
            $response = $this->applicationSummaryServices->list($param);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Application Summary']);
        }
    }
    /**
     * List the Ksm Reference Number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listKsmReferenceNumber(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $response = $this->applicationSummaryServices->listKsmReferenceNumber($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Ksm Reference Number']);
        }        
    }
    }

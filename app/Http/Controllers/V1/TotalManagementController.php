<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementServices;
use Illuminate\Support\Facades\Log;
use Exception;

class TotalManagementController extends Controller
{
    /**
     * @var TotalManagementServices
     */
    private $totalManagementServices;

    /**
     * TotalManagementController constructor.
     * @param TotalManagementServices $totalManagementServices
     */
    public function __construct(TotalManagementServices $totalManagementServices)
    {
        $this->totalManagementServices = $totalManagementServices;
    }
    /** Add a services to the prospect.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function addService(Request $request): JsonResponse
    {
        try {
            $response = $this->totalManagementServices->addService($request);
            if (isset($response['error'])) {
                return $this->validationError($response['error']);
            }
            return $this->sendSuccess(['message' => 'Service Added Successfully']);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Add Service']);
        }
    }
    /** Get approved quota for particular prospect.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuota(Request $request): JsonResponse
    {
        try {
            $response = $this->totalManagementServices->getQuota($request);
            return $this->sendSuccess(['approvedQuota' => $response]);
        } catch(Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Get Quota']);
        }
    }
}
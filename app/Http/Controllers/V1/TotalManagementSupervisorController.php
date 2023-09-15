<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementSupervisorServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class TotalManagementSupervisorController extends Controller
{
    /**
     * @var TotalManagementSupervisorServices
     */
    private $TotalManagementSupervisorServices;

    /**
     * TotalManagementSupervisorController constructor.
     * @param TotalManagementSupervisorServices $totalManagementSupervisorServices
     */
    public function __construct(TotalManagementSupervisorServices $totalManagementSupervisorServices)
    {
        $this->totalManagementSupervisorServices = $totalManagementSupervisorServices;
    }
    /**
     * Display list of Supervisor
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementSupervisorServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Total Management Supervisor'], 400);
        }
    }
    /**
     * Display list of view Assignments
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function viewAssignments(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementSupervisorServices->viewAssignments($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List View Assignments'], 400);
        }
    }
}
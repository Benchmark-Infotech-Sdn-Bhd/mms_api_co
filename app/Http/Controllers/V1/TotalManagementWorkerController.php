<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementWorkerServices;
use Illuminate\Support\Facades\Log;
use Exception;

class TotalManagementWorkerController extends Controller
{
    /**
     * @var TotalManagementWorkerServices
     */
    private TotalManagementWorkerServices $totalManagementWorkerServices;

    /**
     * TotalManagementWorkerController constructor.
     * @param TotalManagementWorkerServices $totalManagementWorkerServices
     */
    public function __construct(TotalManagementWorkerServices $totalManagementWorkerServices)
    {
        $this->totalManagementWorkerServices = $totalManagementWorkerServices;
    }
    /**
     * Dispaly all the Workers with company name filter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workerListForAssignWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->workerListForAssignWorker($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
    }
    /**
     * Dispaly all Accommodation Providers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accommodationProviderDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->accommodationProviderDropDown($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Accommodation Providers'], 400);
        }
    }
    /**
     * Dispaly all Accommodation Units.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function accommodationUnitDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->accommodationUnitDropDown($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Accommodation Units'], 400);
        }
    }
    /**
     * Dispaly form for assign workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->assignWorker($params);
            return $this->sendSuccess(['message' => 'Workers are Assigned Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Assign Workers'], 400);
        }
    }
    /**
     * Display balanced quota for worker list.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBalancedQuota(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->getBalancedQuota($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Balanced Quota'], 400);
        }
    }
    /**
     * Display Company name from service.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompany(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->getCompany($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Company Name'], 400);
        }
    }
    /**
     * Display List of KSM reference number for particular company.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ksmRefereneceNUmberDropDown(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->ksmRefereneceNUmberDropDown($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List KSM Reference Number'], 400);
        }
    }
    /**
     * Display Valid Until and Sector.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSector(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->getSector($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Sector'], 400);
        }
    }
}

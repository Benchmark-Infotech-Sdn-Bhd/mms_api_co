<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementWorkerServices;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class TotalManagementWorkerController extends Controller
{
    /**
     * @var TotalManagementWorkerServices
     */
    private TotalManagementWorkerServices $totalManagementWorkerServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * TotalManagementWorkerController constructor.
     * @param TotalManagementWorkerServices $totalManagementWorkerServices
     * @param AuthServices $authServices
     */
    public function __construct(TotalManagementWorkerServices $totalManagementWorkerServices, AuthServices $authServices)
    {
        $this->totalManagementWorkerServices = $totalManagementWorkerServices;
        $this->authServices = $authServices;
    }
    /**
     * Dispaly all the Workers.
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
            $data = $this->totalManagementWorkerServices->list($params);
            if (isset($data['error'])) {
                return $this->validationError($data['error']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Workers'], 400);
        }
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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->totalManagementWorkerServices->workerListForAssignWorker($params);
            if (isset($data['error'])) {
                return $this->validationError($data['error']);
            }
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
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
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
            if(isset($data['error'])) {
                return $this->validationError($data['error']);
            } else if(isset($data['quotaError'])) {
                return $this->sendError(['message' => 'The number of worker cannot exceed the Quota'], 422);
            }
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
            return $this->sendError(['message' => 'Failed to Display Company'], 400);
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
    public function getSectorAndValidUntil(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->getSectorAndValidUntil($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Sector and Valid Until'], 400);
        }
    }

    /**
     * Display Assigned Worker List.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAssignedWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user);
            $data = $this->totalManagementWorkerServices->getAssignedWorker($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Retrive assigned Worker(s)'], 400);
        }
    }
    /**
     * Dispaly form for remove workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function removeWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementWorkerServices->removeWorker($params);
            if(isset($data['error'])) {
                return $this->validationError($data['error']);
            }
            return $this->sendSuccess(['message' => 'Worker Removed Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Remove Worker'], 400);
        }
    }
}

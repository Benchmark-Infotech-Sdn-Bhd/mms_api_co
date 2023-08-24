<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractWorkerServices;
use Illuminate\Support\Facades\Log;
use Exception;

class EContractWorkerController extends Controller
{
    /**
     * @var EContractWorkerServices
     */
    private EContractWorkerServices $eContractWorkerServices;

    /**
     * EContractWorkerController constructor.
     * @param EContractWorkerServices $eContractWorkerServices
     */
    public function __construct(EContractWorkerServices $eContractWorkerServices)
    {
        $this->eContractWorkerServices = $eContractWorkerServices;
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
            $data = $this->eContractWorkerServices->list($params);
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
     * Dispaly all the Workers under fomnext.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workerListForAssignWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->eContractWorkerServices->workerListForAssignWorker($params);
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
     * Dispaly form for assign workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function assignWorker(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->eContractWorkerServices->assignWorker($params);
            if(isset($data['error'])) {
                return $this->validationError($data['error']);
            }
            return $this->sendSuccess(['message' => 'Workers are Assigned Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Assign Workers'], 400);
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
            $data = $this->eContractWorkerServices->removeWorker($params);
            return $this->sendSuccess(['message' => 'Worker Removed Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Remove Worker'], 400);
        }
    }
}

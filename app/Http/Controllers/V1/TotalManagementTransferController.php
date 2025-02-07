<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\TotalManagementTransferServices;
use Illuminate\Support\Facades\Log;
use Exception;

class TotalManagementTransferController extends Controller
{
    /**
     * @var TotalManagementTransferServices
     */
    private TotalManagementTransferServices $totalManagementTransferServices;

    /**
     * TotalManagementTransferController constructor.
     * @param TotalManagementTransferServices $totalManagementTransferServices
     */
    public function __construct(TotalManagementTransferServices $totalManagementTransferServices)
    {
        $this->totalManagementTransferServices = $totalManagementTransferServices;
    }
    /**
     * worker Employment Detail
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workerEmploymentDetail(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementTransferServices->workerEmploymentDetail($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Worker Employment Details'], 400);
        }
    }
    /**
     * company list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function companyList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementTransferServices->companyList($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Company'], 400);
        }
    }
    /**
     * project list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function projectList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->totalManagementTransferServices->projectList($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Project'], 400);
        }
    }
    /**
     * Transfer Submit.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->totalManagementTransferServices->submit($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['projectExist'])){
                return $this->sendError(['message' => 'Selected Worker is already working in this project'], 422);
            }
            return $this->sendSuccess(['message' => 'Worker Transfered Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Transfer Worker'], 400);
        }
    }
}

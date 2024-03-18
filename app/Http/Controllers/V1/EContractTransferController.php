<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\EContractTransferServices;
use Illuminate\Support\Facades\Log;
use Exception;

class EContractTransferController extends Controller
{
     /**
     * @var EContractTransferServices
     */
    private EContractTransferServices $eContractTransferServices;

    /**
     * EContractTransferController constructor.
     * @param EContractTransferServices $eContractTransferServices
     */
    public function __construct(EContractTransferServices $eContractTransferServices)
    {
        $this->eContractTransferServices = $eContractTransferServices;
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
            $data = $this->eContractTransferServices->companyList($params);
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
            $data = $this->eContractTransferServices->projectList($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Project'], 400);
        }
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
            $data = $this->eContractTransferServices->workerEmploymentDetail($params);
            if(is_null($data) || count($data->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Show Worker Employment Details'], 400);
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
            $response = $this->eContractTransferServices->submit($params);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            } else if(isset($response['projectExist'])){
                return $this->sendError(['message' => 'Selected Worker is already working in this project'], 422);
            } else if(isset($response['quotaError'])) {
                return $this->sendError(['message' => 'The number of worker cannot exceed the Applied Quota'], 422);
            } else if(isset($response['quotaFromExistingError'])) {
                return $this->sendError(['message' => 'Cannot Transfer worker to a From Existing Project'], 422);
            } else if(isset($response['fomnextQuotaError'])) {
                return $this->sendError(['message' => 'The number of Fomnext worker cannot exceed the Fomnext Quota'], 422);
            } else if(isset($response['otherCompanyError'])) {
                return $this->sendError(['message' => 'The selected Client worker cannot be transferred to another Client'], 422);
            } else if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'Worker Transfered Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Transfer Worker'], 400);
        }
    }
}

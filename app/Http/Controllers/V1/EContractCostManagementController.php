<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\EContractCostManagementServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class EContractCostManagementController extends Controller
{
    /**
     * @var EContractCostManagementServices
     */
    private EContractCostManagementServices $eContractCostManagementServices;

    /**
     * EContractCostManagementServices constructor.
     * @param EContractCostManagementServices $eContractCostManagementServices
     */
    public function __construct(EContractCostManagementServices $eContractCostManagementServices)
    {
        $this->eContractCostManagementServices = $eContractCostManagementServices;
    }
    /**
     * create a cost management
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->eContractCostManagementServices->create($request);
            if(isset($data['unauthorizedError'])) {
                return $this->sendError($data['unauthorizedError']);
            }
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess(['message' => 'E-Contract Cost Management Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * update the cost management.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $data = $this->eContractCostManagementServices->update($request);
            if(isset($data['unauthorizedError'])) {
                return $this->sendError($data['unauthorizedError']);
            }
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess(['message' => 'E-Contract Cost Management Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    /**
     * show the cost management
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->eContractCostManagementServices->show($params);
            if(is_null($data) || count($data->toArray()) == 0){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    
    /**
     * list the cost management.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->eContractCostManagementServices->list($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }   

    /**
     * delete the cost management.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {  
        try {
            $params = $this->getRequest($request);
            $response = $this->eContractCostManagementServices->delete($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete Cost Management was failed']);
        }  
    }

    /**
     * delete attachment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $response = $this->eContractCostManagementServices->deleteAttachment($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    } 
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DispatchManagementServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class DispatchManagementController extends Controller
{
    /**
     * @var DispatchManagementServices
     */
    private $dispatchManagementServices;

    /**
     * DispatchManagementController constructor.
     * @param DispatchManagementServices $dispatchManagementServices
     */
    public function __construct(DispatchManagementServices $dispatchManagementServices)
    {
        $this->dispatchManagementServices = $dispatchManagementServices;
    }
    /**
     * listing
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->dispatchManagementServices->list($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to List Dispatch'], 400);
        }
    }
    /**
     * show
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $response = $this->dispatchManagementServices->show($params);
            if(is_null($response)){
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Display Dispatch'], 400);
        }
    }
    /**
     * create
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $response = $this->dispatchManagementServices->create($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'Dispatch Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Create Dispatch'], 400);
        }
    }
    /**
     * Update
     * 
     * @param Request $request
     * @return JsonResponse   
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $response = $this->dispatchManagementServices->update($request);
            if(isset($response['error'])) {
                return $this->validationError($response['error']);
            }else if(isset($response['unauthorizedError'])) {
                return $this->sendError(['message' => 'Unauthorized']);
            }
            return $this->sendSuccess(['message' => 'Dispatch Updated Successfully']);
        } catch (Exception $e) {
            Log::error('Error = ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Failed to Update Dispatch'], 400);
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
            $response = $this->dispatchManagementServices->deleteAttachment($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()]);
        }        
    }
}
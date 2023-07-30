<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\WorkersServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkersController extends Controller
{
    /**
     * @var workersServices
     */
    private WorkersServices $workersServices;

    /**
     * WorkersController constructor.
     * @param WorkersServices $workersServices
     */
    public function __construct(WorkersServices $workersServices)
    {
        $this->workersServices = $workersServices;
    }
    /**
     * Show the form for creating a new Worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->workersServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            } else if(isset($data['ksmError'])) {
                return $this->sendError(['message' => 'KSM reference number does not matched.'], 422);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Show the form for creating a new Worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            
            $data = $this->workersServices->update($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            } else if(isset($data['ksmError'])) {
                return $this->sendError(['message' => 'KSM reference number does not matched.'], 422);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    
    /**
     * Retrieve the specified Worker.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->workersServices->show($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    
    
    /**
     * Search & Retrieve all the Workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->workersServices->list($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Search & Export the Workers.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->workersServices->export($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Worker dropdown.
     *
     * @return JsonResponse
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $data = $this->workersServices->dropdown($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Worker status.
     *
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $data = $this->workersServices->updateStatus($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Worker dropdown.
     *
     * @return JsonResponse
     */
    public function kinRelationship(): JsonResponse
    {
        try {
            $data = $this->workersServices->kinRelationship();
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Onboarding Agent dropdown.
     *
     * @return JsonResponse
     */
    public function onboardingAgent(Request $request): JsonResponse
    {
        try {
            $data = $this->workersServices->onboardingAgent($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Worker Replace.
     *
     * @return JsonResponse
     */
    public function replaceWorker(Request $request): JsonResponse
    {
        try {
            $data = $this->workersServices->replaceWorker($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }

    /**
     * Search & Retrieve Worker StatusList.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function workerStatusList(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->workersServices->workerStatusList($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    
}

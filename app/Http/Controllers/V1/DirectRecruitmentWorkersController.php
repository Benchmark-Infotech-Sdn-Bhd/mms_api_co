<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DirectRecruitmentWorkersServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class DirectRecruitmentWorkersController extends Controller
{
    /**
     * @var DirectRecruitmentWorkersServices
     */
    private DirectRecruitmentWorkersServices $directRecruitmentWorkersServices;

    /**
     * DirectRecruitmentWorkersController constructor.
     * @param DirectRecruitmentWorkersServices $directRecruitmentWorkersServices
     */
    public function __construct(DirectRecruitmentWorkersServices $directRecruitmentWorkersServices)
    {
        $this->directRecruitmentWorkersServices = $directRecruitmentWorkersServices;
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
            $data = $this->directRecruitmentWorkersServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            } else if(isset($data['ksmError'])) {
                return $this->sendError(['message' => 'KSM reference number does not matched.'], 422);
            } else if(isset($data['workerCountError'])) {
                return $this->sendError(['message' => 'The number of worker should not exceed to Approved Quota'], 422);
            } else if(isset($data['ksmCountError'])) {
                return $this->sendError(['message' => 'The number of worker should not exceed to KSM Reference Number Approved Quota'], 422);
            } else if(isset($data['agentQuotaError'])) {
                return $this->sendError(['message' => 'The number of worker should not exceed to the Approved Quota of Agent'], 422);
            } else if($data == false) {
                return $this->sendError(['message' => 'Creation failed. Please retry.'], 422);
            }
            return $this->sendSuccess(['message' => 'Worker Created Successfully']);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Creation failed. Please retry.';
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
            $data = $this->directRecruitmentWorkersServices->list($params);
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
            $data = $this->directRecruitmentWorkersServices->export($params);
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
            $data = $this->directRecruitmentWorkersServices->dropdown($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
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
            
            $data = $this->directRecruitmentWorkersServices->update($request);
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
     * Worker dropdown.
     *
     * @return JsonResponse
     */
    public function kinRelationship(): JsonResponse
    {
        try {
            $data = $this->directRecruitmentWorkersServices->kinRelationship();
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
            $data = $this->directRecruitmentWorkersServices->onboardingAgent($request);
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
            $data = $this->directRecruitmentWorkersServices->replaceWorker($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
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
            $data = $this->directRecruitmentWorkersServices->show($params);
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
     * Import the Workers from Excel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        try {

            $originalFilename = $request->file('worker_file')->getClientOriginalName();
            $originalFilename_arr = explode('.', $originalFilename);
            $fileExt = end($originalFilename_arr);
            $destinationPath = storage_path('upload/worker/');
            $fileName = 'A-' . time() . '.' . $fileExt;
            $request->file('worker_file')->move($destinationPath, $fileName);
            
            $this->directRecruitmentWorkersServices->import($request, $destinationPath . $fileName);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }

            return $this->sendSuccess(['message' => "Successfully worker was imported"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Import failed. Please retry.';
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
            $data = $this->directRecruitmentWorkersServices->workerStatusList($params);
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
     * Worker status.
     *
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $data = $this->directRecruitmentWorkersServices->updateStatus($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Update failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    /**
     * list Import details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function importHistory(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->directRecruitmentWorkersServices->importHistory($params);
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
     * KSM Reference number dropdown based on onboarding agent.
     *
     * @return JsonResponse
     */
    public function ksmDropDownBasedOnOnboardingAgent(Request $request): JsonResponse
    {
        try {
            $data = $this->directRecruitmentWorkersServices->ksmDropDownBasedOnOnboardingAgent($request);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
    /**
     * list failure cases
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function failureExport(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->directRecruitmentWorkersServices->failureExport($params);
            if (isset($data['queueError'])) {
                return $this->sendError(['message' => 'Import is in Progress'], 400);
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Failed to Export. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }
    }
}

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
            }else if($data == false) {
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
    
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ManageWorkersServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class ManageWorkersController extends Controller
{
    /**
     * @var workersServices
     */
    private WorkersServices $workersServices;

    /**
     * ManageWorkersController constructor.
     * @param ManageWorkersServices $manageWorkersServices
     */
    public function __construct(ManageWorkersServices $manageWorkersServices)
    {
        $this->manageWorkersServices = $manageWorkersServices;
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
            $data = $this->manageWorkersServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
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
            
            $data = $this->manageWorkersServices->update($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
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
            $data = $this->manageWorkersServices->show($params);
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
            $data = $this->manageWorkersServices->list($params);
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
            
            $this->manageWorkersServices->import($request, $destinationPath . $fileName);
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
     * Export Template for Workers Import from Excel.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportTemplate(Request $request): JsonResponse
    {
        try {            
            $this->manageWorkersServices->exportTemplate($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }

            return $this->sendSuccess(['message' => "Successfully worker template was exported"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Export failed. Please retry.';
            return $this->sendError(['message' => $data['error']], 400);
        }

    }

}

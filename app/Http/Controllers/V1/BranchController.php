<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\BranchServices;
use Exception;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    /**
     * @var branchServices
     */
    private BranchServices $branchServices;
    /**
     * branchServices constructor.
     * @param BranchServices $branchServices
     */
    public function __construct(BranchServices $branchServices)
    {
        $this->branchServices = $branchServices;
    }
	 /**
     * Show the form for creating a new Branch.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $validation = $this->branchServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->branchServices->create($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Branch creation was failed']);
        }
    }
	 /**
     * Display a listing of the Branch.
     *
     * @return JsonResponse
     */    
    public function retrieveAll(): JsonResponse
    {     
        try {   
            $response = $this->branchServices->retrieveAll(); 
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all branch data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using branch id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {   
        try {
            $response = $this->branchServices->retrieve($request); 
            return $this->sendSuccess($response);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve branch Data was failed']);
        }  
    } 
	 /**
     * Update the specified branch data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {    
            $validation = $this->branchServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->branchServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Branch update was failed']);
        }
    }
	 /**
     * delete the specified Branch data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {     
        try { 
            $params = $this->getRequest($request);
            $response = $this->branchServices->delete($params);
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete branch was failed']);
        } 
    }
    /**
     * searching branch data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $response = $this->branchServices->search($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Search branch data was failed']);
        }
    }
}

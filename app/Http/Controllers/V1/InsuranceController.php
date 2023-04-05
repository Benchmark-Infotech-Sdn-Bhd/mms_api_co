<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\InsuranceServices;
use Exception;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    /**
     * @var insuranceServices
     */
    private InsuranceServices $insuranceServices;
    /**
     * InsuranceServices constructor.
     * @param InsuranceServices $insuranceServices
     */
    public function __construct(InsuranceServices $insuranceServices)
    {
        $this->insuranceServices = $insuranceServices;
    }
	 /**
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validation = $this->insuranceServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->insuranceServices->create($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Insurance creation was failed']);
        }
    }
	 /**
     * Display a listing of the Insurance.
     * @param Request $request
     * @return JsonResponse
     */    
    public function list(Request $request): JsonResponse
    {     
        try {              
            $params = $this->getRequest($request);
            $response = $this->insuranceServices->list($params); 
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all insurance data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {   
        try {
            // $insurance = Insurance::find($id);
            // $vendors = $insurance->vendor;
            $response = $this->insuranceServices->show($request); 
            return $this->sendSuccess($response);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve insurance Data was failed']);
        }  
    } 
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {    
            $validation = $this->insuranceServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->insuranceServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Insurance update was failed']);
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {     
        try { 
            $params = $this->getRequest($request);
            $response = $this->insuranceServices->delete($params);
            return $this->sendSuccess($response); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete insurance was failed']);
        } 
    }
}

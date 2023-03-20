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
            $this->insuranceServices->create($request);
            return $this->sendSuccess(['message' => "Successfully insurance was created"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Insurance creation was failed']);
        }
    }
	 /**
     * Display a listing of the Insurance.
     *
     * @return JsonResponse
     */    
    public function retrieveAll(): JsonResponse
    {     
        try {   
            // $insurance = Insurance::paginate(10);
            $response = $this->insuranceServices->retrieveAll(); 
            return $this->sendSuccess(['data' => $response]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Show insurance was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {   
        try {
            // $insurance = Insurance::find($id);
            // $vendors = $insurance->vendor;
            $response = $this->insuranceServices->retrieve($request); 
            return $this->sendSuccess(['data' => $response]);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Edit insurance was failed']);
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
            $validation = $this->insuranceServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->insuranceServices->update($request); 
            return $this->sendSuccess(['message' => "Successfully insurance was updated"]);
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
            $this->insuranceServices->delete($params);
            return $this->sendSuccess(['message' => "Successfully insurance was deleted"]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'delete insurance was failed']);
        } 
    }
    /**
     * searching Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $response = $this->insuranceServices->search($request);
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'search insurance was failed']);
        }
    }
}

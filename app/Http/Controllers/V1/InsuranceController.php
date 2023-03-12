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
    private $insuranceServices;
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
    public function createInsurance(Request $request)
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
            $this->sendError(['message' => 'Insurance creation was failed']);
        }
    }
	 /**
     * Display a listing of the Insurance.
     *
     * @return JsonResponse
     */    
    public function showInsurance(): JsonResponse
    {     
        try {   
            // $insurance = Insurance::paginate(10);
            $response = $this->insuranceServices->show(); 
            return $this->sendSuccess(['data' => $response]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Show insurance was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editInsurance($id): JsonResponse
    {   
        try {
            // $insurance = Insurance::find($id);
            // $vendors = $insurance->vendor;
            $response = $this->insuranceServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);  
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Edit insurance was failed']);
        }  
    } 
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateInsurance(Request $request, $id): JsonResponse
    {    
        try {    
            $validation = $this->insuranceServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->insuranceServices->updateData($id, $request); 
            return $this->sendSuccess(['message' => "Successfully insurance was updated"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Insurance update was failed']);
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteInsurance($id): JsonResponse
    {     
        try { 
            $this->insuranceServices->delete($id);
            return $this->sendSuccess(['message' => "Successfully insurance was deleted"]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'delete insurance was failed']);
        } 
    }
    /**
     * searching Insurance data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchInsurance(Request $request): JsonResponse
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

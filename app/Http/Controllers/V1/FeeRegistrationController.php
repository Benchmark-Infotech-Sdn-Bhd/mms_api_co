<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FeeRegistrationServices;
use Exception;
use Illuminate\Support\Facades\Log;

class FeeRegistrationController extends Controller
{
    /**
     * @var feeRegistrationServices
     */
    private FeeRegistrationServices $feeRegistrationServices;
    /**
     * FeeRegistrationServices constructor.
     * @param FeeRegistrationServices $feeRegistrationServices
     */
    public function __construct(FeeRegistrationServices $feeRegistrationServices)
    {
        $this->feeRegistrationServices = $feeRegistrationServices;
    }

    /**
     * Show the form for creating a new Fee Registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validation = $this->feeRegistrationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->feeRegistrationServices->create($request);
            return $this->sendSuccess(['message' => "Successfully Fee Registration was created"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'FOMEMA Clinics creation was failed']);
        }
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function retrieveAll(): JsonResponse
    {      
        try {  
            $response = $this->feeRegistrationServices->retrieveAll(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Show fee registration was failed']);
        }
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {    
        try {
            $params = $this->getRequest($request);
            $response = $this->feeRegistrationServices->retrieve($params); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Edit fee registration was failed']);
        } 
    } 
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {             
        try {   
            $validation = $this->feeRegistrationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->feeRegistrationServices->update($request); 
            return $this->sendSuccess(['message' => "Successfully Fee Registration was updated"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Fee Registration update was failed']);
        }
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {      
        try {
            $params = $this->getRequest($request);
            $this->feeRegistrationServices->delete($params);
            return $this->sendSuccess(['message' => "Successfully Fee Registration was deleted"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'delete insurance was failed']);
        } 
    }

    /**
     * searching Fee Registration data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $response = $this->feeRegistrationServices->search($request);
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'search Fee Registration was failed']);
        }
    }
    
}

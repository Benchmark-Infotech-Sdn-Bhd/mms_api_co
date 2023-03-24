<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\TransportationServices;
use Exception;
use Illuminate\Support\Facades\Log;


class TransportationController extends Controller
{
    /**
     * @var transportationServices
     */
    private TransportationServices $transportationServices;
    /**
     * TransportationServices constructor.
     * @param TransportationServices $transportationServices
     */
    public function __construct(TransportationServices $transportationServices)
    {
        $this->transportationServices = $transportationServices;
    }
	 /**
     * Show the form for creating a new Transportation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validation = $this->transportationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->transportationServices->create($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Transportation creation was failed']);
        }
    }
	 /**
     * Display a listing of the Transportation.
     *
     * @return JsonResponse
     */    
    public function retrieveAll(): JsonResponse
    {        
        try {
            $response = $this->transportationServices->retrieveAll(); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all transportation data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {      
        try {
            $response = $this->transportationServices->retrieve($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve transportation data was failed']);
        }
    } 
	 /**
     * Update the specified Transportation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {   
        try {     
            $validation = $this->transportationServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->transportationServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Transportation update was failed']);
        }
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {     
        try {  
            $params = $this->getRequest($request);
            $response = $this->transportationServices->delete($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete transportation was failed']);
        }
    }
    /**
     * searching transportation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $response = $this->transportationServices->search($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Search transportation was failed']);
        }
    }
}

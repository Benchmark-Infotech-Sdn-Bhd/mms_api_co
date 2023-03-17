<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AccommodationServices;
use Exception;
use Illuminate\Support\Facades\Log;

class AccommodationController extends Controller
{
    /**
     * @var accommodationServices
     */
    private AccommodationServices $accommodationServices;
    /**
     * AccommodationServices constructor.
     * @param AccommodationServices $accommodationServices
     */
    public function __construct(AccommodationServices $accommodationServices)
    {
        $this->accommodationServices = $accommodationServices;
    }
    /**
     * Show the form for creating a new Accommodation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            
            $validation = $this->accommodationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->accommodationServices->create($request); 
            return $this->sendSuccess(['message' => "Successfully Accommodation was created"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Accommodation creation was failed']);
        }
    }
    
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function retrieveAll(): JsonResponse
    {        
        try {
            $response = $this->accommodationServices->retrieveAll(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Show accommodation was failed']);
        }
    }

    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {     
        try {
            $params = $this->getRequest($request);
            $response = $this->accommodationServices->retrieve($params); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Edit accommodation was failed']);
        }
    } 
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {  
        try {
            $validation = $this->accommodationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->accommodationServices->update($request); 
            return $this->sendSuccess(['message' => "Successfully Accommodation was updated"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Accommodation update was failed']);
        }
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {   
        try {
            $this->accommodationServices->delete($request);
            return $this->sendSuccess(['message' => "Successfully Accommodation was deleted"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete accommodation was failed']);
        }        
    }
    /**
     * searching Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {          
        try{
            $response = $this->accommodationServices->search($request); 
            return $this->sendSuccess(['data' => $response]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'search accommodation was failed']);
        }        
    }

}

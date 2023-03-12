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
    public function createAccommodation(Request $request): JsonResponse
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
            $this->sendError(['message' => 'Accommodation creation was failed']);
        }
    }
    
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function showAccommodation(): JsonResponse
    {        
        try {
            $response = $this->accommodationServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Show accommodation was failed']);
        }
    }

    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editAccommodation($id): JsonResponse
    {     
        try {
            $response = $this->accommodationServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Edit accommodation was failed']);
        }
    } 
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateAccommodation(Request $request, $id): JsonResponse
    {  
        try {
            $validation = $this->accommodationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->accommodationServices->update($id, $request); 
            return $this->sendSuccess(['message' => "Successfully Accommodation was updated"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Accommodation update was failed']);
        }
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteAccommodation($id): JsonResponse
    {   
        try {
            $this->accommodationServices->delete($id);
            return $this->sendSuccess(['message' => "Successfully Accommodation was deleted"]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Delete accommodation was failed']);
        }        
    }
    /**
     * searching Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAccommodation(Request $request): JsonResponse
    {          
        try{
            $response = $this->accommodationServices->search($request); 
            return $this->sendSuccess(['data' => $response]); 
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'search accommodation was failed']);
        }        
    }

}

<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\AccommodationServices;
use App\Models\Accommodation;
use Illuminate\Support\Facades\Validator;

class AccommodationController extends Controller
{
    /**
     * @var accommodationServices
     */
    private $accommodationServices;

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
    public function createAccommodation(Request $request)
    {
        $validation = $this->accommodationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->accommodationServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Accommodation was created"]);
        } else {
            return $this->sendError(['message' => "Accommodation creation was failed"]);
        }
    }
    
    /**
     * Display a listing of the Accommodation.
     *
     * @return JsonResponse
     */
    public function showAccommodation()
    {        
        $response = $this->accommodationServices->show(); 
		return $this->sendSuccess(['data' => $response]);
    }

    /**
     * Display the data for edit form by using accommodation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editAccommodation($id)
    {     
        $response = $this->accommodationServices->edit($id); 
		return $this->sendSuccess(['data' => $response]);
    } 
    /**
     * Update the specified Accommodation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateAccommodation(Request $request, $id)
    {  
        $validation = $this->accommodationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->accommodationServices->update($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Accommodation was updated"]);
        } else {
            return $this->sendError(['message' => 'Accommodation update was failed']);
        }
    }
    /**
     * delete the specified Accommodation data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteAccommodation($id)
    {   
        $response = $this->accommodationServices->delete($id); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Accommodation was deleted"]);
        } else {
            return $this->sendError(['message' => 'Accommodation delete was failed']);
        }       
    }
    /**
     * searching Accommodation data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAccommodation(Request $request)
    {          
        $response = $this->accommodationServices->search($request); 
		return $this->sendSuccess(['data' => $response]);        
    }

}

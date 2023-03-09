<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FomemaClinicsServices;
use App\Models\FomemaClinics;
use Illuminate\Support\Facades\Validator;

class FomemaClinicsController extends Controller
{
    /**
     * @var fomemaClinicsServices
     */
    private $fomemaClinicsServices;

    public function __construct(FomemaClinicsServices $fomemaClinicsServices)
    {
        $this->fomemaClinicsServices = $fomemaClinicsServices;
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFomemaClinics(Request $request)
    {
        $validation = $this->fomemaClinicsServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->fomemaClinicsServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was created"]);
        } else {
            return $this->sendError(['message' => "FOMEMA Clinics creation was failed"]);
        }
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */    
    public function showFomemaClinics()
    {        
        $response = $this->fomemaClinicsServices->show(); 
		return $this->sendSuccess(['data' => $response]);
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFomemaClinics($id)
    {   
        $response = $this->fomemaClinicsServices->edit($id); 
		return $this->sendSuccess(['data' => $response]);
    } 
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFomemaClinics(Request $request, $id)
    {                
        $validation = $this->fomemaClinicsServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->fomemaClinicsServices->updateData($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was updated"]);
        } else {
            return $this->sendError(['message' => "FOMEMA Clinics creation was failed"]);
        }
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteFomemaClinics($id)
    {    
        $response = $this->fomemaClinicsServices->delete($id); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was deleted"]);
        } else {
            return $this->sendError(['message' => 'FOMEMA Clinics delete was failed']);
        }         
    }
    
}

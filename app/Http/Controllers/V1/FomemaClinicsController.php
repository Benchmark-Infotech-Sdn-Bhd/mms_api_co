<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FomemaClinicsServices;
use Exception;

class FomemaClinicsController extends Controller
{
    /**
     * @var fomemaClinicsServices
     */
    private $fomemaClinicsServices;
    /**
     * FomemaClinicsServices constructor.
     * @param FomemaClinicsServices $fomemaClinicsServices
     */
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
        try {
            $validation = $this->fomemaClinicsServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->fomemaClinicsServices->create($request); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was created"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'FOMEMA Clinics creation was failed']);
        }
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */    
    public function showFomemaClinics()
    {     
        try {   
            $response = $this->fomemaClinicsServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Show FOMEMA Clinics was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFomemaClinics($id)
    {   
        try {
            $response = $this->fomemaClinicsServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Edit FOMEMA Clinics was failed']);
        } 
    } 
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFomemaClinics(Request $request, $id)
    {    
        try {            
            $validation = $this->fomemaClinicsServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->fomemaClinicsServices->updateData($id, $request); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was updated"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'FOMEMA Clinics update was failed']);
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
        try {
            $this->fomemaClinicsServices->delete($id); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was deleted"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'delete insurance was failed']);
        }         
    }
    
}

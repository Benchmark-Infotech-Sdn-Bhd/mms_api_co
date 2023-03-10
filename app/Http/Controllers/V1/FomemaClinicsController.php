<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FomemaClinicsServices;
use Exception;
use Illuminate\Support\Facades\Log;

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
    public function createFomemaClinics(Request $request): JsonResponse
    {
        try {
            $validation = $this->fomemaClinicsServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->fomemaClinicsServices->create($request); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was created"]);
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'FOMEMA Clinics creation was failed']);
        }
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @return JsonResponse
     */    
    public function showFomemaClinics(): JsonResponse
    {     
        try {   
            $response = $this->fomemaClinicsServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Show FOMEMA Clinics was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFomemaClinics($id): JsonResponse
    {   
        try {
            $response = $this->fomemaClinicsServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'Edit FOMEMA Clinics was failed']);
        } 
    } 
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFomemaClinics(Request $request, $id): JsonResponse
    {    
        try {            
            $validation = $this->fomemaClinicsServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->fomemaClinicsServices->updateData($id, $request); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was updated"]);
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'FOMEMA Clinics update was failed']);
        }
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteFomemaClinics($id): JsonResponse
    {    
        try {
            $this->fomemaClinicsServices->delete($id); 
            return $this->sendSuccess(['message' => "Successfully FOMEMA Clinics was deleted"]);
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $this->sendError(['message' => 'delete insurance was failed']);
        }         
    }

    /**
     * searching FOMEMA Clinics data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchFomemaClinics(Request $request): JsonResponse
    {
        try{
            $response = $this->fomemaClinicsServices->search($request);
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'search Fomema Clinics was failed']);
        }
    }
    
}

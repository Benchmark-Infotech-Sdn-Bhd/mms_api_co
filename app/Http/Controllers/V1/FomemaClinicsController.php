<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FomemaClinicsServices;
use Exception;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;

class FomemaClinicsController extends Controller
{
    /**
     * @var fomemaClinicsServices
     */
    private FomemaClinicsServices $fomemaClinicsServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * FomemaClinicsServices constructor.
     * @param FomemaClinicsServices $fomemaClinicsServices
     * @param AuthServices $authServices
     */
    public function __construct(FomemaClinicsServices $fomemaClinicsServices, AuthServices $authServices)
    {
        $this->fomemaClinicsServices = $fomemaClinicsServices;
        $this->authServices = $authServices;
    }
	 /**
     * Show the form for creating a new Fomema Clinics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validation = $this->fomemaClinicsServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->fomemaClinicsServices->create($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'FOMEMA Clinics creation was failed']);
        }
    }
	 /**
     * Display a listing of the Fomema Clinics.
     *
     * @param Request $request
     * @return JsonResponse
     */    
    public function list(Request $request): JsonResponse
    {     
        try {  
            $params = $this->getRequest($request);
            $user = JWTAuth::parseToken()->authenticate();
            $params['company_id'] = $this->authServices->getCompanyIds($user); 
            $response = $this->fomemaClinicsServices->list($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve All FOMEMA Clinics data was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Fomema Clinic id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $response = $this->fomemaClinicsServices->show($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve FOMEMA Clinics data was failed']);
        } 
    } 
	 /**
     * Update the specified Fomema Clinic data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {            
            $validation = $this->fomemaClinicsServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->fomemaClinicsServices->update($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'FOMEMA Clinics update was failed']);
        }
    }
	 /**
     * delete the specified FomemaClinic data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {    
        try {
            $params = $this->getRequest($request);
            $response = $this->fomemaClinicsServices->delete($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete Fomema Clinics was failed']);
        }         
    }
    
}

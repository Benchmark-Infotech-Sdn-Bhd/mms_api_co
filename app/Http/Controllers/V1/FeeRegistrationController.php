<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FeeRegistrationServices;
use Exception;

class FeeRegistrationController extends Controller
{
    /**
     * @var feeRegistrationServices
     */
    private $feeRegistrationServices;
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
    public function createFeeRegistration(Request $request)
    {
        try {
            $validation = $this->feeRegistrationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->feeRegistrationServices->create($request);
            return $this->sendSuccess(['message' => "Successfully Fee Registration was created"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'FOMEMA Clinics creation was failed']);
        }
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function showFeeRegistration()
    {      
        try {  
            $response = $this->feeRegistrationServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Show fee registration was failed']);
        }
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFeeRegistration($id)
    {    
        try {
            $response = $this->feeRegistrationServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Edit fee registration was failed']);
        } 
    } 
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFeeRegistration(Request $request, $id)
    {             
        try {   
            $validation = $this->feeRegistrationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->feeRegistrationServices->updateData($id, $request); 
            return $this->sendSuccess(['message' => "Successfully Fee Registration was updated"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Fee Registration update was failed']);
        }
    }
	 /**
     * delete the specified Fee Registration data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteFeeRegistration($id)
    {      
        try {
            $this->feeRegistrationServices->delete($id);
            return $this->sendSuccess(['message' => "Successfully Fee Registration was deleted"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'delete insurance was failed']);
        } 
    }
    
}

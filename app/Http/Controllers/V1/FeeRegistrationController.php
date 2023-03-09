<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FeeRegistrationServices;
use App\Models\FeeRegistration;
use Illuminate\Support\Facades\Validator;

class FeeRegistrationController extends Controller
{
    /**
     * @var feeRegistrationServices
     */
    private $feeRegistrationServices;

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
        $validation = $this->feeRegistrationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->feeRegistrationServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Fee Registration was created"]);
        } else {
            return $this->sendError(['message' => "Fee Registration creation was failed"]);
        }
    }
    /**
     * Display a listing of the Fee Registration data.
     *
     * @return JsonResponse
     */
    public function showFeeRegistration()
    {        
        $response = $this->feeRegistrationServices->show(); 
		return $this->sendSuccess(['data' => $response]);
    }
    /**
     * Display the data for edit form by using feeRegistration id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editFeeRegistration($id)
    {    
        $response = $this->feeRegistrationServices->edit($id); 
		return $this->sendSuccess(['data' => $response]);
    } 
	 /**
     * Update the specified Fee Registration data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateFeeRegistration(Request $request, $id)
    {                
        $validation = $this->feeRegistrationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->feeRegistrationServices->updateData($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Fee Registration was updated"]);
        } else {
            return $this->sendError(['message' => 'Fee registration update was failed']);
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
        $response = $this->feeRegistrationServices->delete($id); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Fee Registration was deleted"]);
        } else {
            return $this->sendError(['message' => 'Fee Registration delete was failed']);
        } 
    }
    
}

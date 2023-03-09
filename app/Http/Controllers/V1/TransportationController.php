<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\TransportationServices;
use App\Models\Transportation;
use Illuminate\Support\Facades\Validator;

class TransportationController extends Controller
{
    /**
     * @var transportationServices
     */
    private $transportationServices;

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
    public function createTransportation(Request $request)
    {
        $validation = $this->transportationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->transportationServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully transportation was created"]);
        } else {
            return $this->sendError(['message' => "Transportation creation was failed"]);
        }
    }
	 /**
     * Display a listing of the Transportation.
     *
     * @return JsonResponse
     */    
    public function showTransportation()
    {        
        $response = $this->transportationServices->show(); 
        return $this->sendSuccess(['data' => $response]);
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editTransportation($id)
    {      
        $response = $this->transportationServices->edit($id); 
        return $this->sendSuccess(['data' => $response]);
    } 
	 /**
     * Update the specified Transportation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateTransportation(Request $request, $id)
    {        
        $validation = $this->transportationServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->transportationServices->updateData($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully transportation was updated"]);
        } else {
            return $this->sendError(['message' => 'Transportation update was failed']);
        }
    }
	 /**
     * delete the specified Transportation data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteTransportation($id)
    {       
        $response = $this->transportationServices->delete($id); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully transportation was deleted"]);
        } else {
            return $this->sendError(['message' => 'Transportation delete was failed']);
        }
    }
}

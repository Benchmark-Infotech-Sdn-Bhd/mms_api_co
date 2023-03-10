<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\TransportationServices;
use Exception;


class TransportationController extends Controller
{
    /**
     * @var transportationServices
     */
    private $transportationServices;
    /**
     * TransportationServices constructor.
     * @param TransportationServices $transportationServices
     */
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
        try {
            $validation = $this->transportationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->transportationServices->create($request); 
            return $this->sendSuccess(['message' => "Successfully transportation was created"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Transportation creation was failed']);
        }
    }
	 /**
     * Display a listing of the Transportation.
     *
     * @return JsonResponse
     */    
    public function showTransportation()
    {        
        try {
            $response = $this->transportationServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Show transportation was failed']);
        }
    }
	 /**
     * Display the data for edit form by using Transportation id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editTransportation($id)
    {      
        try {
            $response = $this->transportationServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Edit transportation was failed']);
        }
    } 
	 /**
     * Update the specified Transportation data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateTransportation(Request $request, $id)
    {   
        try {     
            $validation = $this->transportationServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->transportationServices->updateData($id, $request); 
            return $this->sendSuccess(['message' => "Successfully transportation was updated"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Transportation update was failed']);
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
        try {  
            $this->transportationServices->delete($id); 
            return $this->sendSuccess(['message' => "Successfully transportation was deleted"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'delete transportation was failed']);
        }
    }
}

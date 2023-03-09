<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\InsuranceServices;
use App\Models\Insurance;
use Illuminate\Support\Facades\Validator;

class InsuranceController extends Controller
{
    /**
     * @var insuranceServices
     */
    private $insuranceServices;

    public function __construct(InsuranceServices $insuranceServices)
    {
        $this->insuranceServices = $insuranceServices;
    }
	 /**
     * Show the form for creating a new Insurance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createInsurance(Request $request)
    {
        $validation = $this->insuranceServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->insuranceServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully insurance was created"]);
        } else {
            return $this->sendError(['message' => "Insurance creation was failed"]);
        }
    }
	 /**
     * Display a listing of the Insurance.
     *
     * @return JsonResponse
     */    
    public function showInsurance()
    {        
        // $insurance = Insurance::paginate(10);
        $response = $this->insuranceServices->show(); 
        return $this->sendSuccess(['data' => $response]); 
    }
	 /**
     * Display the data for edit form by using Insurance id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editInsurance($id)
    {   
        // $insurance = Insurance::find($id);
        // $vendors = $insurance->vendor;
        $response = $this->insuranceServices->edit($id); 
        return $this->sendSuccess(['data' => $response]);    
    } 
	 /**
     * Update the specified Insurance data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateInsurance(Request $request, $id)
    {        
        $validation = $this->insuranceServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->insuranceServices->updateData($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully insurance was updated"]);
        } else {
            return $this->sendError(['message' => 'Insurance update was failed']);
        }
    }
	 /**
     * delete the specified Insurance data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteInsurance($id)
    {      
        $response = $this->insuranceServices->delete($id); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully insurance was deleted"]);
        } else {
            return $this->sendError(['message' => 'Insurance delete was failed']);
        }  
        
    }
}

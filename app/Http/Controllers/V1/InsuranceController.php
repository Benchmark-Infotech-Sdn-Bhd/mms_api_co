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
    private $insuranceServices;
    private $insurance;

    public function __construct(InsuranceServices $insuranceServices,Insurance $insurance)
    {
        $this->insuranceServices = $insuranceServices;
        $this->insurance = $insurance;
    }

    public function createInsurance(Request $request)
    {
        $input = $request->all();
        $validation = $this->insurance::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->insuranceServices->create($request); 
        return $response;
    }
    
    public function showInsurance()
    {        
        // $insurance = Insurance::paginate(10);
        $response = $this->insuranceServices->show(); 
        return $response;   
    }

    public function editInsurance($id)
    {   
        // $insurance = Insurance::find($id);
        // $vendors = $insurance->vendor;
        $response = $this->insuranceServices->edit($id); 
        return $response;      
    } 

    public function updateInsurance(Request $request, $id)
    {        
        $input = $request->all();
        $validation = $this->insurance::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->insuranceServices->updateData($id, $request); 
        return $response;
    }

    public function deleteInsurance($id)
    {      
        $response = $this->insuranceServices->delete($id); 
        return $response; 
        
    }
}

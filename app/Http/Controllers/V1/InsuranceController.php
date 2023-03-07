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

    public function __construct(InsuranceServices $insuranceServices,)
    {
        $this->insuranceServices = $insuranceServices;
    }

    public function createInsurance(Request $request)
    {
        $input = $request->all();
        $validation = Insurance::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->insuranceServices->create($request); 
        return $response;
    }
    
    public function showInsurance()
    {        
        // $insurance = Insurance::paginate(10);
        $insurance = Insurance::with('vendor')->paginate(10);
        return response()->json($insurance,200);
    }

    public function editInsurance($id)
    {        
        $insurance = Insurance::findorfail($id);
        // $insurance = Insurance::find($id);
        // $vendors = $insurance->vendor;
        return response()->json($insurance,200);
    } 

    public function updateInsurance(Request $request, $id)
    {        
        $insurance = Insurance::findorfail($id);
        $input = $request->all();
        $validation = Insurance::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->insuranceServices->update($insurance, $request); 
        return $response;
    }

    public function deleteInsurance($id)
    {       
        $insurance = Insurance::findorfail($id);
        $response = $this->insuranceServices->delete($insurance); 
        return $response;
        
    }
}

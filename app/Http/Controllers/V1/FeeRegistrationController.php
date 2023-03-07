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
    private $feeRegistrationServices;

    public function __construct(FeeRegistrationServices $feeRegistrationServices,)
    {
        $this->feeRegistrationServices = $feeRegistrationServices;
    }

    public function createFeeRegistration(Request $request)
    {
        $input = $request->all();
        $validation = FeeRegistration::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->feeRegistrationServices->create($request); 
        return $response;
    }
    
    public function showFeeRegistration()
    {        
        $feeRegistration = FeeRegistration::paginate(10);
        return response()->json($feeRegistration,200);
    }

    public function editFeeRegistration($id)
    {        
        $feeRegistration = FeeRegistration::findorfail($id);
        return response()->json($feeRegistration,200);
    } 

    public function updateFeeRegistration(Request $request, $id)
    {        
        $feeRegistration = FeeRegistration::findorfail($id);
        $input = $request->all();
        $validation = FeeRegistration::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->feeRegistrationServices->update($feeRegistration, $request); 
        return $response;
    }

    public function deleteFeeRegistration($id)
    {       
        $feeRegistration = FeeRegistration::findorfail($id);
        $response = $this->feeRegistrationServices->delete($feeRegistration); 
        return $response;
        
    }
    
}

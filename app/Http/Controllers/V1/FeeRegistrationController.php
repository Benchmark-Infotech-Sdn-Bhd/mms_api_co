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
    private $feeRegistration;

    public function __construct(FeeRegistrationServices $feeRegistrationServices,FeeRegistration $feeRegistration)
    {
        $this->feeRegistrationServices = $feeRegistrationServices;
        $this->feeRegistration = $feeRegistration;
    }

    public function createFeeRegistration(Request $request)
    {
        $input = $request->all();
        $validation = $this->feeRegistration::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->feeRegistrationServices->create($request); 
        return $response;
    }
    
    public function showFeeRegistration()
    {        
        $response = $this->feeRegistrationServices->show(); 
        return $response;
    }

    public function editFeeRegistration($id)
    {    
        $response = $this->feeRegistrationServices->edit($id); 
        return $response;
    } 

    public function updateFeeRegistration(Request $request, $id)
    {        
        $input = $request->all();
        $validation = $this->feeRegistration::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }        
        $response = $this->feeRegistrationServices->updateData($id, $request); 
        return $response;
    }

    public function deleteFeeRegistration($id)
    {      
        $response = $this->feeRegistrationServices->delete($id); 
        return $response;   
        
    }
    
}

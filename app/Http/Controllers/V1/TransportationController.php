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
    private $transportationServices;
    private $transportation;

    public function __construct(TransportationServices $transportationServices,Transportation $transportation)
    {
        $this->transportationServices = $transportationServices;
        $this->transportation = $transportation;
    }

    public function createTransportation(Request $request)
    {
        $input = $request->all();
        $validation = $this->transportation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->transportationServices->create($request); 
        return $response;
    }
    
    public function showTransportation()
    {        
        // $transportation = Transportation::paginate(10);
        $response = $this->transportationServices->show(); 
        return $response;  
    }

    public function editTransportation($id)
    {      
        $response = $this->transportationServices->edit($id); 
        return $response;  
    } 

    public function updateTransportation(Request $request, $id)
    {        
        $input = $request->all();
        $validation = $this->transportation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->transportationServices->updateData($id, $request); 
        return $response;
    }

    public function deleteTransportation($id)
    {       
        $response = $this->transportationServices->delete($id); 
        return $response; 
    }
}

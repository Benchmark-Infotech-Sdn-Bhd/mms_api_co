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

    public function __construct(TransportationServices $transportationServices,)
    {
        $this->transportationServices = $transportationServices;
    }

    public function createTransportation(Request $request)
    {
        $input = $request->all();
        $validation = Transportation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->transportationServices->create($request); 
        return $response;
    }
    
    public function showTransportation()
    {        
        // $transportation = Transportation::paginate(10);
        $transportation = Transportation::with('vendor')->paginate(10);
        return response()->json($transportation,200);
    }

    public function editTransportation($id)
    {        
        $transportation = Transportation::findorfail($id);
        return response()->json($transportation,200);
    } 

    public function updateTransportation(Request $request, $id)
    {        
        $transportation = Transportation::findorfail($id);
        $input = $request->all();
        $validation = Transportation::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->transportationServices->update($transportation, $request); 
        return $response;
    }

    public function deleteTransportation($id)
    {       
        $transportation = Transportation::findorfail($id);
        $response = $this->transportationServices->delete($transportation); 
        return $response;
        
    }
}

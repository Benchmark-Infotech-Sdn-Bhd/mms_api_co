<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FomemaClinicsServices;
use App\Models\FomemaClinics;
use Illuminate\Support\Facades\Validator;

class FomemaClinicsController extends Controller
{
    private $fomemaClinicsServices;

    public function __construct(FomemaClinicsServices $fomemaClinicsServices,)
    {
        $this->fomemaClinicsServices = $fomemaClinicsServices;
    }

    public function createFomemaClinics(Request $request)
    {
        $input = $request->all();
        $validation = FomemaClinics::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->fomemaClinicsServices->create($request); 
        return $response;
    }
    
    public function showFomemaClinics()
    {        
        $fomemaClinics = FomemaClinics::paginate(10);
        return response()->json($fomemaClinics,200);
    }

    public function editFomemaClinics($id)
    {        
        $fomemaClinics = FomemaClinics::findorfail($id);
        return response()->json($fomemaClinics,200);
    } 

    public function updateFomemaClinics(Request $request, $id)
    {        
        $fomemaClinics = FomemaClinics::findorfail($id);
        $input = $request->all();
        $validation = FomemaClinics::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->fomemaClinicsServices->update($fomemaClinics, $request); 
        return $response;
    }

    public function deleteFomemaClinics($id)
    {       
        $fomemaClinics = FomemaClinics::findorfail($id);
        $response = $this->fomemaClinicsServices->delete($fomemaClinics); 
        return $response;
        
    }
    
}

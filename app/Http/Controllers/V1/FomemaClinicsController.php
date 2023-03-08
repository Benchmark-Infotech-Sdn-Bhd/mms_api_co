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
    private $fomemaClinics;

    public function __construct(FomemaClinicsServices $fomemaClinicsServices,FomemaClinics $fomemaClinics)
    {
        $this->fomemaClinicsServices = $fomemaClinicsServices;
        $this->fomemaClinics = $fomemaClinics;
    }

    public function createFomemaClinics(Request $request)
    {
        $input = $request->all();
        $validation = $this->fomemaClinics::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->fomemaClinicsServices->create($request); 
        return $response;
    }
    
    public function showFomemaClinics()
    {        
        $response = $this->fomemaClinicsServices->show(); 
        return $response;
    }

    public function editFomemaClinics($id)
    {   
        $response = $this->fomemaClinicsServices->edit($id); 
        return $response;
    } 

    public function updateFomemaClinics(Request $request, $id)
    {        
        $input = $request->all();
        $validation = $this->fomemaClinics::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }        
        $response = $this->fomemaClinicsServices->updateData($id, $request); 
        return $response;
    }

    public function deleteFomemaClinics($id)
    {    
        $response = $this->fomemaClinicsServices->delete($id); 
        return $response;        
    }
    
}

<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\VendorServices;
use App\Models\Vendor;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    private $vendorServices;
    private $vendor;
    public function __construct(Vendor $vendor,VendorServices $vendorServices,)
    {
        $this->vendorServices = $vendorServices;
        $this->vendor = $vendor;
    }

    public function createVendor(Request $request)
    {
        $input = $request->all();
        $validation = $this->vendor::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->vendorServices->create($request); 
        return $response;
    }
    
    public function showVendors()
    {   
        $response = $this->vendorServices->show(); 
        return $response;        
    }

    public function editVendors($id)
    {        
        $response = $this->vendorServices->edit($id); 
        return $response;          
    } 

    public function updateVendors(Request $request, $id)
    {                
        $input = $request->all();
        $validation = $this->vendor::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }        
        $response = $this->vendorServices->updateData($id, $request); 
        return $response;
    }

    public function deleteVendors($id)
    {  
        $response = $this->vendorServices->delete($id); 
        return $response;        
    }
    
}

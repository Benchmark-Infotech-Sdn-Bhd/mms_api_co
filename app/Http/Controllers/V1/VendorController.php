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
    public function __construct(VendorServices $vendorServices,)
    {
        $this->vendorServices = $vendorServices;
    }

    public function createVendor(Request $request)
    {
        $input = $request->all();
        $validation = Vendor::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->vendorServices->create($request); 
        return $response;
    }
    
    public function showVendors()
    {        
        // $vendors = Vendor::paginate(10);
        $vendors = Vendor::with('accommodations', 'insurances', 'transportations')->paginate(10);
        return response()->json($vendors,200);
    }

    public function editVendors($id)
    {        
        $vendors = Vendor::find($id);
        // $accommodations = $vendors->accommodations;
        // $insurances = $vendors->insurances;
        // $transportations = $vendors->transportations;
        // $vendors = Vendor::findorfail($id);
        return response()->json($vendors,200);
    } 

    public function updateVendors(Request $request, $id)
    {        
        $vendors = Vendor::findorfail($id);
        $input = $request->all();
        $validation = Vendor::validate($input);
        if ($validation !== true) {
            return response()->json(['errors' => $validation], 422);
        }
        $response = $this->vendorServices->update($vendors, $request); 
        return $response;
    }

    public function deleteVendors($id)
    {       
        $vendors = Vendor::find($id);
        $response = $this->vendorServices->delete($vendors); 
        return $response;        
    }
    
}

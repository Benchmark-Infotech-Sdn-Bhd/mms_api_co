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
    /**
     * @var vendorServices
     */
    private $vendorServices;
    public function __construct(VendorServices $vendorServices)
    {
        $this->vendorServices = $vendorServices;
    }
	 /**
     * Show the form for creating a new Vendor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createVendor(Request $request)
    {
        $response = $this->vendorServices->create($request); 
        return $response;
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return JsonResponse
     */   
    public function showVendors()
    {   
        $response = $this->vendorServices->show(); 
        return $response;        
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editVendors($id)
    {        
        $response = $this->vendorServices->edit($id); 
        return $response;          
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateVendors(Request $request, $id)
    {                       
        $response = $this->vendorServices->updateData($id, $request); 
        return $response;
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteVendors($id)
    {  
        $response = $this->vendorServices->delete($id); 
        return $response;        
    }
    
}

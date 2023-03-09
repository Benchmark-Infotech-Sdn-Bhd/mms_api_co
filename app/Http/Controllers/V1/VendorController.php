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
        
        $validation = $this->vendorServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }
        $response = $this->vendorServices->create($request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully vendor was created"]);
        } else {
            return $this->sendError(['message' => "Vendor creation was failed"]);
        }
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return JsonResponse
     */   
    public function showVendors()
    {   
        $response = $this->vendorServices->show(); 
        return $this->sendSuccess(['data' => $response]);
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
        return $this->sendSuccess(['data' => $response]);
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateVendors(Request $request, $id)
    {           
        $validation = $this->vendorServices->inputValidation($request);
        if ($validation !== true) {
            return $this->validationError($validation);
        }            
        $response = $this->vendorServices->updateData($id, $request); 
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Vendor was updated"]);
        } else {
            return $this->sendError(['message' => 'Vendor update was failed']);
        }
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
        if($response == true) {
            return $this->sendSuccess(['message' => "Successfully Vendor was deleted"]);
        } else {
            return $this->sendError(['message' => 'Vendor delete was failed']);
        }    
    }
    
}

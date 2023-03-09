<?php

namespace App\Http\Controllers\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\VendorServices;
use Exception;

class VendorController extends Controller
{
    /**
     * @var vendorServices
     */
    private $vendorServices;
    /**
     * VendorServices constructor.
     * @param VendorServices $vendorServices
     */
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
        try {   
            $validation = $this->vendorServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $this->vendorServices->create($request);             
            return $this->sendSuccess(['message' => "Successfully vendor was created"]);
            
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Vendor creation was failed']);
        }
    }
	 /**
     * Display a listing of the Vendors.
     *
     * @return JsonResponse
     */   
    public function showVendors()
    {   
        try {
            $response = $this->vendorServices->show(); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Show Vendors was failed'], 400);
        }
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param $id
     * @return JsonResponse
     */
    public function editVendors($id)
    {     
        try {   
            $response = $this->vendorServices->edit($id); 
            return $this->sendSuccess(['data' => $response]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Edit Vendors was failed'], 400);
        }
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param Request $request, $id
     * @return JsonResponse
     */
    public function updateVendors(Request $request, $id)
    {    
        try {       
            $validation = $this->vendorServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }         
            $this->vendorServices->updateData($id, $request);
            return $this->sendSuccess(['message' => "Successfully Vendor was updated"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Vendor update was failed']);
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
        try {
            $this->vendorServices->delete($id); 
            return $this->sendSuccess(['message' => "Successfully Vendor was deleted"]);
        } catch (Exception $exception) {
            $this->sendError(['message' => 'Vendor delete was failed']);
        }  
    }
    
}

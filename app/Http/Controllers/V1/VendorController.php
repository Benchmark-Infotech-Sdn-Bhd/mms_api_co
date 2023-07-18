<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\VendorServices;
use Exception;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * @var vendorServices
     */
    private VendorServices $vendorServices;
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
    public function create(Request $request): JsonResponse
    {     
        try {   
            $validation = $this->vendorServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }
            $response = $this->vendorServices->create($request);       
            return $this->sendSuccess($response);
            
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
            // return $this->sendError(['message' => 'Vendor creation was failed']);
        }
    }
	 /**
     * Display a listing of the Vendors.
     * @param Request $request
     * @return JsonResponse
     */   
    public function list(Request $request): JsonResponse
    {   
        try {
            $response = $this->vendorServices->list($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve all Vendors data was failed'], 400);
        }
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {     
        try {   
            $params = $this->getRequest($request);
            $response = $this->vendorServices->show($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve Vendors data was failed'], 400);
        }
    } 
	 /**
     * Update the specified Vendor data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {    
        try {    
            $validation = $this->vendorServices->updateValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }         
            $response = $this->vendorServices->update($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => $e->getMessage()],400);
        }
    }
	 /**
     * delete the specified Vendors data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {  
        try {
            $params = $this->getRequest($request);
            $response = $this->vendorServices->delete($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete Vendor was failed']);
        }  
    }
    /**
     * delete the specified Attachment data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAttachment(Request $request): JsonResponse
    {   
        try {
            $params = $this->getRequest($request);
            $response = $this->vendorServices->deleteAttachment($params);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Delete attachments was failed']);
        }        
    }
    /**
     * Display a listing of the insurance vendor list.
     * @param Request $request
     * @return JsonResponse
     */   
    public function insuranceVendorList(Request $request): JsonResponse
    {   
        try {
            $response = $this->vendorServices->insuranceVendorList($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve insurance Vendors data was failed'], 400);
        }
    }
    /**
     * Display a listing of the Transportation vendor list.
     * @param Request $request
     * @return JsonResponse
     */   
    public function transportationVendorList(Request $request): JsonResponse
    {   
        try {
            $response = $this->vendorServices->transportationVendorList($request); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Retrieve Transportation Vendors data was failed'], 400);
        }
    }
}

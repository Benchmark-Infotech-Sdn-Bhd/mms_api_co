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
     *
     * @return JsonResponse
     */   
    public function retrieveAll(): JsonResponse
    {   
        try {
            $response = $this->vendorServices->retrieveAll(); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Show Vendors was failed'], 400);
        }
    }
	 /**
     * Display the data for edit form by using Vendor id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function retrieve(Request $request): JsonResponse
    {     
        try {   
            $params = $this->getRequest($request);
            $response = $this->vendorServices->retrieve($params); 
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Edit Vendors was failed'], 400);
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
            $validation = $this->vendorServices->inputValidation($request);
            if ($validation) {
                return $this->validationError($validation);
            }         
            $response = $this->vendorServices->update($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'Vendor update was failed']);
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
            return $this->sendError(['message' => 'Vendor delete was failed']);
        }  
    }
    /**
     * searching Vendors data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try{
            $response = $this->vendorServices->search($request);
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            return $this->sendError(['message' => 'search vendor was failed']);
        }
    }
    
}

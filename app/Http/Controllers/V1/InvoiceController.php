<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InvoiceServices;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceController extends Controller
{
    /**
     * @var invoiceServices
     */
    private InvoiceServices $invoiceServices;

    /**
     * InvoiceController constructor.
     * @param InvoiceServices $invoiceServices
     */
    public function __construct(InvoiceServices $invoiceServices)
    {
        $this->invoiceServices = $invoiceServices;
    }
    /**
     * Show the form for creating a new Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->invoiceServices->create($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'creation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }

    /**
     * Show the form for creating a new Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            
            $data = $this->invoiceServices->update($request);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'updation failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    /**
     * Retrieve the specified Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->show($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }
    
    
    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->list($params);
            if(isset($data['validate'])){
                return $this->validationError($data['validate']); 
            }
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    }   

    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTaxRates(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->getTaxRates($params);
            
            return $this->sendSuccess($data->original);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 
    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function xeroGetTaxRates(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->xeroGetTaxRates($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getItems(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->getItems($params);
            
            return $this->sendSuccess($data->original);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * get items.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function xeroGetItems(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->xeroGetItems($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccounts(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->getAccounts($params);
            
            return $this->sendSuccess($data->original);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * get items.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function xeroGetAccounts(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->xeroGetAccounts($params);
            return $this->sendSuccess($data);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            $params = $this->getRequest($request);
            $data = $this->invoiceServices->getInvoices($params);
            
            return $this->sendSuccess($data->original);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 

    /**
     * Search & Retrieve all the Invoice.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccessToken(): JsonResponse
    {
        try {
        $data = $this->invoiceServices->getAccessToken();            
            return $this->sendSuccess($data->original);
        } catch (Exception $e) {
            Log::error('Error - ' . print_r($e->getMessage(), true));
            $data['error'] = 'Retrieve failed. Please retry.';
            return $this->sendError(['message' => $data['error']]);
        }
    } 
}

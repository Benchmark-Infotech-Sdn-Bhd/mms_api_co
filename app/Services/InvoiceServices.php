<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InvoiceServices
{
    /**
     * @var Invoice
     */
    private Invoice $invoice;
    /**
     * @var InvoiceItems
     */
    private InvoiceItems $invoiceItems;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * DirectRecruitmentExpensesServices constructor.
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses
     * @param InvoiceItems $invoiceItems
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            Invoice                 $invoice,
            InvoiceItems            $invoiceItems,
            ValidationServices      $validationServices,
            AuthServices            $authServices,
            Storage                 $storage
    )
    {
        $this->invoice = $invoice;
        $this->invoiceItems = $invoiceItems;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        if(!($this->validationServices->validate($request->toArray(),$this->invoice->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        $invoice = $this->invoice->create([
            'crm_prospect_id' => $request['crm_prospect_id'],
            'issue_date' => ((isset($request['issue_date']) && !empty($request['issue_date'])) ? $request['issue_date'] : null),
            'due_date' => ((isset($request['due_date']) && !empty($request['due_date'])) ? $request['due_date'] : null),
            'reference_number' => $request['reference_number'] ?? '',
            'account' => $request['account'] ?? '',
            'tax' => $request['tax'] ?? '',
            'amount' => $request['amount'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);

        //if (isset($request['invoice_items']) && sizeof($request['invoice_items']) > 0) {

        foreach($request['invoice_items'] as $item){
            $this->invoiceItems::create([
                "invoice_id" => $invoice['id'],
                "item" => $item['item'],
                "description" => $item['description'],
                "quantity" => $item['quantity'],
                "price" => $item['price'],
                "total_price" => $item['total_price']
            ]);

        }

        return $invoice;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];

        if(!($this->validationServices->validate($request->toArray(),$this->invoice->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $invoice = $this->invoice->findOrFail($request['id']);
        $invoice->crm_prospect_id = $request['crm_prospect_id'] ?? $invoice->crm_prospect_id;
        $invoice->issue_date = ((isset($request['issue_date']) && !empty($request['issue_date'])) ? $request['issue_date'] : $invoice->issue_date);
        $invoice->due_date = ((isset($request['due_date']) && !empty($request['due_date'])) ? $request['due_date'] : $invoice->due_date);
        $invoice->reference_number = ((isset($request['reference_number']) && !empty($request['reference_number'])) ? $request['reference_number'] : $invoice->reference_number);
        $invoice->account = $request['account'] ?? $invoice->account;
        $invoice->tax = $request['tax'] ?? $invoice->tax;
        $invoice->amount = $request['amount'] ?? $invoice->amount;
        $invoice->created_by = $request['created_by'] ?? $invoice->created_by;
        $invoice->modified_by = $params['modified_by'];
        $invoice->save();

        if ($request['invoice_items']){

            $this->invoiceItems->where('invoice_id', $request['id'])->delete();

            foreach($request['invoice_items'] as $item){
                    $this->invoiceItems::create([
                    "invoice_id" => $request['id'],
                    "item" => $item['item'],
                    "description" => $item['description'],
                    "quantity" => $item['quantity'],
                    "price" => $item['price'],
                    "total_price" => $item['total_price']
                ]); 
            }
        }

        return true;
    }
    
    
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $data = $this->invoice->with('invoiceItems')->find($request['id']);

        if(is_null($data)){
            return [
                "message" => "Data not found"
            ];
        }

        return $data;

    }
    
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->invoice
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('reference_number', 'like', "%{$request['search_param']}%")
                ->orWhere('account', 'like', '%'.$request['search_param'].'%');
            }
            
        })->select('id','crm_prospect_id','issue_date','due_date','reference_number','account','tax','amount','created_at')
        ->distinct()
        ->orderBy('created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getTaxRates($request) : mixed
    {
        $http = new Client();
        try {
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_TAX_RATES_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . Config::get('services.XERO_ACCESS_TOKEN'),
                    'Xero-Tenant-Id' => Config::get('services.XERO_TENANT_ID'),
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Tax details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAccessToken() : mixed
    {
        $http = new Client();
        try {
            $response = $http->request('POST', Config::get('services.XERO_TOKEN_URL'), [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode(Config::get('services.XERO_CLIENT_ID') . ":" . Config::get('services.XERO_CLIENT_SECRET')),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ], 
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => Config::get('services.XERO_CLIENT_ID'),
                    'refresh_token' => Config::get('services.XERO_REFRESH_TOKEN'),
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            Config::set('services.XERO_REFRESH_TOKEN', $result['refresh_token']);
            Config::set('services.XERO_ACCESS_TOKEN', $result['access_token']);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting refresh token' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

}

<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\InvoiceItemsTemp;
use App\Models\XeroSettings;
use App\Models\XeroTaxRates;
use App\Models\XeroAccounts;
use App\Models\XeroItems;
use App\Models\DirectRecruitmentExpenses;
use App\Models\EContractCostManagement;
use App\Models\TotalManagementCostManagement;
use App\Models\CRMProspect;
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
     * @var InvoiceItemsTemp
     */
    private InvoiceItemsTemp $invoiceItemsTemp;
    /**
     * @var XeroSettings
     */
    private XeroSettings $xeroSettings;
    /**
     * @var XeroTaxRates
     */
    private XeroTaxRates $xeroTaxRates;
    /**
     * @var XeroAccounts
     */
    private XeroAccounts $xeroAccounts;
    /**
     * @var XeroItems
     */
    private XeroItems $xeroItems;
    /**
     * @var DirectRecruitmentExpenses
     */
    private DirectRecruitmentExpenses $directRecruitmentExpenses;
    /**
     * @var EContractCostManagement
     */
    private EContractCostManagement $eContractCostManagement;
    /**
     * @var TotalManagementCostManagement
     */
    private TotalManagementCostManagement $totalManagementCostManagement;
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
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
     * InvoiceServices constructor.
     * @param Invoice $Invoice
     * @param InvoiceItems $invoiceItems
     * @param InvoiceItemsTemp $invoiceItemsTemp
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses
     * @param EContractCostManagement $eContractCostManagement
     * @param TotalManagementCostManagement $totalManagementCostManagement
     * @param CRMProspect $crmProspect
     * @param XeroSettings $xeroSettings
     * @param XeroTaxRates $xeroTaxRates
     * @param XeroAccounts $xeroAccounts
     * @param XeroItems $xeroItems
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            Invoice                     $invoice,
            InvoiceItems                $invoiceItems,
            InvoiceItemsTemp            $invoiceItemsTemp,
            DirectRecruitmentExpenses   $directRecruitmentExpenses,
            EContractCostManagement     $eContractCostManagement,
            TotalManagementCostManagement $totalManagementCostManagement,
            CRMProspect                 $crmProspect,
            XeroSettings                $xeroSettings,
            XeroTaxRates                $xeroTaxRates,
            XeroAccounts                $xeroAccounts,
            XeroItems                   $xeroItems,
            ValidationServices          $validationServices,
            AuthServices                $authServices,
            Storage                     $storage
    )
    {
        $this->invoice = $invoice;
        $this->invoiceItems = $invoiceItems;
        $this->invoiceItemsTemp = $invoiceItemsTemp;
        $this->xeroSettings = $xeroSettings;
        $this->xeroTaxRates = $xeroTaxRates;
        $this->xeroAccounts = $xeroAccounts;
        $this->xeroItems = $xeroItems;
        $this->directRecruitmentExpenses = $directRecruitmentExpenses;
        $this->eContractCostManagement = $eContractCostManagement;
        $this->totalManagementCostManagement = $totalManagementCostManagement;
        $this->crmProspect = $crmProspect;
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
        $accountSystem = $this->getXeroSettings();
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
            'tax' => $request['tax'] ?? 0,
            'amount' => $request['amount'] ?? 0,
            'due_amount' => $request['due_amount'] ?? 0,
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0,
            'company_id' => $user['company_id'],
            'remarks' => $request['remarks'] ?? ''
        ]);

        $crmProspect = $this->crmProspect->findOrFail($request['crm_prospect_id']);

        if($accountSystem['title'] == 'XERO'){
            $generateInvoice['Type'] = 'ACCREC';
            $issuedateConverted = (Carbon::parse($params['due_date'])->timestamp * 1000)."+0000";
            $generateInvoice['Date'] = '/Date('.$issuedateConverted.')/';
            $duedateConverted = (Carbon::parse($params['due_date'])->timestamp * 1000)."+0000";
            $generateInvoice['DueDate'] = '/Date('.$duedateConverted.')/';
            $generateInvoice['DateString'] = $params['issue_date']."T00:00:00";
            $generateInvoice['DueDateString'] = $params['due_date']."T00:00:00";
            $generateInvoice['LineAmountTypes'] = 'Exclusive';

            $generateInvoice['Contact']['ContactID'] = $crmProspect->xero_contact_id;

            $lineItems = json_decode($request['invoice_items']);
            
            if ($request['invoice_items']){
                $increment = 0;
                foreach($lineItems as $item){
                    $this->invoiceItems::create([
                        "invoice_id" => $invoice['id'],
                        "item" => $item->item ?? '',
                        "description" => $item->description,
                        "quantity" => $item->quantity,
                        "price" => $item->price,
                        "account" => $item->account,
                        "tax" => $item->tax_rate,
                        "total_price" => $item->total_price
                    ]);

                    $generateInvoice['LineItems'][$increment] = new \stdClass();
                    $generateInvoice['LineItems'][$increment]->Description = 'Expense';
                    //$generateInvoice['LineItems'][$increment]->Item = $item->item ?? '';
                    $generateInvoice['LineItems'][$increment]->Description = $item->description;
                    $generateInvoice['LineItems'][$increment]->Quantity = $item->quantity;
                    $generateInvoice['LineItems'][$increment]->UnitAmount = $item->price;
                    $generateInvoice['LineItems'][$increment]->AccountCode = $item->account ?? '';
                    $generateInvoice['LineItems'][$increment]->DiscountRate = $item->tax_rate ?? 0;
                    $increment++;
                }
            }

            $generateInvoiceXero = $this->generateInvoices($generateInvoice);   
            
            if(isset($generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'])){

                $invoiceData = $this->invoice->findOrFail($invoice['id']);
                $invoiceData->invoice_number = $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'];
                $invoiceData->due_amount = $generateInvoiceXero->original['Invoices'][0]['AmountDue'];
                $invoiceData->invoice_status = $generateInvoiceXero->original['Invoices'][0]['Status'];
                $invoiceData->save();
    
                // Delete from temporary table
                $this->invoiceItemsTemp->where('created_by', $user['id'])->delete();
    
                foreach($lineItems as $item){
                    if($item->service_id == 1){
                        $this->directRecruitmentExpenses->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                        ]);
                    } else if($item->service_id == 2){
                        $this->eContractCostManagement->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                        ]);
                    }
                    else if($item->service_id == 3){
                        $this->totalManagementCostManagement->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                        ]);
                    }
                }
            }

        } else if ($accountSystem['title'] == 'ZOHO') {
            $generateInvoice['date'] = $params['issue_date'] ?? null;
            $generateInvoice['due_date'] = $params['due_date'] ?? null;
            $generateInvoice['reference_number'] = $params['reference_number'] ?? null;
            $generateInvoice['customer_id'] = $crmProspect->xero_contact_id;
            $generateInvoice['is_inclusive_tax'] = '';

            $lineItems = json_decode($request['invoice_items']);
        
            if ($request['invoice_items']){
                $increment = 0;
                foreach($lineItems as $item){
                    $this->invoiceItems::create([
                        "invoice_id" => $invoice['id'],
                        "item" => $item->item ?? '',
                        "description" => $item->description,
                        "quantity" => $item->quantity,
                        "price" => $item->price,
                        "account" => $item->account,
                        "tax" => $item->tax_rate ?? 0,
                        "tax_id" => $item->tax_id ?? '',
                        "total_price" => $item->total_price
                    ]);

                    $generateInvoice['line_items'][$increment] = new \stdClass();
                    $generateInvoice['line_items'][$increment]->item_order = $item->item_order ?? '';
                    $generateInvoice['line_items'][$increment]->item_id = $item->item_id ?? '';
                    $generateInvoice['line_items'][$increment]->name = $item->item ?? '';
                    $generateInvoice['line_items'][$increment]->rate = $item->price;
                    $generateInvoice['line_items'][$increment]->description = $item->description;
                    $generateInvoice['line_items'][$increment]->quantity = $item->quantity;
                    $generateInvoice['line_items'][$increment]->tax_id = $item->tax_id ?? '';
                    $generateInvoice['line_items'][$increment]->account_id = $item->account ?? ''; 
                    $increment++;
                }
            }

            $generateInvoiceXero = $this->generateInvoicesZoho($generateInvoice);  
            
            if(isset($generateInvoiceXero->original['invoice']['invoice_id'])){

                $invoiceData = $this->invoice->findOrFail($invoice['id']);
                $invoiceData->invoice_number = $generateInvoiceXero->original['invoice']['invoice_id'];
                $invoiceData->zoho_invoice_number = $generateInvoiceXero->original['invoice']['invoice_number'];
                $invoiceData->due_amount = $generateInvoiceXero->original['invoice']['balance'];
                $invoiceData->invoice_status = $generateInvoiceXero->original['invoice']['status'];
                $invoiceData->save();
    
                // Delete from temporary table
                $this->invoiceItemsTemp->where('created_by', $user['id'])->delete();
    
                foreach($lineItems as $item){
                    if($item->service_id == 1){
                        $this->directRecruitmentExpenses->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['invoice']['invoice_id']
                        ]);
                    } else if($item->service_id == 2){
                        $this->eContractCostManagement->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['invoice']['invoice_id']
                        ]);
                    }
                    else if($item->service_id == 3){
                        $this->totalManagementCostManagement->where('id', $item->expense_id)->update([
                              'invoice_number' => $generateInvoiceXero->original['invoice']['invoice_id']
                        ]);
                    }
                }
            }

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
        $invoice->amount = $request['amount'] ?? $invoice->amount;
        $invoice->due_amount = $request['amount'] ?? $invoice->due_amount;
        $invoice->remarks = $request['remarks'] ?? $invoice->remarks;
        $invoice->created_by = $request['created_by'] ?? $invoice->created_by;
        $invoice->modified_by = $params['modified_by'];
        $invoice->save();

        if ($request['invoice_items']){

            $this->invoiceItems->where('invoice_id', $request['id'])->delete();
            $lineItems = json_decode($request['invoice_items']);
            foreach($request['invoice_items'] as $item){
                    $this->invoiceItems::create([
                    "invoice_id" => $request['id'],
                    "item" => $item->item,
                    "description" => $item->description,
                    "quantity" => $item->quantity,
                    "price" => $item->price,
                    "account" => $item->account,
                    "tax" => $item->tax_rate,
                    "total_price" => $item->total_price
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

        $invoiceData = $this->invoice->find($request['id']);
        
        if(isset($invoiceData) && !empty($invoiceData)){

            $accountSystem = $this->getXeroSettings();
            if($accountSystem['title'] == 'XERO'){
                $invoiceXeroData = $this->getInvoices($invoiceData);
                if(isset($invoiceXeroData->original['Invoices'][0]['InvoiceNumber'])){
                    $invoiceData->due_amount = $invoiceXeroData->original['Invoices'][0]['AmountDue'];
                    $invoiceData->due_date = Carbon::parse($invoiceXeroData->original['Invoices'][0]['DueDateString'])->format('Y-m-d');
                    $invoiceData->invoice_status = $invoiceXeroData->original['Invoices'][0]['Status'];
                    $invoiceData->save();
                }
            }
            else if ($accountSystem['title'] == 'ZOHO') {
                $invoiceZohoData = $this->getInvoicesZoho($invoiceData);
                if(isset($invoiceZohoData->original['invoice']['invoice_id'])){
                    $invoiceData->due_amount = $invoiceZohoData->original['invoice']['balance'];
                    $invoiceData->due_date = $invoiceZohoData->original['due_date'];
                    $invoiceData->invoice_status = $invoiceZohoData->original['status'];
                    $invoiceData->save();
                }
            }
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->invoice->with(['crm_prospect' => function ($query) {
            $query->select(['id', 'company_name']);
        }])
        ->whereIn('invoice.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == 'Customer') {
                $query->where('crm_prospect_id', '=', $user['reference_id']);
            }
        })
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('invoice_number', 'like', "%{$request['search_param']}%");
            }
            if (isset($request['invoice_status']) && !empty($request['invoice_status'])) {
                $query->where('invoice_status', 'like', "%{$request['invoice_status']}%");
            }
            
        })->select('id','crm_prospect_id','issue_date','due_date','reference_number','tax','amount','due_amount','created_at','invoice_number','invoice_status')
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
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_TAX_RATES_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
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
    public function saveTaxRates() : mixed
    {
        $http = new Client();
        $cronConfig = $this->getCronSettings();
        try {
            foreach($cronConfig as $clients){
                switch($clients['title']) {
                    case 'XERO':
                        $response = $http->request('GET', $clients['url'] . Config::get('services.XERO_TAX_RATES_URL'), [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $clients['access_token'],
                                'Xero-Tenant-Id' => $clients['tenant_id'],
                                'Accept' => 'application/json',
                            ],
                            'form_params' => [
                            ],
                        ]);
                        $result = json_decode((string)$response->getBody(), true);

                        if(isset($result['TaxRates'])){
                            foreach ($result['TaxRates'] as $row) {
                                $this->xeroTaxRates->updateOrCreate(
                                    [
                                        'company_id' => $clients['company_id'],
                                        'name' => $row['Name'] ?? null, 
                                        'tax_type' => $row['TaxType'] ?? null, 
                                        'report_tax_type' => $row['ReportTaxType'] ?? null, 
                                        'display_tax_rate' => $row['DisplayTaxRate'] ?? 0, 
                                        'effective_rate' => $row['EffectiveRate'] ?? 0, 
                                        'status' => $row['Status'] ?? null
                                    ],
                                    [
                                        'can_applyto_assets' => $row['CanApplyToAssets'] ?? null, 
                                        'can_applyto_equity' => $row['CanApplyToEquity'] ?? null, 
                                        'can_applyto_expenses' => $row['CanApplyToExpenses'] ?? null, 
                                        'can_applyto_liabilities' => $row['CanApplyToLiabilities'] ?? null, 
                                        'can_applyto_revenue' => $row['CanApplyToRevenue'] ?? null
                                    ]
                                );
                            }
                        }
                        break;                  
                    case 'ZOHO':
                        $response = $http->request('GET', $clients['url'] . Config::get('services.ZOHO_TAX_RATES_URL'). '?organization_id=' . $clients['tenant_id'], [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $clients['access_token'],
                                'Content-Type' => 'application/json',
                                'Accept' => '*/*'
                            ], 
                        ]);
                        $result = json_decode((string)$response->getBody(), true);
                            if(isset($result['taxes'])){
                                foreach ($result['taxes'] as $row) {
                                    $this->xeroTaxRates->updateOrCreate(
                                        [
                                            'company_id' => $clients['company_id'],
                                            'tax_id' => $row['tax_id'] ?? null, 
                                            'name' => $row['tax_name'] ?? null, 
                                            'tax_type' => $row['tax_type'] ?? null, 
                                            'report_tax_type' => $row['ReportTaxType'] ?? null, 
                                            'display_tax_rate' => $row['tax_percentage'] ?? 0, 
                                            'effective_rate' => $row['tax_percentage'] ?? 0, 
                                            'status' => $row['status'] ?? null
                                        ],
                                        [
                                            'can_applyto_assets' => $row['CanApplyToAssets'] ?? null, 
                                            'can_applyto_equity' => $row['CanApplyToEquity'] ?? null, 
                                            'can_applyto_expenses' => $row['CanApplyToExpenses'] ?? null, 
                                            'can_applyto_liabilities' => $row['CanApplyToLiabilities'] ?? null, 
                                            'can_applyto_revenue' => $row['CanApplyToRevenue'] ?? null,

                                            'tax_specific_type' => $row['tax_specific_type'] ?? null,
                                            'output_tax_account_name' => $row['output_tax_account_name'] ?? null,
                                            'purchase_tax_account_name' => $row['purchase_tax_account_name'] ?? null,
                                            'tax_account_id' => $row['tax_account_id'] ?? null,
                                            'purchase_tax_account_id' => $row['purchase_tax_account_id'] ?? null,
                                            'is_inactive' => $row['is_inactive'] ?? null,
                                            'is_value_added' => $row['is_value_added'] ?? null,
                                            'is_default_tax' => $row['is_default_tax'] ?? null,
                                            'is_editable' => $row['is_editable'] ?? null,
                                            'last_modified_time' => $row['last_modified_time'] ?? null,
                                        ]
                                    );
                                }
                            }
                        break; 
                    default:
                        $response = '';
                }
            }
            
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetTaxRates($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroTaxRates
            ->select('id', 'name', 'tax_type', 'report_tax_type', 'can_applyto_assets', 'can_applyto_equity', 'can_applyto_expenses', 'can_applyto_liabilities', 'can_applyto_revenue', 'display_tax_rate', 'effective_rate', 'status', 'company_id', 'tax_id', 'tax_specific_type', 'output_tax_account_name', 'purchase_tax_account_name', 'tax_account_id', 'purchase_tax_account_id', 'is_inactive', 'is_value_added', 'is_default_tax', 'is_editable', 'last_modified_time')
            ->where('company_id',$user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getItems($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_ITEMS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Items details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function saveItems() : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_ITEMS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);

            if(isset($result['Items'])){
                foreach ($result['Items'] as $row) {
                    $this->xeroItems->updateOrCreate(
                        [
                            'item_id' => $row['ItemID'] ?? null, 
                             'code' => $row['Code'] ?? null
                        ],
                        [
                            'description' => $row['Description'] ?? null, 
                             'purchase_description' => $row['PurchaseDescription'] ?? null, 
                             'name' => $row['Name'] ?? null, 
                             'is_tracked_as_inventory' => $row['IsTrackedAsInventory'] ?? null, 
                             'is_sold' => $row['IsSold'] ?? null,
                             'is_purchased' => $row['IsPurchased'] ?? null,
                        ]
                    );
                }
            }

            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Items details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetItems($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroItems
            ->select('id', 'item_id', 'code', 'description', 'purchase_description', 'name', 'is_tracked_as_inventory', 'is_sold', 'is_purchased')
            ->where('company_id',$user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAccounts($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_ACCOUNTS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Account details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function saveAccounts() : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getCronSettings();
        try {
            foreach($xeroConfig as $clients){
                //Log::channel('cron_activity_logs')->info('Exception in getting Account details ' . $clients['company_id']);
                switch($clients['title']) {
                    case 'XERO':
                    $response = $http->request('GET', $clients['url'] . Config::get('services.XERO_ACCOUNTS_URL'), [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                            'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                            'Accept' => 'application/json',
                        ],
                        'form_params' => [
                        ],
                    ]);
                    $result = json_decode((string)$response->getBody(), true);
                    
                    if(isset($result['Accounts'])){
                        foreach ($result['Accounts'] as $row) {
                            $this->xeroAccounts->updateOrCreate(
                                [
                                    'account_id' => $row['AccountID'] ?? null, 
                                    'code' => $row['Code'] ?? null
                                ],
                                [
                                    'name' => $row['Name'] ?? null, 
                                    'status' => $row['Status'] ?? null, 
                                    'type' => $row['Type'] ?? null, 
                                    'tax_type' => $row['TaxType'] ?? null, 
                                    'class' => $row['Class'] ?? null,
                                    'enable_payments_to_account' => $row['EnablePaymentsToAccount'] ?? null,
                                    'show_in_expense_claims' => $row['ShowInExpenseClaims'] ?? null,
                                    'bank_account_number' => $row['BankAccountNumber'] ?? null,
                                    'bank_account_type' => $row['BankAccountType'] ?? null,
                                    'currency_code' => $row['CurrencyCode'] ?? null,
                                    'reporting_code' => $row['ReportingCode'] ?? null,
                                    'reporting_code_name' => $row['ReportingCodeName'] ?? null,
                                    'company_id' => $clients['company_id'],
                                ]
                            );
                        }
                    }
                    break;                  
                    case 'ZOHO':
                        $response = $http->request('GET', $clients['url'] . Config::get('services.ZOHO_ACCOUNTS_URL'). '?organization_id=' . $clients['tenant_id'], [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $clients['access_token'],
                                'Content-Type' => 'application/json',
                                'Accept' => '*/*'
                            ], 
                        ]);
                        $result = json_decode((string)$response->getBody(), true);
                            if(isset($result['chartofaccounts'])){
                                foreach ($result['chartofaccounts'] as $row) {
                                    $this->xeroAccounts->updateOrCreate(
                                        [
                                            'account_id' => $row['account_id'] ?? null,                                             
                                        ],
                                        [
                                            'company_id' => $clients['company_id'],
                                            'code' => $row['Code'] ?? null,
                                            'name' => $row['account_name'] ?? null, 
                                            'status' => ($row['is_active'] == true) ? 'ACTIVE' : 'INACTIVE', 
                                            'type' => $row['account_type'] ?? null, 
                                            'tax_type' => $row['TaxType'] ?? null, 
                                            'class' => $row['Class'] ?? null,
                                            'enable_payments_to_account' => $row['EnablePaymentsToAccount'] ?? null,
                                            'show_in_expense_claims' => $row['ShowInExpenseClaims'] ?? null,
                                            'bank_account_number' => $row['BankAccountNumber'] ?? null,
                                            'bank_account_type' => $row['BankAccountType'] ?? null,
                                            'currency_code' => $row['CurrencyCode'] ?? null,
                                            'reporting_code' => $row['ReportingCode'] ?? null,
                                            'reporting_code_name' => $row['ReportingCodeName'] ?? null,
                                            
                                            'description' => $row['description'] ?? null,
                                            'is_user_created' => $row['is_user_created'] ?? null,
                                            'is_system_account' => $row['is_system_account'] ?? null,
                                            'can_show_in_ze' => $row['can_show_in_ze'] ?? null,
                                            'parent_account_id' => $row['parent_account_id'] ?? null,
                                            'parent_account_name' => $row['parent_account_name'] ?? null,
                                            'depth' => $row['depth'] ?? null,
                                            'has_attachment' => $row['has_attachment'] ?? null,
                                            'is_child_present' => $row['is_child_present'] ?? null,
                                            'child_count' => $row['child_count'] ?? null,
                                        ]
                                    );
                                }
                            }
                        break; 
                    default:
                        $response = '';
                }
            }

            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Account details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetaccounts($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroAccounts
            ->select('id', 'account_id', 'code', 'name', 'status', 'type', 'tax_type','class','enable_payments_to_account','show_in_expense_claims', 'bank_account_number','bank_account_type', 'currency_code', 'reporting_code', 'reporting_code_name', 'company_id', 'description', 'is_user_created', 'is_system_account', 'can_show_in_ze', 'parent_account_id', 'parent_account_name', 'depth', 'has_attachment', 'is_child_present')
            ->where('company_id',$user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getInvoices($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        $rawUrl = '';
        if(isset($request['invoice_number']) && !empty($request['invoice_number'])){
            $rawUrl = "/".$request['invoice_number'];
        }

        try {
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url']. Config::get('services.XERO_INVOICES_URL'). $rawUrl, '');
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_INVOICES_URL'). $rawUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getInvoicesZoho($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        $rawUrl = '';
        if(isset($request['invoice_number']) && !empty($request['invoice_number'])){
            $rawUrl = "/".$request['invoice_number'];
        }

        try {
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.ZOHO_INVOICES_URL'). $rawUrl. '?organization_id=' . $xeroConfig['tenant_id'], '');
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.ZOHO_INVOICES_URL'). $rawUrl. '?organization_id=' . $xeroConfig['tenant_id'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'form_params' => [
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function generateInvoices($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        try {
            $data = [
                    'Type'=>'ACCREC',
                    'Contact'=> $request['Contact'],
                    'Date' => $request['Date'],
                    'DueDate' => $request['DueDate'],
                    'DateString' => $request['DateString'],
                    'DueDateString' => $request['DueDateString'],
                    'LineAmountTypes' => $request['LineAmountTypes'],
                    'LineItems' => $request['LineItems']
            ];
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url']. Config::get('services.XERO_INVOICES_URL'), $data);
            $response = $http->request('POST', Config::get('services.XERO_URL')  . Config::get('services.XERO_INVOICES_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'Type'=>'ACCREC',
                    'Contact'=> $request['Contact'],
                    'Date' => $request['Date'],
                    'DueDate' => $request['DueDate'],
                    'DateString' => $request['DateString'],
                    'DueDateString' => $request['DueDateString'],
                    'LineAmountTypes' => $request['LineAmountTypes'],
                    'LineItems' => $request['LineItems']
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            dd($result); exit;
            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in submitting invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function generateInvoicesZoho($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();
        try {
            $data = [
                'reference_number' => $request['reference_number'],              
                'customer_id' => $request['customer_id'],
                'date' => $request['date'],
                'due_date' => $request['due_date'],
                'is_inclusive_tax' => $request['is_inclusive_tax'],
                'line_items' => $request['line_items']
            ];
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.ZOHO_INVOICES_URL'). '?organization_id=' . $xeroConfig['tenant_id'], $data);
            $response = $http->request('POST', $xeroConfig['url'] . Config::get('services.ZOHO_INVOICES_URL'). '?organization_id=' . $xeroConfig['tenant_id'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*'
                ],
                'json' => [    
                    'reference_number' => $request['reference_number'],              
                    'customer_id' => $request['customer_id'],
                    'date' => $request['date'],
                    'due_date' => $request['due_date'],
                    'is_inclusive_tax' => $request['is_inclusive_tax'],
                    'line_items' => $request['line_items']
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);
            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in submitting invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }    

    /**
     * @param $request
     * @return mixed
     */
    public function createContacts($request) : mixed
    {
        $http = new Client();
        $xeroConfig = $this->getXeroSettings();

        switch($xeroConfig['title']) {
            case 'XERO':
                if(isset($request['ContactID']) && !empty($request['ContactID'])){
                    $data = [
                        'ContactID'=>$request['ContactID'] ?? '',
                        'Name'=>$request['company_name'],
                        'ContactNumber'=> $request['contact_number'],
                        'AccountNumber' => $request['bank_account_number'],
                        'EmailAddress' => $request['email'],
                        'BankAccountDetails' => $request['bank_account_number'],
                        'TaxNumber' => $request['tax_id'],
                        'AccountsReceivableTaxType' => $request['account_receivable_tax_type'],
                        'AccountsPayableTaxType' => $request['account_payable_tax_type']
                        ];
                } else {
                    $data = [
                        'Name'=>$request['company_name'],
                        'ContactNumber'=> $request['contact_number'],
                        'AccountNumber' => $request['bank_account_number'],
                        'EmailAddress' => $request['email'],
                        'BankAccountDetails' => $request['bank_account_number'],
                        'TaxNumber' => $request['tax_id'],
                        'AccountsReceivableTaxType' => $request['account_receivable_tax_type'],
                        'AccountsPayableTaxType' => $request['account_payable_tax_type']
                    ];
                }
                
                try {
                    app('thirdPartyLogServices')->startApiLog($xeroConfig['url']. Config::get('services.XERO_CONTACTS_URL'), $data);
                    $response = $http->request('POST', $xeroConfig['url'] . Config::get('services.XERO_CONTACTS_URL'), [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                            'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                            'Accept' => 'application/json',
                        ],
                        'json' => $data,
                    ]);
                    $result = json_decode((string)$response->getBody(), true);
                    app('thirdPartyLogServices')->endApiLog($result);
                    return response()->json($result);
                } catch (Exception $e) {
                    Log::error('Exception in submitting contact details' . $e);
                    return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
                }
                break;                  
            case 'ZOHO':
                if(isset($request['ContactID']) && !empty($request['ContactID'])){
                    $data = [
                        'Contact_id'=>$request['ContactID'] ?? '',
                        'contact_name'=>$request['company_name'],
                        'company_name'=>$request['company_name'],
                        "contact_type"=>"customer",
                        "customer_sub_type"=>"business",
                        'contact_persons' => [[
                            'first_name' => $request['company_name'],
                            'last_name' => '',
                            'mobile' => $request['contact_number'],
                            'phone' => '',
                            'email' => $request['email'],
                            'is_primary_contact' => true,
                            'enable_portal' => false
                        ]]
                    ];
                } else {
                    $data = [
                        'contact_name'=>$request['company_name'],
                        'company_name'=>$request['company_name'],
                        "contact_type"=>"customer",
                        "customer_sub_type"=>"business",
                        'contact_persons' => [[
                            'first_name' => $request['company_name'],
                            'last_name' => '',
                            'mobile' => $request['contact_number'],
                            'phone' => '',
                            'email' => $request['email'],
                            'is_primary_contact' => true,
                            'enable_portal' => false
                        ]]
                    ];
                }     
                
                try {
                    app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.ZOHO_CONTACTS_URL'). '?organization_id=' . $xeroConfig['tenant_id'], $data);
                    $response = $http->request('POST', $xeroConfig['url'] . Config::get('services.ZOHO_CONTACTS_URL'). '?organization_id=' . $xeroConfig['tenant_id'], [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                            'Content-Type' => 'application/json',
                            'Accept' => '*/*'
                        ],
                        'json' => $data,
                    ]);
                    $result = json_decode((string)$response->getBody(), true);
                    return response()->json($result);
                } catch (Exception $e) {
                    Log::error('Exception in submitting contact details' . $e);
                    return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
                }
                break; 
            default:
                return false;
        }

        
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAccessToken() : mixed
    {
        $http = new Client();
        $cronConfig = $this->getCronSettings();

        try {
            foreach($cronConfig as $clients){
                switch($clients['title']) {
                    case 'XERO':
                        $response = $http->request('POST', Config::get('services.XERO_TOKEN_URL'), [
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($clients['client_id'] . ":" . $clients['client_secret']),
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Accept' => 'application/json',
                            ], 
                            'form_params' => [
                                'grant_type' => 'refresh_token',
                                'client_id' => $clients['client_id'],
                                'refresh_token' => $clients['refresh_token'],
                            ],
                        ]);
                        break;                  
                    case 'ZOHO':
                        $response = $http->request('POST', Config::get('services.ZOHO_TOKEN_URL'). '?refresh_token=' . $clients['refresh_token'] . '&client_id=' . $clients['client_id'] . '&client_secret=' . $clients['client_secret'] . '&redirect_uri=' . $clients['redirect_url'] . '&grant_type=refresh_token', [
                            'headers' => [
                                'Content-Type' => 'multipart/form-data; boundary=XXX',
                                'Accept' => '*/*'
                            ], 
                        ]);
                        
                        break; 
                    default:
                        $response = '';                    
                }
                if(isset($response) && !empty($response)){
                    $result = json_decode((string)$response->getBody(), true);
                    $newConfig = $this->xeroSettings->find($clients['id']);
                    $newConfig->refresh_token = $result['refresh_token'] ?? $clients['refresh_token'];
                    $newConfig->access_token = $result['access_token'];
                    $newConfig->save();
                }                    
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting refresh token' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getXeroSettings() : mixed
    { 
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroSettings->where('company_id',$user['company_id'])->first();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getCronSettings() : mixed
    {
        return $this->xeroSettings->whereNull('deleted_at')->get();
    }

}

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

        $generateInvoice['Type'] = 'ACCREC';
        $issuedateConverted = (Carbon::parse($params['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['Date'] = '/Date('.$issuedateConverted.')/';
        $duedateConverted = (Carbon::parse($params['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['DueDate'] = '/Date('.$duedateConverted.')/';
        $generateInvoice['DateString'] = $params['issue_date']."T00:00:00";
        $generateInvoice['DueDateString'] = $params['due_date']."T00:00:00";
        $generateInvoice['LineAmountTypes'] = 'Exclusive';

        $crmProspect = $this->crmProspect->findOrFail($request['crm_prospect_id']);
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
            $invoiceXeroData = $this->getInvoices($invoiceData);
            if(isset($invoiceXeroData->original['Invoices'][0]['InvoiceNumber'])){
                $invoiceData->due_amount = $invoiceXeroData->original['Invoices'][0]['AmountDue'];
                $invoiceData->due_date = Carbon::parse($invoiceXeroData->original['Invoices'][0]['DueDateString'])->format('Y-m-d');
                $invoiceData->invoice_status = $invoiceXeroData->original['Invoices'][0]['Status'];
                $invoiceData->save();
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
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_TAX_RATES_URL'), [
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
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_TAX_RATES_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
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
            
            return true;
        } catch (Exception $e) {
            Log::error('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetTaxRates($request) : mixed
    {
        return $this->xeroTaxRates
            ->select('id', 'name', 'tax_type', 'report_tax_type', 'can_applyto_assets', 'can_applyto_equity', 'can_applyto_expenses', 'can_applyto_liabilities', 'can_applyto_revenue', 'display_tax_rate', 'effective_rate', 'status')
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
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_ITEMS_URL'), [
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
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_ITEMS_URL'), [
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
            Log::error('Exception in getting Items details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetItems($request) : mixed
    {
        return $this->xeroItems
            ->select('id', 'item_id', 'code', 'description', 'purchase_description', 'name', 'is_tracked_as_inventory', 'is_sold', 'is_purchased')
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
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_ACCOUNTS_URL'), [
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
        $xeroConfig = $this->getXeroSettings();
        try {
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_ACCOUNTS_URL'), [
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
                        ]
                    );
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Exception in getting Account details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function xeroGetaccounts($request) : mixed
    {
        return $this->xeroAccounts
            ->select('id', 'account_id', 'code', 'name', 'status', 'type', 'tax_type','class','enable_payments_to_account','show_in_expense_claims', 'bank_account_number','bank_account_type', 'currency_code', 'reporting_code', 'reporting_code_name')
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
            $response = $http->request('GET', Config::get('services.XERO_URL') . Config::get('services.XERO_INVOICES_URL'). $rawUrl, [
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
            $response = $http->request('POST', Config::get('services.XERO_URL') . Config::get('services.XERO_INVOICES_URL'), [
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
            $response = $http->request('POST', Config::get('services.XERO_URL') . Config::get('services.XERO_CONTACTS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'json' => $data,
            ]);
            $result = json_decode((string)$response->getBody(), true);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in submitting contact details' . $e);
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
        $xeroConfig = $this->getXeroSettings();

        try {
            $response = $http->request('POST', Config::get('services.XERO_TOKEN_URL'), [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($xeroConfig['client_id'] . ":" . $xeroConfig['client_secret']),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ], 
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => $xeroConfig['client_id'],
                    'refresh_token' => $xeroConfig['refresh_token'],
                ],
            ]);
            $result = json_decode((string)$response->getBody(), true);

            $xeroConfig->refresh_token = $result['refresh_token'];
            $xeroConfig->access_token = $result['access_token'];
            $xeroConfig->save();

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting refresh token' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getXeroSettings() : mixed
    {
        return $this->xeroSettings->find(1);
    }

}

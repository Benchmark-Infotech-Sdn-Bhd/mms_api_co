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
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use App\Services\EmailServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZohoServices
{
    public const SERVICE_ID_DIRECTRECRUITMENT = 1;
    public const SERVICE_ID_ECONTRACT = 2;

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
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var EmailServices
     */
    private EmailServices $emailServices;

    /**
     * ZohoServices constructor.
     * 
     * @param Invoice $Invoice Instance of the Invoice class
     * @param InvoiceItems $invoiceItems Instance of the InvoiceItems class
     * @param InvoiceItemsTemp $invoiceItemsTemp Instance of the InvoiceItemsTemp class
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses Instance of the DirectRecruitmentExpenses class
     * @param EContractCostManagement $eContractCostManagement Instance of the EContractCostManagement class
     * @param TotalManagementCostManagement $totalManagementCostManagement Instance of the TotalManagementCostManagement class
     * @param CRMProspect $crmProspect Instance of the CRMProspect class
     * @param XeroSettings $xeroSettings Instance of the XeroSettings class
     * @param XeroTaxRates $xeroTaxRates Instance of the XeroTaxRates class
     * @param XeroAccounts $xeroAccounts Instance of the XeroAccounts class
     * @param XeroItems $xeroItems Instance of the XeroItems class
     * @param AuthServices $authServices Instance of the AuthServices class
     * @param EmailServices $emailServices Instance of the EmailServices class
     * 
     * @return void
     * 
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
            AuthServices                $authServices,
            EmailServices               $emailServices
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
        $this->authServices = $authServices;
        $this->emailServices = $emailServices;
    }
    
    /**
     * Add the heaer params.
     * 
     * @param array $clients
     * 
     * @return array
     */
    private function getHeaders($clients): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $clients['access_token'],
                'Content-Type' => 'application/json',
                'Accept' => '*/*'
            ],
        ];
    }

    /**
     * create Zoho TaxRate
     *
     * @param array $clients
     * @param array $row The row containg the tax rate data
     * 
     * @return void
     */
    private function createZohoTaxRate($clients, $row): void
    {
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

     /**
     * create Zoho Items
     *
     * @param array $clients
     * @param array $row The row containg the items data
     * 
     * @return void
     */
    private function createZohoItems($clients, $row): void
    {
        $this->xeroItems->updateOrCreate(
            [
                'item_id' => $row['item_id'],
                'company_id' => $clients['company_id'],
            ],
            [   
                'code' => $row['code'] ?? null,
                'description' => $row['description'] ?? null, 
                'purchase_description' => $row['PurchaseDescription'] ?? null, 
                'name' => $row['name'] ?? null, 
                'is_tracked_as_inventory' => $row['IsTrackedAsInventory'] ?? null, 
                'is_sold' => $row['IsSold'] ?? null,
                'is_purchased' => $row['IsPurchased'] ?? null,                                            
                'status' => $row['status'] ?? null, 
                'rate' => $row['rate'] ?? null, 
                'item_type' => $row['item_type'] ?? null, 
                'product_type' => $row['product_type'] ?? null, 
                'sku' => $row['sku'] ?? null,                                             
            ]
        );
    }

    /**
     * create Zoho Accounts
     *
     * @param array $clients
     * @param array $row The row containg the account data
     * 
     * @return void
     */
    private function createZohoAccounts($clients, $row): void
    {
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

    /**
     * Update the Expense
     *
     * @param array $item
     * @param int $invoiceNumber
     * 
     * @return void
     */
    private function updateExpense($item, $invoiceNumber){
        if($item['service_id'] == self::SERVICE_ID_DIRECTRECRUITMENT){
            $this->directRecruitmentExpenses->where('id', $item['expense_id'])->update([
                    'invoice_number' => $invoiceNumber
            ]);
        } else if($item['service_id'] == self::SERVICE_ID_ECONTRACT){
            $this->eContractCostManagement->where('id', $item['expense_id'])->update([
                    'invoice_number' => $invoiceNumber
            ]);
        }
        else {
            $this->totalManagementCostManagement->where('id', $item['expense_id'])->update([
                    'invoice_number' => $invoiceNumber
            ]);
        }
    }

    /**
     * Save tax rates from Zoho.
     * 
     * @param array $clients
     * 
     * @return mixed
     */
    public function saveTaxRates($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('GET', $clients['url'] . Config::get('services.ZOHO_TAX_RATES_URL'). '?organization_id=' . $clients['tenant_id'], $this->getHeaders($clients));
            $result = json_decode((string)$response->getBody(), true);
            if(isset($result['taxes'])){
                foreach ($result['taxes'] as $row) {
                    $this->createZohoTaxRate($clients, $row);
                }
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Save tax items from Zoho.
     * 
     * @param array $clients
     * 
     * @return mixed
     */
    public function saveItems($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('GET', $clients['url'] . Config::get('services.ZOHO_ITEMS_URL'). '?organization_id=' . $clients['tenant_id'], $this->getHeaders($clients));
            $result = json_decode((string)$response->getBody(), true);
            //dd($result); exit;
            if(isset($result['items'])){
                foreach ($result['items'] as $row) {
                    $this->createZohoItems($clients, $row);
                }
            }            
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }
    /**
     * Save the accounts from Zoho
     * 
     * @param array $clients
     * 
     * @return mixed
     */
    public function saveAccounts($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('GET', $clients['url'] . Config::get('services.ZOHO_ACCOUNTS_URL'). '?organization_id=' . $clients['tenant_id'], $this->getHeaders($clients));
            $result = json_decode((string)$response->getBody(), true);
            if(isset($result['chartofaccounts'])){
                foreach ($result['chartofaccounts'] as $row) {
                    $this->createZohoAccounts($clients, $row);
                }
            }          
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }
    /**
     * Get the access token
     * 
     * @param array $clients
     * 
     * @return mixed
     */
    public function getAccessToken($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('POST', Config::get('services.ZOHO_TOKEN_URL'). '?refresh_token=' . $clients['refresh_token'] . '&client_id=' . $clients['client_id'] . '&client_secret=' . $clients['client_secret'] . '&redirect_uri=' . $clients['redirect_url'] . '&grant_type=refresh_token', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data; boundary=XXX',
                    'Accept' => '*/*'
                ], 
            ]);
            if(isset($response) && !empty($response)){
                $result = json_decode((string)$response->getBody(), true);
                $newConfig = $this->xeroSettings->find($clients['id']);
                $newConfig->refresh_token = $result['refresh_token'] ?? $clients['refresh_token'];
                $newConfig->access_token = $result['access_token'];
                $newConfig->save();
            }        
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Get the invoice
     * 
     * @param object $request
     * @param array $xeroConfig
     * 
     * @return mixed
     */
    public function getInvoices($request, $xeroConfig) : mixed
    {
        $http = new Client();
        //$xeroConfig = $this->getXeroSettings();
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
            if(isset($result['invoice']['invoice_id'])){
                $request->due_amount = $result['invoice']['balance'];
                $request->due_date = $result['invoice']['due_date'];
                $request->invoice_status = $result['invoice']['status'];
                $request->save();
            }
            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in getting Invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Create the contact
     * 
     * @param $request
     * @param $xeroConfig
     * 
     * @return mixed
     */
    public function createContacts($request, $xeroConfig) : mixed
    {
        $http = new Client();
        if(isset($request['ContactID']) && !empty($request['ContactID'])){
            $method = 'PUT';
            $contactIdUrl = '/'.$request['ContactID'];
        } else {
            $method = 'POST';
            $contactIdUrl = '';
        }     

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
        
        try {
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.ZOHO_CONTACTS_URL'). $contactIdUrl . '?organization_id=' . $xeroConfig['tenant_id'], $data);
            $response = $http->request($method, $xeroConfig['url'] . Config::get('services.ZOHO_CONTACTS_URL'). $contactIdUrl . '?organization_id=' . $xeroConfig['tenant_id'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*'
                ],
                'json' => $data,
            ]);
            $result = json_decode((string)$response->getBody(), true);
            if(isset($result['contact']['contact_id'])){
                $prospectData = $this->crmProspect->findOrFail($request['prospect_id']);
                $prospectData->xero_contact_id = $result['contact']['contact_id'];
                $prospectData->save();
            }

            app('thirdPartyLogServices')->endApiLog($result);
            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in submitting contact details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Create the invoice
     * 
     * @param $request
     * @param $xeroConfig
     * 
     * @return mixed
     */
    public function createInvoice($request, $xeroConfig) : mixed
    {
        $crmProspect = $this->crmProspect->findOrFail($request['crm_prospect_id']);

        $generateInvoice['date'] = $params['issue_date'] ?? null;
        $generateInvoice['due_date'] = $params['due_date'] ?? null;
        $generateInvoice['reference_number'] = $params['reference_number'] ?? null;
        $generateInvoice['customer_id'] = $crmProspect->xero_contact_id;
        $generateInvoice['is_inclusive_tax'] = '';

        $lineItems = json_decode($request['invoice_items']);
        
        if ($request['invoice_items']){
            $increment = 0;
            foreach($lineItems as $item){

                $taxData = $this->xeroTaxRates::find($item->tax_id);                
                $itemData = $this->xeroItems::find($item->item_id);
                $accountData = $this->xeroAccounts::find($item->account_id);
                
                $this->invoiceItems::create([
                    "invoice_id" => $request['invoice_id'],
                    "service_id" => $taxData['service_id'] ?? null,
                    "expense_id" => $taxData['expense_id'] ?? null,
                    "item" => $itemData['item_id'] ?? '',
                    "description" => $item->description,
                    "quantity" => $item->quantity,
                    "price" => $item->price,
                    "account" => $accountData['account_id'] ?? '',
                    "tax" => $item->tax_rate ?? 0,
                    "tax_id" => $taxData['tax_id'] ?? '',
                    "total_price" => $item->total_price
                ]);

                $generateInvoice['line_items'][$increment] = new \stdClass();
                $generateInvoice['line_items'][$increment]->item_order = $item->item_order ?? '';
                $generateInvoice['line_items'][$increment]->item_id = $itemData['item_id'] ?? '';
                $generateInvoice['line_items'][$increment]->name = $item->item ?? '';
                $generateInvoice['line_items'][$increment]->rate = $item->price;
                $generateInvoice['line_items'][$increment]->description = $item->description;
                $generateInvoice['line_items'][$increment]->quantity = $item->quantity;
                $generateInvoice['line_items'][$increment]->tax_id = $taxData['tax_id'] ?? '';
                $generateInvoice['line_items'][$increment]->account_id = $accountData['account_id'] ?? ''; 
                $increment++;
            }
        }

        $generateInvoiceXero = $this->generateInvoices($generateInvoice, $xeroConfig);   

        if(isset($generateInvoiceXero->original['invoice']['invoice_id'])){

            $invoiceData = $this->invoice->findOrFail($request['invoice_id']);
            $invoiceData->invoice_number = $generateInvoiceXero->original['invoice']['invoice_id'];
            $invoiceData->zoho_invoice_number = $generateInvoiceXero->original['invoice']['invoice_number'];
            $invoiceData->due_amount = $generateInvoiceXero->original['invoice']['balance'];
            $invoiceData->invoice_status = $generateInvoiceXero->original['invoice']['status'];
            $invoiceData->save();

            // Delete from temporary table
            $this->invoiceItemsTemp->where('created_by', $request['created_by'])->delete();

            foreach($lineItems as $item){
                $this->updateExpense($item, $generateInvoiceXero->original['invoice']['invoice_id']);
            }
        }
        return true;
    }

    /**
     * Generate the Invoice
     * 
     * @param $request
     * @param $xeroConfig
     * 
     * @return mixed
     */
    public function generateInvoices($request, $xeroConfig) : mixed
    {
        $http = new Client();
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
     * Invoice Resubmit
     * 
     * @param $invoice
     * @param $xeroConfig
     * 
     * @return mixed
     */
    public function invoiceReSubmit($invoice, $xeroConfig) : mixed
    {
        $generatedInvoiceNumber = '';

        $invoiceData = $this->invoice->findOrFail($invoice['id']);
        $invoiceData->resubmit_count = $invoiceData->resubmit_count + 1;
        $invoiceData->save();

        $crmProspect = $this->crmProspect->findOrFail($invoice['crm_prospect_id']);
                        
        $generateInvoice['date'] = $invoice['issue_date'] ?? null;
        $generateInvoice['due_date'] = $invoice['due_date'] ?? null;
        $generateInvoice['reference_number'] = $invoice['reference_number'] ?? null;
        $generateInvoice['customer_id'] = $crmProspect->xero_contact_id;
        $generateInvoice['is_inclusive_tax'] = '';
    
        if ($invoice['invoiceItems']){
            $increment = 0;
            foreach($invoice['invoiceItems'] as $item){
                
                $generateInvoice['line_items'][$increment] = new \stdClass();
                $generateInvoice['line_items'][$increment]->item_order = '';
                $generateInvoice['line_items'][$increment]->item_id = $item['item'];
                $generateInvoice['line_items'][$increment]->name = '';
                $generateInvoice['line_items'][$increment]->rate = $item['price'];
                $generateInvoice['line_items'][$increment]->description = $item['description'];
                $generateInvoice['line_items'][$increment]->quantity = $item['quantity'];
                $generateInvoice['line_items'][$increment]->tax_id = $item['tax_id'] ?? '';
                $generateInvoice['line_items'][$increment]->account_id = $item['account'] ?? ''; 
                $increment++;
            }
        }

        $generateInvoiceXero = $this->generateInvoices($generateInvoice, $xeroConfig);  
        
        if(isset($generateInvoiceXero->original['invoice']['invoice_id'])){

            $invoiceData->invoice_number = $generatedInvoiceNumber = $generateInvoiceXero->original['invoice']['invoice_id'];
            $invoiceData->zoho_invoice_number = $generateInvoiceXero->original['invoice']['invoice_number'];
            $invoiceData->due_amount = $generateInvoiceXero->original['invoice']['balance'];
            $invoiceData->invoice_status = $generateInvoiceXero->original['invoice']['status'];
            $invoiceData->save();

            // Delete from temporary table
            //$this->invoiceItemsTemp->where('created_by', $user['id'])->delete();

            foreach($invoice['invoiceItems'] as $item){
                $this->invoiceItemsTemp->where('service_id', $item['service_id'])->where('expense_id', $item['expense_id'])->delete();
                $this->updateExpense($item, $generateInvoiceXero->original['invoice']['invoice_id']);
            }
        }

        $mailParams['company_name'] = $crmProspect->company_name;
        $mailParams['company_email'] = $crmProspect->email;
        $mailParams['reference_number'] = $invoice['reference_number'];
        $mailParams['email'] = Config::get('services.INVOICE_RESUBMISSION_FAILED_MAIL');

        Log::channel('cron_activity_logs')->info('Checking mail '.__LINE__);
        Log::channel('cron_activity_logs')->info('Generated Invoice Number ' . $generatedInvoiceNumber);
        if($invoiceData->resubmit_count >= 3 && !empty($generatedInvoiceNumber)){
            Log::channel('cron_activity_logs')->info('Sending mail ' . print_r($mailParams));
            $this->emailServices->sendInvoiceResubmissionFailedMail($mailParams);
        }

        return true;
    }
}

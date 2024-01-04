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

class XeroServices
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
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var EmailServices
     */
    private EmailServices $emailServices;

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
     * @param AuthServices $authServices
     * @param EmailServices $emailServices
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
     * @param $request
     * @return mixed
     */
    public function saveTaxRates($clients) : mixed
    {
        $http = new Client();
        try {            
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
    public function saveItems($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('GET', $clients['url'] . Config::get('services.XERO_ITEMS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $clients['access_token'],
                    'Xero-Tenant-Id' => $clients['tenant_id'],
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
                            'company_id' => $clients['company_id'],
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
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function saveAccounts($clients) : mixed
    {
        $http = new Client();
        try {            
            $response = $http->request('GET', $clients['url'] . Config::get('services.XERO_ACCOUNTS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $clients['access_token'],
                    'Xero-Tenant-Id' => $clients['tenant_id'],
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
    public function getAccessToken($clients) : mixed
    {
        $http = new Client();
        try {            
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
     * @param $request
     * @return mixed
     */
    public function getInvoices($request, $xeroConfig) : mixed
    {
        $http = new Client();
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
            
            if(isset($result['Invoices'][0]['InvoiceNumber'])){
                $request->due_amount = $result['Invoices'][0]['AmountDue'];
                $request->due_date = Carbon::parse($result['Invoices'][0]['DueDateString'])->format('Y-m-d');
                $request->invoice_status = $result['Invoices'][0]['Status'];
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
     * @param $request
     * @return mixed
     */
    public function createContacts($request, $xeroConfig) : mixed
    {
        $http = new Client();
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
            if(isset($result['Contacts'][0]['ContactID'])){
                $prospectData = $this->crmProspect->findOrFail($request['prospect_id']);
                $prospectData->xero_contact_id = $result['Contacts'][0]['ContactID'];
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
     * @param $request
     * @return mixed
     */
    public function createInvoice($request, $xeroConfig) : mixed
    {
        $crmProspect = $this->crmProspect->findOrFail($request['crm_prospect_id']);

        $generateInvoice['Type'] = 'ACCREC';
        $issuedateConverted = (Carbon::parse($request['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['Date'] = '/Date('.$issuedateConverted.')/';
        $duedateConverted = (Carbon::parse($request['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['DueDate'] = '/Date('.$duedateConverted.')/';
        $generateInvoice['DateString'] = $request['issue_date']."T00:00:00";
        $generateInvoice['DueDateString'] = $request['due_date']."T00:00:00";
        $generateInvoice['LineAmountTypes'] = 'Exclusive';
        $generateInvoice['Contact']['ContactID'] = $crmProspect->xero_contact_id;

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
                    "item" => $itemData['code'] ?? '',
                    "description" => $item->description,
                    "quantity" => $item->quantity,
                    "price" => $item->price,
                    "account" => $accountData['code'] ?? '',
                    "tax" => $item->tax_rate ?? 0,
                    "tax_id" => $taxData['tax_type'] ?? '',
                    "total_price" => $item->total_price
                ]);

                $generateInvoice['LineItems'][$increment] = new \stdClass();
                $generateInvoice['LineItems'][$increment]->ItemCode = $itemData['code'] ?? '';
                $generateInvoice['LineItems'][$increment]->Description = $item->description;
                $generateInvoice['LineItems'][$increment]->Quantity = $item->quantity;
                $generateInvoice['LineItems'][$increment]->UnitAmount = $item->price;
                $generateInvoice['LineItems'][$increment]->AccountCode = $accountData['code'] ?? '';
                $generateInvoice['LineItems'][$increment]->TaxType = $taxData['tax_type'] ?? 0;
                $increment++;

            }
        }

        $generateInvoiceXero = $this->generateInvoices($generateInvoice, $xeroConfig);   
        
        if(isset($generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'])){

            $invoiceData = $this->invoice->findOrFail($request['invoice_id']);
            $invoiceData->invoice_number = $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'];
            $invoiceData->due_amount = $generateInvoiceXero->original['Invoices'][0]['AmountDue'];
            $invoiceData->invoice_status = $generateInvoiceXero->original['Invoices'][0]['Status'];
            $invoiceData->save();

            // Delete from temporary table
            $this->invoiceItemsTemp->where('created_by', $request['created_by'])->delete();

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

        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function generateInvoices($request, $xeroConfig) : mixed
    {
        $http = new Client();
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
            $response = $http->request('POST', $xeroConfig['url']  . Config::get('services.XERO_INVOICES_URL'), [
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
    public function invoiceReSubmit($invoice, $xeroConfig) : mixed
    {
        $generatedInvoiceNumber = '';

        $invoiceData = $this->invoice->findOrFail($invoice['id']);
        $invoiceData->resubmit_count = $invoiceData->resubmit_count + 1;
        $invoiceData->save();

        $crmProspect = $this->crmProspect->findOrFail($invoice['crm_prospect_id']);

        $generateInvoice['Type'] = 'ACCREC';
        $issuedateConverted = (Carbon::parse($invoice['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['Date'] = '/Date('.$issuedateConverted.')/';
        $duedateConverted = (Carbon::parse($invoice['due_date'])->timestamp * 1000)."+0000";
        $generateInvoice['DueDate'] = '/Date('.$duedateConverted.')/';
        $generateInvoice['DateString'] = $invoice['issue_date']."T00:00:00";
        $generateInvoice['DueDateString'] = $invoice['due_date']."T00:00:00";
        $generateInvoice['LineAmountTypes'] = 'Exclusive';
        $generateInvoice['Contact']['ContactID'] = $crmProspect->xero_contact_id;

        if ($invoice['invoiceItems']){
            $increment = 0;
            foreach($invoice['invoiceItems'] as $item){
                $generateInvoice['LineItems'][$increment] = new \stdClass();
                $generateInvoice['LineItems'][$increment]->ItemCode = $item['item'] ?? '';
                $generateInvoice['LineItems'][$increment]->Description = $item['description'];
                $generateInvoice['LineItems'][$increment]->Quantity = $item['quantity'];
                $generateInvoice['LineItems'][$increment]->UnitAmount = $item['price'];
                $generateInvoice['LineItems'][$increment]->AccountCode = $item['account'] ?? '';
                $generateInvoice['LineItems'][$increment]->TaxType = $item['tax_id'] ?? '';
                $increment++;

            }
        }

        $generateInvoiceXero = $this->generateInvoices($generateInvoice, $xeroConfig); 

        if(isset($generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'])){

            $invoiceData->invoice_number = $generatedInvoiceNumber = $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'];
            $invoiceData->due_amount = $generateInvoiceXero->original['Invoices'][0]['AmountDue'];
            $invoiceData->invoice_status = $generateInvoiceXero->original['Invoices'][0]['Status'];
            $invoiceData->save();

            foreach($invoice['invoiceItems'] as $item){
                $this->invoiceItemsTemp->where('service_id', $item['service_id'])->where('expense_id', $item['expense_id'])->delete();
                if($item['service_id'] == 1){
                    $this->directRecruitmentExpenses->where('id', $item['expense_id'])->update([
                            'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                    ]);
                } else if($item['service_id'] == 2){
                    $this->eContractCostManagement->where('id', $item['expense_id'])->update([
                            'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                    ]);
                }
                else if($item['service_id'] == 3){
                    $this->totalManagementCostManagement->where('id', $item['expense_id'])->update([
                            'invoice_number' => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                    ]);
                }
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

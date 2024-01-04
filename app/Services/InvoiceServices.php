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
use App\Services\XeroServices;
use App\Services\ZohoServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use App\Services\EmailServices;
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
     * @var XeroServices
     */
    private XeroServices $xeroServices;
    /**
     * @var ZohoServices
     */
    private ZohoServices $zohoServices;    
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var EmailServices
     */
    private EmailServices $emailServices;
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
     * @param XeroServices $xeroServices
     * @param ZohoServices $zohoServices
     * @param AuthServices $authServices
     * @param EmailServices $emailServices
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
            XeroServices                $xeroServices,
            ZohoServices                $zohoServices,
            AuthServices                $authServices,
            EmailServices               $emailServices,
            Storage                     $storage
    )
    {
        $this->invoice = $invoice;
        //$this->invoiceItems = $invoiceItems;
        //$this->invoiceItemsTemp = $invoiceItemsTemp;
        $this->xeroSettings = $xeroSettings;
        $this->xeroTaxRates = $xeroTaxRates;
        $this->xeroAccounts = $xeroAccounts;
        $this->xeroItems = $xeroItems;
        //$this->directRecruitmentExpenses = $directRecruitmentExpenses;
        //$this->eContractCostManagement = $eContractCostManagement;
        //$this->totalManagementCostManagement = $totalManagementCostManagement;
        //$this->crmProspect = $crmProspect;
        $this->validationServices = $validationServices;
        $this->xeroServices = $xeroServices;
        $this->zohoServices = $zohoServices;
        $this->authServices = $authServices;
        //$this->emailServices = $emailServices;
        //$this->storage = $storage;
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
            'crm_prospect_id' => $params['crm_prospect_id'],
            'issue_date' => ((isset($params['issue_date']) && !empty($params['issue_date'])) ? $params['issue_date'] : null),
            'due_date' => ((isset($params['due_date']) && !empty($params['due_date'])) ? $params['due_date'] : null),
            'reference_number' => $params['reference_number'] ?? '',
            'tax' => $params['tax'] ?? 0,
            'amount' => $params['amount'] ?? 0,
            'due_amount' => $params['due_amount'] ?? 0,
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0,
            'company_id' => $user['company_id'],
            'remarks' => $params['remarks'] ?? ''
        ]);

        $params['invoice_id'] = $invoice['id'];

        match ($accountSystem['title']) {
            'XERO' => $this->xeroServices->createInvoice($params, $accountSystem),
            'ZOHO' => $this->zohoServices->createInvoice($params, $accountSystem),
        }; 

        return $invoice;

    }
    
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $invoiceData = $this->invoice->where('company_id', $user['company_id'])->find($request['id']);        
        if(isset($invoiceData) && !empty($invoiceData)){
            $accountSystem = $this->getXeroSettings();
            match ($accountSystem['title']) {
                'XERO' => $this->xeroServices->getInvoices($invoiceData, $accountSystem),
                'ZOHO' => $this->zohoServices->getInvoices($invoiceData, $accountSystem),
            };
        }
        $data = $this->invoice->with('invoiceItems')->where('company_id', $user['company_id'])->find($request['id']);
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
    public function saveTaxRates() : mixed
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach($cronConfig as $clients){
                match ($clients['title']) {
                    'XERO' => $this->xeroServices->saveTaxRates($clients),
                    'ZOHO' => $this->zohoServices->saveTaxRates($clients),
                };
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
    public function saveItems() : mixed
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach($cronConfig as $clients){
                match ($clients['title']) {
                    'XERO' => $this->xeroServices->saveItems($clients),
                    'ZOHO' => $this->zohoServices->saveItems($clients),
                };
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
    public function xeroGetItems($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroItems
            ->select('id', 'item_id', 'code', 'description', 'purchase_description', 'name', 'is_tracked_as_inventory', 'is_sold', 'is_purchased', 'company_id', 'status', 'rate', 'item_type', 'product_type', 'sku')
            ->where('company_id',$user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function saveAccounts() : mixed
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach($cronConfig as $clients){
                match ($clients['title']) {
                    'XERO' => $this->xeroServices->saveAccounts($clients),
                    'ZOHO' => $this->zohoServices->saveAccounts($clients),
                };
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
    public function createContacts($request) : mixed
    {
        $accountSystem = $this->getXeroSettings();
        try {
            match ($accountSystem['title']) {
                'XERO' => $this->xeroServices->createContacts($request, $accountSystem),
                'ZOHO' => $this->zohoServices->createContacts($request, $accountSystem),
            };  
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in submitting contact details' . $e);
            return false;
        }     
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAccessToken() : mixed
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach($cronConfig as $clients){
                match ($clients['title']) {
                    'XERO' => $this->xeroServices->getAccessToken($clients),
                    'ZOHO' => $this->zohoServices->getAccessToken($clients),
                };
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


    /**
     * @param $request
     * @return mixed
     */
    public function invoiceReSubmit() : mixed
    {
        
        $pendingInvoices = $this->invoice->with('invoiceItems')
        ->join('xero_settings', 'invoice.company_id', 'xero_settings.company_id')
        ->SELECT('xero_settings.*','invoice.*')
        ->where('invoice.resubmit_count', '<', 3 )
        ->whereNull('invoice.deleted_at')
        ->whereNull('invoice.invoice_number')->get();

        try {
            foreach($pendingInvoices as $invoice){

                $accountSystem['title'] = $invoice['title'];
                $accountSystem['url'] = $invoice['url'];
                $accountSystem['access_token'] = $invoice['access_token'];
                $accountSystem['tenant_id'] = $invoice['tenant_id'];

                match ($accountSystem['title']) {
                    'XERO' => $this->xeroServices->invoiceReSubmit($invoice, $accountSystem),
                    'ZOHO' => $this->zohoServices->invoiceReSubmit($invoice, $accountSystem),
                };                 
            }

            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in Re submitting the invoices ' . $e);
            return false;
        }

    }

}

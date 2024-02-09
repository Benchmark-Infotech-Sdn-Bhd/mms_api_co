<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\XeroSettings;
use App\Models\XeroTaxRates;
use App\Models\XeroAccounts;
use App\Models\XeroItems;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceServices
{
    /**
     * @var Invoice
     */
    private Invoice $invoice;
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
    private array $services;

    /**
     * InvoiceServices constructor.
     * @param Invoice $invoice
     * @param XeroSettings $xeroSettings
     * @param XeroTaxRates $xeroTaxRates
     * @param XeroAccounts $xeroAccounts
     * @param XeroItems $xeroItems
     * @param ValidationServices $validationServices
     * @param XeroServices $xeroServices
     * @param ZohoServices $zohoServices
     * @param AuthServices $authServices
     */
    public function __construct(
        Invoice            $invoice,
        XeroSettings       $xeroSettings,
        XeroTaxRates       $xeroTaxRates,
        XeroAccounts       $xeroAccounts,
        XeroItems          $xeroItems,
        ValidationServices $validationServices,
        XeroServices       $xeroServices,
        ZohoServices       $zohoServices,
        AuthServices       $authServices,
    )
    {
        $this->invoice = $invoice;
        $this->xeroSettings = $xeroSettings;
        $this->xeroTaxRates = $xeroTaxRates;
        $this->xeroAccounts = $xeroAccounts;
        $this->xeroItems = $xeroItems;
        $this->validationServices = $validationServices;
        $this->xeroServices = $xeroServices;
        $this->zohoServices = $zohoServices;
        $this->authServices = $authServices;
        $this->services = [
            'XERO' => $this->xeroServices,
            'ZOHO' => new $this->zohoServices,
        ];
    }

    /**
     * Create a new invoice.
     *
     * @param $request - The request data.
     * @return array|Invoice The created invoice or an array if validation fails.
     * @throws Exception If the accounting service is not supported.
     */
    public function create($request)
    {
        $requestData = $request->all();
        $authenticatedUser = JWTAuth::parseToken()->authenticate();
        $accountingSettings = $this->getXeroSettings();
        $requestData['created_by'] = $authenticatedUser['id'];
        if (!($this->validationServices->validate($request->toArray(), $this->invoice->rules))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $invoice = $this->generateInvoiceParams($requestData, $authenticatedUser['company_id']);
        $requestData['invoice_id'] = $invoice['id'];
        $this->services[$accountingSettings['title']]->createInvoice($requestData, $accountingSettings);
        return $invoice;
    }

    /**
     * Generate invoice parameters based on the given input.
     *
     * @param array $params The input parameters for generating the invoice.
     *                     - 'crm_prospect_id': The CRM prospect ID related to the invoice.
     *                     - 'issue_date': The issue date of the invoice.
     *                     - 'due_date': The due date of the invoice.
     *                     - 'reference_number' (optional): The reference number of the invoice. Default is an empty string.
     *                     - 'tax' (optional): The tax amount of the invoice. Default is 0.
     *                     - 'amount' (optional): The total amount of the invoice. Default is 0.
     *                     - 'due_amount' (optional): The due amount of the invoice. Default is 0.
     *                     - 'created_by' (optional): The ID of the user who created the invoice. Default is 0.
     *                     - 'modified_by' (optional): The ID of the user who modified the invoice. Default is 0.
     *                     - 'remarks' (optional): Any remarks related to the invoice. Default is an empty string.
     * @param int $companyId The ID of the company associated with the invoice.
     * @return mixed The generated invoice parameters.
     */
    private function generateInvoiceParams(array $params, int $companyId)
    {
        return $this->invoice->create([
            'crm_prospect_id' => $params['crm_prospect_id'],
            'issue_date' => $this->extractDate($params, 'issue_date'),
            'due_date' => $this->extractDate($params, 'due_date'),
            'reference_number' => $params['reference_number'] ?? '',
            'tax' => $params['tax'] ?? 0,
            'amount' => $params['amount'] ?? 0,
            'due_amount' => $params['due_amount'] ?? 0,
            'created_by' => $params['created_by'] ?? 0,
            'modified_by' => $params['created_by'] ?? 0,
            'company_id' => $companyId,
            'remarks' => $params['remarks'] ?? ''
        ]);
    }

    /**
     * Extracts a date value from the given data array based on the specified field.
     * If the field is not found or the value is empty, null is returned.
     *
     * @param array $data The data array from which to extract the date.
     * @param string $field The field name to extract the date from.
     * @return ?string The extracted date value, or null if not found or empty.
     */
    private function extractDate(array $data, string $field): ?string
    {
        return (!empty($data[$field])) ? $data[$field] : null;
    }

    /**
     * Show the invoice data based on the given request.
     *
     * @param mixed $request The request object containing the necessary information.
     * @return mixed The invoice data if found, otherwise an error message.
     * @throws GuzzleException
     */
    public function show($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!($this->validationServices->validate($request, ['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $invoiceData = $this->fetchInvoiceData($request, $user);

        if (!empty($invoiceData)) {
            $accountSystem = $this->getXeroSettings();
            $this->retrieveInvoices($invoiceData, $accountSystem);
        }

        $data = $this->invoice->with('invoiceItems')->where('company_id', $user['company_id'])->find($request['id']);

        if (is_null($data)) {
            return [
                "message" => "Data not found"
            ];
        }

        return $data;
    }

    /**
     * Retrieve invoices from the specified account system.
     *
     * @param array $invoiceData The data required for retrieving the invoices.
     * @param array $accountSystem The account system information.
     *                            - 'title': The title of the account system (e.g., XERO, ZOHO).
     * @return void
     * @throws GuzzleException
     */
    private function retrieveInvoices($invoiceData, $accountSystem): void
    {
        $this->services[$accountSystem['title']]->getInvoices($invoiceData, $accountSystem);
    }

    /**
     * Fetches the invoice data based on the given request and user information.
     *
     * @param mixed $request The request object or array containing the invoice ID.
     * @param array $user The user information containing the company ID.
     * @return mixed The fetched invoice data or null if not found.
     */
    private function fetchInvoiceData($request, $user)
    {
        return $this->invoice->where('company_id', $user['company_id'])->find($request['id']);
    }

    /**
     * Get a list of invoices based on the search criteria.
     *
     * @param array $request The request parameters for filtering the invoices.
     *                       - 'search_param' (optional): The search parameter for filtering invoices. Minimum length is 3 characters.
     *                       - 'invoice_status' (optional): The status of the invoices to filter by.
     * @return LengthAwarePaginator|array The list of invoices or an array with validation errors if the request parameters are invalid.
     */
    public function list($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
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
                if (!empty($request['search_param'])) {
                    $query->where('invoice_number', 'like', "%{$request['search_param']}%");
                }
                if (!empty($request['invoice_status'])) {
                    $query->where('invoice_status', 'like', "%{$request['invoice_status']}%");
                }

            })->select('id', 'crm_prospect_id', 'issue_date', 'due_date', 'reference_number', 'tax', 'amount', 'due_amount', 'created_at', 'invoice_number', 'invoice_status')
            ->distinct()
            ->orderBy('created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Save tax rates for all configured clients.
     *
     * @return bool Returns true if the tax rates are successfully saved for all clients, otherwise false.
     * @throws GuzzleException
     */
    public function saveTaxRates(): bool
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach ($cronConfig as $clients) {
                $this->services[$clients['title']]->saveTaxRates($clients);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Fetches the tax rates from Xero API for the current user's company.
     *
     * @param mixed $request The request data (if any) needed to fetch the tax rates.
     * @return Collection The collection of tax rates fetched from Xero API.
     */
    public function xeroGetTaxRates($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroTaxRates
            ->select('id', 'name', 'tax_type', 'report_tax_type', 'can_applyto_assets', 'can_applyto_equity', 'can_applyto_expenses', 'can_applyto_liabilities', 'can_applyto_revenue', 'display_tax_rate', 'effective_rate', 'status', 'company_id', 'tax_id', 'tax_specific_type', 'output_tax_account_name', 'purchase_tax_account_name', 'tax_account_id', 'purchase_tax_account_id', 'is_inactive', 'is_value_added', 'is_default_tax', 'is_editable', 'last_modified_time')
            ->where('company_id', $user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Save items using the specified cron settings.
     *
     * @return bool Returns true if the items were successfully saved, false otherwise.
     * @throws GuzzleException
     */
    public function saveItems()
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach ($cronConfig as $clients) {
                $this->services[$clients['title']]->saveItems($clients);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Retrieve items from Xero based on the given request.
     *
     * @param $request - The request object.
     * @return Collection The collection of items retrieved from Xero.
     */
    public function xeroGetItems($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroItems
            ->select('id', 'item_id', 'code', 'description', 'purchase_description', 'name', 'is_tracked_as_inventory', 'is_sold', 'is_purchased', 'company_id', 'status', 'rate', 'item_type', 'product_type', 'sku')
            ->where('company_id', $user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Save accounts information for multiple clients.
     *
     * @return bool Returns true if the accounts information is successfully saved for all clients, false otherwise.
     * @throws GuzzleException
     */
    public function saveAccounts(): bool
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach ($cronConfig as $clients) {
                $this->services[$clients['title']]->saveAccounts($clients);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Get accounts from Xero.
     *
     * @param $request - The request object containing data for the API call.
     * @return Collection The collection of Xero accounts.
     */
    public function xeroGetaccounts($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroAccounts
            ->select('id', 'account_id', 'code', 'name', 'status', 'type', 'tax_type', 'class', 'enable_payments_to_account', 'show_in_expense_claims', 'bank_account_number', 'bank_account_type', 'currency_code', 'reporting_code', 'reporting_code_name', 'company_id', 'description', 'is_user_created', 'is_system_account', 'can_show_in_ze', 'parent_account_id', 'parent_account_name', 'depth', 'has_attachment', 'is_child_present')
            ->where('company_id', $user['company_id'])
            ->distinct('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Create contacts in the account system based on the provided request.
     *
     * @param mixed $request The request data containing contact details.
     * @return bool Whether the creation of contacts was successful or not.
     */
    public function createContacts($request): bool
    {
        $accountSystem = $this->getXeroSettings();
        try {
            $this->services[$accountSystem['title']]->createContacts($request, $accountSystem);
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in submitting contact details' . $e);
            return false;
        }
    }

    /**
     * Get the access token for the configured clients.
     *
     * @return bool Returns true if access tokens for all clients are successfully obtained, false otherwise.
     */
    public function getAccessToken()
    {
        $cronConfig = $this->getCronSettings();
        try {
            foreach ($cronConfig as $clients) {
                $this->services[$clients['title']]->getAccessToken($clients);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Get the Xero settings for the current user.
     *
     * @return mixed The Xero settings for the user.
     */
    public function getXeroSettings(): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->xeroSettings->where('company_id', $user['company_id'])->first();
    }

    /**
     * Get the cron settings from Xero.
     *
     * @return Collection The collection of cron settings from Xero.
     */
    public function getCronSettings()
    {
        return $this->xeroSettings->whereNull('deleted_at')->get();
    }


    /**
     * Resubmit pending invoices to the account system.
     *
     * @return bool Returns true if the invoices are successfully resubmitted, false otherwise.
     */
    public function invoiceReSubmit(): bool
    {

        $pendingInvoices = $this->invoice->with('invoiceItems')
            ->join('xero_settings', 'invoice.company_id', 'xero_settings.company_id')
            ->SELECT('xero_settings.*', 'invoice.*')
            ->where('invoice.resubmit_count', '<', 3)
            ->whereNull('invoice.deleted_at')
            ->whereNull('invoice.invoice_number')->get();

        try {
            foreach ($pendingInvoices as $invoice) {

                $accountSystem['title'] = $invoice['title'];
                $accountSystem['url'] = $invoice['url'];
                $accountSystem['access_token'] = $invoice['access_token'];
                $accountSystem['tenant_id'] = $invoice['tenant_id'];
                $this->services[$accountSystem['title']]->invoiceReSubmit($invoice, $accountSystem);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in Re submitting the invoices ' . $e);
            return false;
        }

    }

}

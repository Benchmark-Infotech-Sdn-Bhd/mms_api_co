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
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use stdClass;

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
     * @var EmailServices
     */
    private EmailServices $emailServices;

    /**
     * Constructor for the class.
     *
     * @param Invoice $invoice Instance of the Invoice model.
     * @param InvoiceItems $invoiceItems Instance of the InvoiceItems model.
     * @param InvoiceItemsTemp $invoiceItemsTemp Instance of the InvoiceItemsTemp model.
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses Instance of the DirectRecruitmentExpenses model.
     * @param EContractCostManagement $eContractCostManagement Instance of the EContractCostManagement model.
     * @param TotalManagementCostManagement $totalManagementCostManagement Instance of the TotalManagementCostManagement model.
     * @param CRMProspect $crmProspect Instance of the CRMProspect model.
     * @param XeroSettings $xeroSettings Instance of the XeroSettings model.
     * @param XeroTaxRates $xeroTaxRates Instance of the XeroTaxRates model.
     * @param XeroAccounts $xeroAccounts Instance of the XeroAccounts model.
     * @param XeroItems $xeroItems Instance of the XeroItems model.
     * @param EmailServices $emailServices Instance of the EmailServices model.
     *
     * @return void
     */
    public function __construct(
        Invoice                       $invoice,
        InvoiceItems                  $invoiceItems,
        InvoiceItemsTemp              $invoiceItemsTemp,
        DirectRecruitmentExpenses     $directRecruitmentExpenses,
        EContractCostManagement       $eContractCostManagement,
        TotalManagementCostManagement $totalManagementCostManagement,
        CRMProspect                   $crmProspect,
        XeroSettings                  $xeroSettings,
        XeroTaxRates                  $xeroTaxRates,
        XeroAccounts                  $xeroAccounts,
        XeroItems                     $xeroItems,
        EmailServices                 $emailServices
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
        $this->emailServices = $emailServices;
    }

    /**
     * Retrieves and saves tax rates from Xero API.
     *
     * @param array $clients An array containing client information.
     *                      - url: The Xero API URL.
     *                      - access_token: The access token for authentication.
     *                      - tenant_id: The ID of the Xero tenant.
     *                      - company_id: The ID of the company.
     * @return bool Returns true if the tax rates were successfully saved, false otherwise.
     * @throws GuzzleException
     */
    public function saveTaxRates($clients): bool
    {
        $http = new Client();
        try {
            $response = $http->request(
                'GET',
                $clients['url'] . Config::get('services.XERO_TAX_RATES_URL'),
                $this->buildRequestOptions($clients)
            );
            return $this->handleResponse($response, $clients);
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info(
                'Exception in getting Tax details',
                ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            );
            return false;
        }
    }

    /**
     * Builds the request options for making API requests.
     *
     * @param $clients The array of clients data.
     *                       - 'access_token' (string): The access token of the client.
     *                       - 'tenant_id' (string): The tenant ID of the client.
     * @return array The built request options.
     *               - 'headers' (array): The headers for the API request.
     *                                   - 'Authorization' (string): The authorization header.
     *                                   - 'Xero-Tenant-Id' (string): The Xero tenant ID header.
     *                                   - 'Accept' (string): The accept header.
     *               - 'form_params' (array): The form parameters for the API request.
     */
    private function buildRequestOptions($clients): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $clients['access_token'],
                'Xero-Tenant-Id' => $clients['tenant_id'],
                'Accept' => 'application/json',
            ],
            'form_params' => [],
        ];
    }

    /**
     * Handle the response from Xero API and update/create tax rates
     *
     * @param ResponseInterface $response The response object from Xero API
     * @param $clients The array of clients' information
     * @return bool Returns true if tax rates are updated or created, otherwise returns false
     */
    private function handleResponse(ResponseInterface $response, $clients): bool
    {
        $result = json_decode((string)$response->getBody(), true);
        if (isset($result['TaxRates'])) {
            foreach ($result['TaxRates'] as $row) {
                $this->xeroTaxRates->updateOrCreate(
                    $this->buildUpdateOrCreateConditions($row, $clients['company_id']),
                    $this->buildUpdateOrCreateAttributes($row)
                );
            }
            return true;
        }
        return false;
    }

    /**
     * Build the conditions for updating or creating a row.
     *
     * @param array $row The row data.
     * @param int $companyId The company ID.
     * @return array The conditions.
     */
    private function buildUpdateOrCreateConditions(array $row, $companyId): array
    {
        return [
            'company_id' => $companyId,
            'name' => $row['Name'] ?? null,
            'tax_type' => $row['TaxType'] ?? null,
            'report_tax_type' => $row['ReportTaxType'] ?? null,
            'display_tax_rate' => $row['DisplayTaxRate'] ?? 0,
            'effective_rate' => $row['EffectiveRate'] ?? 0,
            'status' => $row['Status'] ?? null,
        ];
    }

    /**
     * Builds an array of attributes for update or create operations.
     *
     * @param array $row The row containing the attribute values.
     * @return array The array of attributes for update or create operations.
     */
    private function buildUpdateOrCreateAttributes(array $row): array
    {
        return [
            'can_applyto_assets' => $row['CanApplyToAssets'] ?? null,
            'can_applyto_equity' => $row['CanApplyToEquity'] ?? null,
            'can_applyto_expenses' => $row['CanApplyToExpenses'] ?? null,
            'can_applyto_liabilities' => $row['CanApplyToLiabilities'] ?? null,
            'can_applyto_revenue' => $row['CanApplyToRevenue'] ?? null,
        ];
    }

    /**
     * Save items to the database.
     *
     * @param array $clients The clients data.
     * @return bool Returns true on successful save, false otherwise.
     * @throws GuzzleException
     */
    public function saveItems($clients): bool
    {
        try {
            $response = $this->createHTTPRequest($clients);
            $result = json_decode((string)$response->getBody(), true);

            if (isset($result['Items'])) {

                foreach ($result['Items'] as $itemData) {  // Renamed row to itemData which is more understandable.
                    $conditions = [
                        'item_id' => $itemData['ItemID'] ?? null,
                        'code' => $itemData['Code'] ?? null
                    ];
                    $attributes = $this->buildSaveUpdateOrCreateAttributes($itemData, $clients['company_id']);

                    $this->xeroItems->updateOrCreate($conditions, $attributes);
                }
            }

            return true;

        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->error('Exception in getting Xero item details', ['exception' => $e]);
            return false;
        }
    }

    /**
     * Creates an HTTP request using the provided client information.
     *
     * @param array $clients An array containing the client information, including the URL, access token, and tenant ID.
     * @return ResponseInterface The response of the HTTP request.
     * @throws GuzzleException
     */
    private function createHTTPRequest($clients)
    {
        $http = new Client();
        $url = $clients['url'] . Config::get('services.XERO_ITEMS_URL');

        $headers = [
            'Authorization' => 'Bearer ' . $clients['access_token'],
            'Xero-Tenant-Id' => $clients['tenant_id'],
            'Accept' => 'application/json',
        ];

        return $http->request('GET', $url, ['headers' => $headers]);
    }


    /**
     * Builds an array of attributes for saving, updating or creating an item.
     *
     * @param array $itemData The data of the item.
     * @param int $company_id The ID of the company.
     * @return array The attributes for saving, updating or creating an item.
     */
    private function buildSaveUpdateOrCreateAttributes($itemData, $company_id)
    {
        return [
            'company_id' => $company_id,
            'description' => $itemData['Description'] ?? null,
            'purchase_description' => $itemData['PurchaseDescription'] ?? null,
            'name' => $itemData['Name'] ?? null,
            'is_tracked_as_inventory' => $itemData['IsTrackedAsInventory'] ?? null,
            'is_sold' => $itemData['IsSold'] ?? null,
            'is_purchased' => $itemData['IsPurchased'] ?? null,
        ];
    }

    /**
     * Save the accounts for the given clients.
     *
     * @param object $clients The array of clients.
     * @return bool Returns true if the accounts were saved successfully, false otherwise.
     * @throws GuzzleException
     */
    public function saveAccounts($clients): bool
    {
        try {
            $data = $this->fetchAccountData($clients);
            return $this->saveFetchedData($data, $clients);
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Fetches account data from Xero API based on client information.
     *
     * @param object $clients The object containing the client information.
     *                      The array must have the following keys:
     *                      - url: The base URL of the Xero API.
     *                      - access_token: The access token to authenticate the API request.
     *                      - tenant_id: The tenant ID of the Xero organization.
     * @return array An array containing the account data.
     * @throws GuzzleException
     */
    private function fetchAccountData($clients): array
    {
        $http = new Client();
        $response = $http->request('GET', $clients['url'] . Config::get('services.XERO_ACCOUNTS_URL'), [
            'headers' => [
                'Authorization' => 'Bearer ' . $clients['access_token'],
                'Xero-Tenant-Id' => $clients['tenant_id'],
                'Accept' => 'application/json',
            ],
        ]);

        $result = json_decode((string)$response->getBody(), true);

        return $result['Accounts'] ?? [];
    }

    /**
     * Save fetched data.
     *
     * @param array $data The fetched data.
     * @param object $clients The clients data.
     * @return bool Returns true if the data is successfully saved, false otherwise.
     */
    private function saveFetchedData(array $data, $clients): bool
    {
        foreach ($data as $row) {
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

        return true;
    }

    /**
     * Get the access token from the XERO API.
     *
     * @param $clients - The clients array.
     * @return bool The result of getting the access token.
     * @throws GuzzleException
     */
    public function getAccessToken($clients): bool
    {
        $http = new Client();
        try {
            $response = $http->request('POST', Config::get('services.XERO_TOKEN_URL'), $this->buildRequestsOptions($clients));
            $result = $this->handleResponses($response);
            if ($result !== false) {
                $this->setNewCredentials($result, $clients);
            }
            return true;
        } catch (Exception $e) {
            Log::channel('cron_activity_logs')->info('Exception in getting Tax details' . $e);
            return false;
        }
    }

    /**
     * Build the options for sending requests.
     *
     * @param array $clients The client details.
     * @return array The options for sending requests.
     */
    protected function buildRequestsOptions($clients)
    {
        return [
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
        ];
    }

    /**
     * Handle the responses received from external services.
     *
     * @param mixed $response The response received from the external service.
     * @return mixed The decoded JSON response as an array, or false if the response is empty.
     */
    protected function handleResponses($response)
    {
        if (!empty($response)) {
            return json_decode((string)$response->getBody(), true);
        } else {
            return false;
        }
    }

    /**
     * Update the credentials of a client with new access and refresh tokens.
     *
     * @param array $result The result containing the new access and refresh tokens.
     * @param array $clients The array representing the client's information, including the client's ID and current refresh token.
     *
     * @return void
     */
    protected function setNewCredentials($result, $clients)
    {
        $newConfig = $this->xeroSettings->find($clients['id']);
        $newConfig->refresh_token = $result['refresh_token'] ?? $clients['refresh_token'];
        $newConfig->access_token = $result['access_token'];
        $newConfig->save();
    }

    /**
     * Retrieves invoices from Xero.
     *
     * @param array $request The request data.
     * @param array $xeroConfig The Xero configuration settings.
     *
     * @return JsonResponse Returns the response from Xero or error message on failure.
     * @throws GuzzleException
     */
    public function getInvoices($request, $xeroConfig)
    {
        $http = new Client();
        $rawUrl = $this->prepareUrl($request);

        try {
            $response = $http->request('GET', $xeroConfig['url'] . Config::get('services.XERO_INVOICES_URL') . $rawUrl, $this->buildGetInvoiceRequestOptions($xeroConfig, $rawUrl));
            return $this->handleGetInvoiceResponse($request, $response);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Prepares the URL based on the provided request.
     *
     * @param array $request The request data.
     *
     * @return string The prepared URL.
     */
    private function prepareUrl($request): string
    {
        return (!empty($request['invoice_number'])) ? "/" . $request['invoice_number'] : '';
    }

    /**
     * Build the options for the get invoice request.
     *
     * @param array $xeroConfig The Xero configuration array.
     * @param string $rawUrl The raw URL for the request.
     *
     * @return array The options for the get invoice request.
     */
    private function buildGetInvoiceRequestOptions($xeroConfig, $rawUrl): array
    {
        app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.XERO_INVOICES_URL') . $rawUrl, '');

        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                'Accept' => 'application/json',
            ],
            'form_params' => [],
        ];
    }

    /**
     * Handle the response of the get invoice request.
     *
     * @param $request - The original request object.
     * @param $response - The response object from the Xero API.
     *
     * @return JsonResponse The JSON response containing the result of the get invoice request.
     */
    private function handleGetInvoiceResponse($request, $response)
    {
        $result = json_decode((string)$response->getBody(), true);

        if (isset($result['Invoices'][0]['InvoiceNumber'])) {
            $request->due_amount = $result['Invoices'][0]['AmountDue'];
            $request->due_date = Carbon::parse($result['Invoices'][0]['DueDateString'])->format('Y-m-d');
            $request->invoice_status = $result['Invoices'][0]['Status'];
            $request->save();
        }

        app('thirdPartyLogServices')->endApiLog($result);
        return response()->json($result);
    }

    /**
     * Handle the error and return a JSON response.
     *
     * @param Exception $e The exception that occurred.
     *
     * @return JsonResponse The JSON response containing the error message.
     */
    private function handleError(Exception $e): JsonResponse
    {
        Log::error('Exception in getting Invoice details' . $e);
        return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
    }

    /**
     * Create contacts in Xero.
     *
     * @param array $request The request data for creating contacts.
     * @param array $xeroConfig The Xero configuration array.
     *
     * @return JsonResponse The response from Xero API.
     * @throws GuzzleException
     */
    public function createContacts($request, $xeroConfig)
    {
        $http = new Client();
        $data = $this->prepareDataArray($request);

        try {
            app('thirdPartyLogServices')->startApiLog($xeroConfig['url'] . Config::get('services.XERO_CONTACTS_URL'), $data);

            $response = $http->request('POST', $xeroConfig['url'] . Config::get('services.XERO_CONTACTS_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                    'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                    'Accept' => 'application/json',
                ],
                'json' => $data,
            ]);

            $result = json_decode((string)$response->getBody(), true);

            if (isset($result['Contacts'][0]['ContactID'])) {
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
     * Prepare the data array for the request.
     *
     * @param array $request The request data array.
     *
     * @return array The prepared data array.
     */
    private function prepareDataArray($request): array
    {
        $data = [
            'Name' => $request['company_name'],
            'ContactNumber' => $request['contact_number'],
            'AccountNumber' => $request['bank_account_number'],
            'EmailAddress' => $request['email'],
            'BankAccountDetails' => $request['bank_account_number'],
            'TaxNumber' => $request['tax_id'],
            'AccountsReceivableTaxType' => $request['account_receivable_tax_type'],
            'AccountsPayableTaxType' => $request['account_payable_tax_type']
        ];

        if (!empty($request['ContactID'])) {
            $data['ContactID'] = $request['ContactID'];
        }

        return $data;
    }

    /**
     * Create an invoice.
     *
     * @param array $request The request data.
     * @param array $xeroConfig The Xero configuration array.
     *
     * @return bool Returns true if the invoice is created successfully.
     */
    public function createInvoice($request, $xeroConfig): bool
    {
        $crmProspect = $this->crmProspect->findOrFail($request['crm_prospect_id']);
        $generateInvoice = $this->preparedInvoiceData($request, $crmProspect);
        $generateInvoiceXero = $this->generateInvoices($generateInvoice, $xeroConfig);

        if (isset($generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'])) {
            $this->updateInvoiceData($request, $generateInvoiceXero);
            $mapServiceToAction = [
                1 => [$this->directRecruitmentExpenses, 'invoice_number'],
                2 => [$this->eContractCostManagement, 'invoice_number'],
                3 => [$this->totalManagementCostManagement, 'invoice_number']
            ];
            $this->updateInvoiceNumber($request, $generateInvoiceXero, $mapServiceToAction);
        }
        return true;
    }

    /**
     * Prepare invoice data for generating an invoice.
     *
     * @param array $request The request data.
     * @param object $crmProspect The CRM prospect object.
     *
     * @return array The prepared invoice data.
     */
    private function preparedInvoiceData($request, $crmProspect): array
    {
        $generateInvoice['Type'] = 'ACCREC';
        $generateInvoice['Date'] = $this->timestampToXeroDate($request['due_date']);
        $generateInvoice['DueDate'] = $this->timestampToXeroDate($request['due_date']);
        $generateInvoice['DateString'] = $request['issue_date'] . "T00:00:00";
        $generateInvoice['DueDateString'] = $request['due_date'] . "T00:00:00";
        $generateInvoice['LineAmountTypes'] = 'Exclusive';
        $generateInvoice['Contact']['ContactID'] = $crmProspect->xero_contact_id;
        $this->addLineItems($request, $generateInvoice);

        return $generateInvoice;
    }

    /**
     * Add line items to the invoice.
     *
     * @param array $request The request array containing invoice information.
     * @param array $generateInvoice The array for generating the invoice.
     *
     * @return void
     */
    private function addLineItems($request, &$generateInvoice): void
    {
        $lineItems = json_decode($request['invoice_items']);
        if ($request['invoice_items']) {
            foreach ($lineItems as $index => $item) {
                $this->createInvoiceItem($request, $item);
                $this->addLineItemToInvoice($item, $index, $generateInvoice);
            }
        }
    }

    /**
     * Create an invoice item.
     *
     * @param array $request The request data containing the invoice ID.
     * @param object $item The item to be added to the invoice.
     *
     * @return void
     */
    private function createInvoiceItem($request, $item): void
    {
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
    }

    /**
     * Add a line item to the invoice.
     *
     * @param object $item The item object.
     * @param int $index The index for the line item.
     * @param array $generateInvoice The invoice data to generate.
     *
     * @return void
     */
    private function addLineItemToInvoice($item, $index, &$generateInvoice): void
    {
        $taxData = $this->xeroTaxRates::find($item->tax_id);
        $itemData = $this->xeroItems::find($item->item_id);
        $accountData = $this->xeroAccounts::find($item->account_id);

        $generateInvoice['LineItems'][$index] = new stdClass();
        $generateInvoice['LineItems'][$index]->ItemCode = $itemData['code'] ?? '';
        $generateInvoice['LineItems'][$index]->Description = $item->description;
        $generateInvoice['LineItems'][$index]->Quantity = $item->quantity;
        $generateInvoice['LineItems'][$index]->UnitAmount = $item->price;
        $generateInvoice['LineItems'][$index]->AccountCode = $accountData['code'] ?? '';
        $generateInvoice['LineItems'][$index]->TaxType = $taxData['tax_type'] ?? 0;
    }

    /**
     * Update the invoice data.
     *
     * @param array $request The request data.
     * @param mixed $generateInvoiceXero The generated invoice data from Xero.
     *
     * @return void
     */
    private function updateInvoiceData($request, $generateInvoiceXero): void
    {
        $invoiceData = $this->invoice->findOrFail($request['invoice_id']);
        $invoiceData->invoice_number = $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber'];
        $invoiceData->due_amount = $generateInvoiceXero->original['Invoices'][0]['AmountDue'];
        $invoiceData->invoice_status = $generateInvoiceXero->original['Invoices'][0]['Status'];
        $invoiceData->save();
        $this->invoiceItemsTemp->where('created_by', $request['created_by'])->delete();
    }

    /**
     * Update the invoice number for the given request.
     *
     * @param array $request The request containing the invoice items.
     * @param mixed $generateInvoiceXero The generated invoice from Xero.
     * @param array $mapServiceToAction The mapping of service IDs to actions.
     *
     * @return void
     */
    private function updateInvoiceNumber($request, $generateInvoiceXero, $mapServiceToAction): void
    {
        $lineItems = json_decode($request['invoice_items']);
        foreach ($lineItems as $item) {
            if (array_key_exists($item->service_id, $mapServiceToAction)) {
                $mapServiceToAction[$item->service_id][0]->where('id', $item->expense_id)->update([
                    $mapServiceToAction[$item->service_id][1] => $generateInvoiceXero->original['Invoices'][0]['InvoiceNumber']
                ]);
            }
        }
    }

    /**
     * Convert a timestamp to a Xero-formatted date.
     *
     * @param int $timestamp The timestamp to convert.
     *
     * @return string The Xero-formatted date string.
     */
    private function timestampToXeroDate($timestamp): string
    {
        $converted = (Carbon::parse($timestamp)->timestamp * 1000) . "+0000";
        return '/Date(' . $converted . ')/';
    }

    /**
     * Generate invoices and return the response.
     *
     * @param $request - The request object containing invoice data.
     * @param array $xeroConfig The Xero configuration array.
     *
     * @return JsonResponse The response generated from the invoice generation.
     */
    public function generateInvoices($request, $xeroConfig)
    {
        $http = new Client();
        try {
            $requestData = $this->prepareInvoiceData($request);
            $this->logApiCall($xeroConfig['url'], $requestData);

            $response = $this->createGenerateInvoicesHTTPRequest($http, $xeroConfig, $requestData);

            $result = json_decode((string)$response->getBody(), true);
            $this->logApiCallEnd($result);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Exception in submitting invoice details' . $e);
            return response()->json(['msg' => 'Error', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Prepare the invoice data for submission.
     *
     * @param array $request The request data for the invoice.
     *
     * @return array The prepared invoice data.
     */
    private function prepareInvoiceData($request): array
    {
        return [
            'Type' => 'ACCREC',
            'Contact' => $request['Contact'],
            'Date' => $request['Date'],
            'DueDate' => $request['DueDate'],
            'DateString' => $request['DateString'],
            'DueDateString' => $request['DueDateString'],
            'LineAmountTypes' => $request['LineAmountTypes'],
            'LineItems' => $request['LineItems']
        ];
    }

    /**
     * Create the generate invoices HTTP request.
     *
     * @param object $http The HTTP client.
     * @param array $xeroConfig The Xero configuration array.
     * @param array $data The data for the request.
     *
     * @return mixed The result of the generate invoices HTTP request.
     */
    private function createGenerateInvoicesHTTPRequest($http, $xeroConfig, $data): mixed
    {
        return $http->request('POST', $xeroConfig['url'] . Config::get('services.XERO_INVOICES_URL'), [
            'headers' => [
                'Authorization' => 'Bearer ' . $xeroConfig['access_token'],
                'Xero-Tenant-Id' => $xeroConfig['tenant_id'],
                'Accept' => 'application/json',
            ],
            'json' => $data
        ]);
    }

    /**
     * Log an API call.
     *
     * @param string $url The URL for the API call.
     * @param mixed $data The data for the API call.
     *
     * @return void
     */
    private function logApiCall($url, $data): void
    {
        app('thirdPartyLogServices')->startApiLog($url . Config::get('services.XERO_INVOICES_URL'), $data);
    }

    /**
     * Log the end of an API call.
     *
     * @param mixed $result The result of the API call.
     *
     * @return void
     */
    private function logApiCallEnd($result): void
    {
        app('thirdPartyLogServices')->endApiLog($result);
    }

    /**
     * Resubmit an invoice.
     *
     * @param array $invoice The invoice data.
     * @param array $xeroConfig The Xero configuration array.
     *
     * @return bool Returns true on success.
     * @throws Exception
     */
    public function invoiceReSubmit($invoice, $xeroConfig): bool
    {
        $generatedInvoiceNumber = '';
        $invoiceData = $this->invoice->findOrFail($invoice['id']);
        $invoiceData->resubmit_count = $invoiceData->resubmit_count + 1;
        $invoiceData->save();
        $crmProspect = $this->crmProspect->findOrFail($invoice['crm_prospect_id']);


        $invoiceToGenerate = $this->generateInvoiceBase($invoice, $crmProspect);

        if ($invoice['invoiceItems']) {
            $invoiceToGenerate['LineItems'] = $this->generateInvoiceItems($invoice['invoiceItems']);
        }

        $invoiceFromXero = $this->generateInvoices($invoiceToGenerate, $xeroConfig);

        if (isset($invoiceFromXero->original['Invoices'][0]['InvoiceNumber'])) {
            $generatedInvoiceNumber = $this->updateInvoiceDataAndInvoiceItems($invoiceFromXero, $invoiceData, $invoice['invoiceItems']);
        }

        $mailParams = $this->prepareMailParameters($crmProspect, $invoice);

        Log::channel('cron_activity_logs')->info('Checking mail ' . __LINE__);
        Log::channel('cron_activity_logs')->info('Generated Invoice Number ' . $generatedInvoiceNumber);

        if ($invoiceData->resubmit_count >= 3 && !empty($generatedInvoiceNumber)) {
            Log::channel('cron_activity_logs')->info('Sending mail ' . print_r($mailParams, true));
            $this->emailServices->sendInvoiceResubmissionFailedMail($mailParams);
        }

        return true;
    }

    /**
     * Generate the base structure for an invoice.
     *
     * @param array $invoice The invoice details array.
     * @param object $crmProspect The CRM prospect object.
     *
     * @return array The base structure for the invoice.
     */
    private function generateInvoiceBase($invoice, $crmProspect)
    {
        $invoiceToGenerate['Type'] = 'ACCREC';
        $invoiceToGenerate['Date'] = $this->timestampToXeroDate($invoice['issue_date']);
        $invoiceToGenerate['DueDate'] = $this->timestampToXeroDate($invoice['due_date']);
        $invoiceToGenerate['DateString'] = $invoice['issue_date'] . "T00:00:00";
        $invoiceToGenerate['DueDateString'] = $invoice['due_date'] . "T00:00:00";
        $invoiceToGenerate['LineAmountTypes'] = 'Exclusive';
        $invoiceToGenerate['Contact']['ContactID'] = $crmProspect->xero_contact_id;

        return $invoiceToGenerate;
    }

    /**
     * Generate invoice line items based on the given array of invoice items.
     *
     * @param array $invoiceItems The array of invoice items.
     *
     * @return array The generated invoice line items.
     */
    private function generateInvoiceItems($invoiceItems)
    {
        $invoiceLineItems = [];
        $increment = 0;
        foreach ($invoiceItems as $item) {
            $invoiceLineItems[$increment] = $this->populateInvoiceItem($item);
            $increment++;
        }
        return $invoiceLineItems;
    }

    /**
     * Populate an invoice item object with data.
     *
     * @param array $item The item data array.
     *     - 'item' (string, optional): The item code.
     *     - 'description' (string): The item description.
     *     - 'quantity' (int): The quantity of the item.
     *     - 'price' (float): The unit amount of the item.
     *     - 'account' (string, optional): The account code for the item.
     *     - 'tax_id' (string, optional): The tax type for the item.
     *
     * @return stdClass The populated invoice item object.
     *     - 'ItemCode' (string): The item code.
     *     - 'Description' (string): The item description.
     *     - 'Quantity' (int): The quantity of the item.
     *     - 'UnitAmount' (float): The unit amount of the item.
     *     - 'AccountCode' (string): The account code for the item.
     *     - 'TaxType' (string): The tax type for the item.
     */
    private function populateInvoiceItem($item)
    {
        $invoiceItem = new stdClass();
        $invoiceItem->ItemCode = $item['item'] ?? '';
        $invoiceItem->Description = $item['description'];
        $invoiceItem->Quantity = $item['quantity'];
        $invoiceItem->UnitAmount = $item['price'];
        $invoiceItem->AccountCode = $item['account'] ?? '';
        $invoiceItem->TaxType = $item['tax_id'] ?? '';

        return $invoiceItem;
    }

    /**
     * Update the invoice data and invoice items.
     *
     * @param $invoiceFromXero - The invoice retrieved from Xero.
     * @param $invoiceData - The invoice data to be updated.
     * @param $invoiceItems - The invoice items to be updated.
     *
     * @return string The generated invoice number.
     * @throws Exception
     */
    private function updateInvoiceDataAndInvoiceItems($invoiceFromXero, $invoiceData, $invoiceItems)
    {
        $invoiceData->invoice_number = $generatedInvoiceNumber = $invoiceFromXero->original['Invoices'][0]['InvoiceNumber'];
        $invoiceData->due_amount = $invoiceFromXero->original['Invoices'][0]['AmountDue'];
        $invoiceData->invoice_status = $invoiceFromXero->original['Invoices'][0]['Status'];
        $invoiceData->save();

        foreach ($invoiceItems as $item) {
            $this->invoiceItemsTemp->where('service_id', $item['service_id'])->where('expense_id', $item['expense_id'])->delete();
            $this->updateExpenseItem($item, $invoiceFromXero->original['Invoices'][0]['InvoiceNumber']);
        }
        return $generatedInvoiceNumber;
    }

    /**
     * Update the invoice number of an expense item.
     *
     * @param array $item The expense item.
     * @param string $invoiceNumber The invoice number to update.
     *
     * @return void
     * @throws Exception
     */
    private function updateExpenseItem($item, $invoiceNumber)
    {
        $expenseRepository = $this->getExpenseRepository($item['service_id']);
        $expenseRepository->where('id', $item['expense_id'])->update([
            'invoice_number' => $invoiceNumber
        ]);
    }

    /**
     * Get the expense repository based on the service id.
     *
     * @param int $serviceId The service id.
     *
     * @return object The expense repository.
     * @throws Exception If the service id is invalid.
     */
    private function getExpenseRepository($serviceId)
    {
        if ($serviceId == 1) {
            return $this->directRecruitmentExpenses;
        } else if ($serviceId == 2) {
            return $this->eContractCostManagement;
        } else if ($serviceId == 3) {
            return $this->totalManagementCostManagement;
        }
        throw new Exception("Invalid service id");
    }

    /**
     * Prepare the mail parameters for invoice resubmission.
     *
     * @param object $crmProspect The CRM prospect object.
     * @param array $invoice The invoice array.
     *
     * @return array The mail parameters for invoice resubmission.
     */
    private function prepareMailParameters($crmProspect, $invoice)
    {
        $mailParams['company_name'] = $crmProspect->company_name;
        $mailParams['company_email'] = $crmProspect->email;
        $mailParams['reference_number'] = $invoice['reference_number'];
        $mailParams['email'] = Config::get('services.INVOICE_RESUBMISSION_FAILED_MAIL');

        return $mailParams;
    }
}

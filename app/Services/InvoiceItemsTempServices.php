<?php

namespace App\Services;

use App\Models\InvoiceItemsTemp;
use App\Models\XeroItems;
use App\Models\XeroAccounts;
use App\Models\XeroTaxRates;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceItemsTempServices
{
    /**
     * @var InvoiceItemsTemp
     */
    private InvoiceItemsTemp $invoiceItemsTemp;
    /**
     * @var XeroItems
     */
    private XeroItems $xeroItems;
    /**
     * @var XeroAccounts
     */
    private XeroAccounts $xeroAccounts;
    /**
     * @var XeroTaxRates
     */
    private XeroTaxRates $xeroTaxRates;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * InvoiceServices constructor method.
     * @param InvoiceItemsTemp $invoiceItemsTemp Instance of the InvoiceItemsTemp class.
     * @param XeroItems $xeroItems Instance of the XeroItems class.
     * @param XeroAccounts $xeroAccounts Instance of the XeroAccounts class.
     * @param XeroTaxRates $xeroTaxRates Instance of the XeroTaxRates class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     */
    public function __construct(
        InvoiceItemsTemp   $invoiceItemsTemp,
        XeroItems $xeroItems,
        XeroAccounts $xeroAccounts,
        XeroTaxRates $xeroTaxRates,
        ValidationServices $validationServices
    )
    {
        $this->invoiceItemsTemp = $invoiceItemsTemp;
        $this->xeroItems = $xeroItems;
        $this->xeroAccounts = $xeroAccounts;
        $this->xeroTaxRates = $xeroTaxRates;
        $this->validationServices = $validationServices;
    }

    /**
     * Create a new invoice.
     *
     * @param array $request The request data containing information about the invoice.
     *                      The $request array should have the following keys:
     *                      - crm_prospect_id: The ID of the prospect associated with the invoice.
     *                      - service_id: The ID of the service provided in the invoice.
     *                      - invoice_number: The number of the invoice.
     *                      - invoice_items: A JSON-encoded string containing the invoice items.
     * @return array The result of the create operation.
     *               If the request is not valid, it returns an array with the following key-value pair:
     *               - 'validate': An array of validation errors.
     *               If an invoice already exists for the provided user, prospect, and service, it returns an array with the following key-value pairs:
     *               - 'isExists': false,
     *               - 'message': "Please complete the Pending Invoice before raising a new one. Proceed to Pending Invoice?"
     *               Otherwise, it creates the invoice items and returns the result of the createInvoiceItems method.
     */
    public function create($request)
    {
        $userId = JWTAuth::parseToken()->authenticate()['id'];
        $invoiceItems = json_decode($request['invoice_items']);

        if (!$this->isValidRequest($request->toArray())) {
            return ['validate' => $this->validationServices->errors()];
        }

        if ($this->isInvoiceExist($userId, $request['crm_prospect_id'], $request['service_id'])) {
            return [
                "isExists" => false,
                "message" => "Please complete the Pending Invoice before raising a new one. Proceed to Pending Invoice?"
            ];
        }

        return $this->createInvoiceItems($invoiceItems, $request['crm_prospect_id'], $request['service_id'], $request['invoice_number'], $userId);
    }

    /**
     * Validate the given request data.
     *
     * @param mixed $requestData The request data to be validated.
     *
     * @return bool True if the request data is valid, false otherwise.
     */
    private function isValidRequest($requestData)
    {
        return $this->validationServices->validate($requestData, $this->invoiceItemsTemp->rules);
    }

    /**
     * Check if an invoice exists for a given user, prospect, and service.
     *
     * @param int $userId The ID of the user.
     * @param int $prospectId The ID of the prospect.
     * @param int $serviceId The ID of the service.
     *
     * @return bool True if the invoice exists, false otherwise.
     */
    private function isInvoiceExist($userId, $prospectId, $serviceId)
    {
        $invoiceItemsCount = $this->invoiceItemsTemp->where('created_by', $userId)->count();

        if (isset($invoiceItemsCount) && ($invoiceItemsCount != 0)) {
            $invoiceItemsCheck = $this->invoiceItemsTemp
                ->where('crm_prospect_id', $prospectId)
                ->where('service_id', $serviceId)
                ->where('created_by', $userId)
                ->count();
            return (isset($invoiceItemsCheck) && ($invoiceItemsCheck != $invoiceItemsCount));
        }
        return false;
    }

    /**
     * Create invoice items from the given line items.
     *
     * @param mixed $lineItems The line items to create invoice items from.
     * @param int $prospectId The CRM prospect ID for the invoice items.
     * @param int $serviceId The service ID for the invoice items.
     * @param string $invoiceNumber The invoice number for the invoice items.
     * @param int $userId The user ID for the invoice items.
     *
     * @return mixed The created invoice items.
     */
    private function createInvoiceItems($lineItems, $prospectId, $serviceId, $invoiceNumber, $userId)
    {
        $invoiceItemsTemp = '';
        foreach ($lineItems as $item) {
            $invoiceItemsTemp = $this->invoiceItemsTemp::create([
                'crm_prospect_id' => $prospectId,
                'service_id' => $serviceId,
                'expense_id' => $item->expense_id,
                'invoice_number' => $invoiceNumber,
                'item' => $item->item ?? '',
                'description' => $item->description ?? '',
                'quantity' => $item->quantity ?? '',
                'price' => $item->price ?? 0,
                'account' => $item->account ?? '',
                'tax_rate' => $item->tax_rate ?? 0,
                'total_price' => $item->total_price ?? 0,
                'created_by' => $userId,
                'modified_by' => $userId
            ]);
        }
        return $invoiceItemsTemp;
    }

    /**
     * Update the invoice items with the given request data.
     *
     * @param mixed $request The request data containing the updated invoice items.
     *
     * @return bool|array True if the invoice items were successfully updated,
     *                   an array with error messages otherwise.
     */
    public function update($request)
    {
        if (!($this->validationServices->validate($request->toArray(), $this->invoiceItemsTemp->rulesForUpdation($request['id'])))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $invoiceItemsTemp = $this->getInvoiceItemByUser($request, $user);

        if (is_null($invoiceItemsTemp)) {
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        if (!$this->canRaiseNewInvoice($request, $invoiceItemsTemp)) {
            return [
                "isExists" => false,
                "message" => "Please complete the Pending Invoice before raising a new one. Proceed to Pending Invoice?"
            ];
        }

        $this->updateInvoiceItems($request, $invoiceItemsTemp, $user);

        $invoiceItemsTemp->save();

        return true;
    }

    /**
     * Retrieve the invoice item for a given user and request.
     *
     * @param mixed $request The request data containing the id of the invoice item.
     * @param mixed $user The user data object containing the user's id.
     *
     * @return mixed|null The invoice item matching the user's id and request id, or null if no match was found.
     */
    private function getInvoiceItemByUser($request, $user)
    {
        return $this->invoiceItemsTemp
            ->where('created_by', $user['id'])
            ->find($request['id']);
    }

    /**
     * Check if a new invoice can be raised.
     *
     * @param object $request The request object containing crm_prospect_id and service_id properties.
     * @param object $invoiceItem The invoice item object containing crm_prospect_id and service_id properties.
     *
     * @return bool Returns true if a new invoice can be raised, false otherwise.
     */
    private function canRaiseNewInvoice($request, $invoiceItem)
    {
        return ($invoiceItem->crm_prospect_id == $request['crm_prospect_id'])
            && ($invoiceItem->service_id == $request['service_id']);
    }

    /**
     * Update the invoice item fields with the values from the request, if available.
     *
     * @param array $request The request data containing updated values for the invoice item fields.
     * @param object $invoiceItemsTemp The temporary invoice items object to update the fields on.
     * @param array $user The user data containing the ID of the user performing the update.
     *
     * @return void
     */
    private function updateInvoiceItems($request, $invoiceItemsTemp, $user)
    {
        $fields = ['crm_prospect_id', 'service_id', 'tax_id', 'item_id',
            'account_id', 'expense_id', 'invoice_number',
            'description', 'quantity', 'price', 
            'total_price', 'created_by'];

        foreach ($fields as $field) {
            $invoiceItemsTemp->$field = $request[$field] ?? $invoiceItemsTemp->$field;
        }

        If (!empty($invoiceItemsTemp->item_id)){
            $itemRes = $this->xeroItems->select('name')->find($invoiceItemsTemp->item_id);            
        }
        $invoiceItemsTemp->item = $itemRes['name'] ?? '';

        If (!empty($invoiceItemsTemp->account_id)){
            $accountRes = $this->xeroAccounts->select('name')->find($invoiceItemsTemp->account_id);
        }
        $invoiceItemsTemp->account = $accountRes['name'] ?? '';

        If (!empty($invoiceItemsTemp->tax_id)){
            $taxrateRes = $this->xeroTaxRates->select('effective_rate')->find($invoiceItemsTemp->tax_id);
        }
        $invoiceItemsTemp->tax_rate = $taxrateRes['effective_rate'] ?? '';
        
        $invoiceItemsTemp->modified_by = $user['id'];
    }


    /**
     * Show a specific invoice item.
     *
     * @param mixed $request The request data containing the ID of the invoice item to be shown.
     *
     * @return mixed The invoice item data if found, otherwise an array with a "message" key indicating the error.
     */
    public function show($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!($this->validationServices->validate($request, ['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $data = $this->invoiceItemsTemp->with(['crm_prospect' => function ($query) {
            $query->select(['id', 'company_name']);
        }])->where('created_by', $user['id'])->find($request['id']);

        if (is_null($data)) {
            return [
                "message" => "Data not found"
            ];
        }

        return $data;

    }

    /**
     * List invoice items.
     *
     * @param mixed $request The request data.
     *
     * @return array|LengthAwarePaginator The paginated list of invoice items or an error message if validation fails.
     */
    public function list($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->invoiceItemsTemp
            ->with(['crm_prospect' => function ($query) {
                $query->select(['id', 'company_name']);
            }])
            ->where(function ($query) use ($request) {
                if (!empty($request['search_param'])) {
                    $query->where('item', 'like', "%{$request['search_param']}%")
                        ->orWhere('description', 'like', '%' . $request['search_param'] . '%');
                }
            })
            ->where('created_by', $user['id'])->select('id', 'crm_prospect_id', 'service_id', 'tax_id', 'item_id', 'account_id', 'expense_id', 'invoice_number', 'item', 'description', 'quantity', 'price', 'account', 'tax_rate', 'total_price', 'created_by', 'modified_by', 'created_at')
            ->distinct()
            ->orderBy('created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Delete the specified invoice item from the temporary storage.
     *
     * @param array $request The request data containing the ID of the invoice item to be deleted.
     *
     * @return array An array containing the success status of the deletion and a corresponding message.
     *     The 'isDeleted' key is set to boolean true if the deletion is successful, and false otherwise.
     *     The 'message' key provides a descriptive message about the deletion process.
     */
    public function delete($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $invoiceItemsTemp = $this->invoiceItemsTemp::where('created_by', $user['id'])->find($request['id']);

        if (is_null($invoiceItemsTemp)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $invoiceItemsTemp->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * Delete all invoice items created by the authenticated user.
     *
     * @return array Returns an array with the following keys:
     *     - isDeleted: A boolean indicating if the data was deleted successfully.
     *     - message: A string describing the result of the deletion operation.
     */
    public function deleteAll()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $invoiceItemsTemp = $this->invoiceItemsTemp->where('created_by', $user['id'])->count();

        if (isset($invoiceItemsTemp) && ($invoiceItemsTemp == 0)) {
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $this->invoiceItemsTemp->where('created_by', $user['id'])->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

}

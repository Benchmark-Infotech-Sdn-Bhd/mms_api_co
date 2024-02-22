<?php

namespace App\Services;

use App\Models\TotalManagementExpenses;
use App\Models\TotalManagementExpensesAttachments;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class TotalManagementExpensesServices
{
    public const ATTACHMENT_FILE_TYPE = 'Total Management Expenses';
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_PAYBACK = ['payBackError' => true];
    public const DEFAULT_VALUE = 0;

    /**
     * @var totalManagementExpenses
     */
    private TotalManagementExpenses $totalManagementExpenses;
    /**
     * @var totalManagementExpensesAttachments
     */
    private TotalManagementExpensesAttachments $totalManagementExpensesAttachments;
    /**
     * @var authServices
     */
    private AuthServices $authServices;
    /**
     * @var storage
     */
    private Storage $storage;
    /**
     * TotalManagementExpensesServices constructor.
     * 
     * @param TotalManagementExpenses $totalManagementExpenses The totalManagementExpenses object.
     * @param TotalManagementExpensesAttachments $totalManagementExpensesAttachments The totalManagementExpensesAttachments object.
     * @param AuthServices $authServices The authServices object.
     * @param Storage $storage The storage object.
     */
    public function __construct(
            TotalManagementExpenses                 $totalManagementExpenses,
            TotalManagementExpensesAttachments      $totalManagementExpensesAttachments,
            AuthServices                            $authServices,
            Storage                                 $storage
    )
    {
        $this->totalManagementExpenses = $totalManagementExpenses;
        $this->totalManagementExpensesAttachments = $totalManagementExpensesAttachments;
        $this->authServices = $authServices;
        $this->storage = $storage;
    }

    /**
     * validate the search request data
     * 
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * validate the create request data
     * 
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'project_id' => 'required',
            'worker_id' => 'required',
            'title' => 'required|regex:/^[a-zA-Z0-9 ]*$/',
            'type' => 'required',
            'payment_reference_number' => 'regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * validate the update request data
     * 
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'application_id' => 'required',
            'project_id' => 'required',
            'worker_id' => 'required',
            'title' => 'required|regex:/^[a-zA-Z0-9 ]*$/',
            'type' => 'required',
            'payment_reference_number' => 'regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * validate the payback request data
     * 
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function payBackValidation(): array
    {
        return [
            'id' => 'required',
            'amount_paid' => 'required|regex:/^\-?[0-9]+(?:\.[0-9]{1,2})?$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow'
        ];
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return mixed Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListRequest($request): array|bool
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validatePayBackRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->payBackValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Get a paginated list of total management expenses.
     * 
     * @param $request
     *        worker_id (int) ID of the worker
     *        search (text) search parameter
     * 
     * @return mixed Returns The paginated list of expense
     * 
     * @see validateListRequest()
     * @see validateListRequest()
     * @see applySearchFilter()
     * @see ListSelectColumns()
     * 
     */
    public function list($request) : mixed
    {
        $validationResult = $this->validateListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $data = $this->totalManagementExpenses
            ->leftJoin('total_management_expenses_attachments', function($join) use ($request){
                $join->on('total_management_expenses.id', '=', 'total_management_expenses_attachments.file_id')
                ->whereNull('total_management_expenses_attachments.deleted_at');
            });
        $data = $this->applyCondition($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->ListSelectColumns($data)
                    ->orderBy('total_management_expenses.created_at','DESC')
                    ->paginate(Config::get('services.paginate_worker_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        worker_id (int) ID of the worker
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->where('total_management_expenses.worker_id', $request['worker_id']);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search_param (string) search parameter
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if (!empty($search)) {
                $query->where('total_management_expenses.title', 'like', '%'. $search. '%');
            }
        });
    }

    /**
     * Select data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('total_management_expenses.id', 'total_management_expenses.worker_id', 'total_management_expenses.title','total_management_expenses.type', 'total_management_expenses.amount', 'total_management_expenses.deduction','total_management_expenses.payment_reference_number', 'total_management_expenses.payment_date', 'total_management_expenses.amount_paid', 'total_management_expenses.remaining_amount', 'total_management_expenses.remarks', 'total_management_expenses_attachments.file_name','total_management_expenses_attachments.file_url', 'total_management_expenses.invoice_number')
                    ->distinct();
    }

    /**
     * Show details of a total management expense
     * 
     * @param $request
     *        id (int) ID of the expense
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the expense detail with related attachments
     */
    public function show($request) : mixed
    {
        return $this->totalManagementExpenses::with(['totalManagementExpensesAttachments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->join('workers', function ($join) use ($request) {
                $join->on('workers.id', '=', 'total_management_expenses.worker_id')
                    ->whereIn('workers.company_id', $request['company_id']);
            })
            ->select('total_management_expenses.id', 'total_management_expenses.worker_id', 'total_management_expenses.application_id', 'total_management_expenses.project_id', 'total_management_expenses.title', 'total_management_expenses.type', 'total_management_expenses.payment_reference_number', 'total_management_expenses.payment_date', 'total_management_expenses.amount', 'total_management_expenses.amount_paid', 'total_management_expenses.deduction', 'total_management_expenses.remaining_amount', 'total_management_expenses.remarks', 'total_management_expenses.created_by', 'total_management_expenses.modified_by', 'total_management_expenses.is_payroll', 'total_management_expenses.payroll_id', 'total_management_expenses.month', 'total_management_expenses.year', 'total_management_expenses.invoice_number', 'total_management_expenses.created_at', 'total_management_expenses.updated_at', 'total_management_expenses.deleted_at')
            ->find($request['id']);
    }
    /**
     * Create a new total management expense.
     * 
     * @param $request
     * 
     * @return bool|array Returns true if the create is successful. Returns an error array if validation fails or any error occurs during the create process.
     * 
     * @see validateCreateRequest()
     * @see enrichRequestWithUserDetails()
     * @see createTotalManagementExpense()
     * @see uploadExpenseFiles()
     * 
     */
    public function create($request): bool|array
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $expense = $this->createTotalManagementExpense($request);

        $this->uploadExpenseFiles($request, $expense->id);

        return true;
    }
    /**
     * create total management expense.
     *
     * @param array $params
     *              worker_id (int) ID of the worker
     *              application_id (int) ID of the application
     *              project_id (int) ID of the project
     *              title (string) title of the expense
     *              type (string) type of the expense
     *              payment_reference_number (string) payment reference number
     *              payment_date (date) payment date
     *              amount (decimal) amount of the expense
     *              remarks (string) remarks of expense
     *              created_by The ID of the user who created the expense.
     * 
     * @return mixed Returns the created expense record.
     */
    private function createTotalManagementExpense($params): mixed
    {
        return $this->totalManagementExpenses->create([
            'worker_id' => $params['worker_id'] ?? self::DEFAULT_VALUE,
            'application_id' => $params['application_id'] ?? self::DEFAULT_VALUE,
            'project_id' => $params['project_id'] ?? self::DEFAULT_VALUE,
            'title' => $params['title'] ?? '',
            'type' => $params['type'] ?? '',
            'payment_reference_number' => $params['payment_reference_number'] ?? '',
            'payment_date' => $params['payment_date'] ?? null,
            'amount' => $params['amount'] ?? self::DEFAULT_VALUE,
            'remarks' => $params['remarks'] ?? '',
            'created_by'    => $params['created_by'] ?? self::DEFAULT_VALUE,
            'modified_by'   => $params['created_by'] ?? self::DEFAULT_VALUE,
        ]);
    }
    /**
     * Upload attachment of expense.
     *
     * @param array $request
     *              attachment (file) uploaded file
     *              created_by The ID of the user who upload attachment.
     * @param int $expenseId
     * 
     * @return void
     */
    private function uploadExpenseFiles($request, $expenseId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/TotalManagementExpenses/'.$expenseId. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->totalManagementExpensesAttachments::create([
                        'file_id' => $expenseId,
                        'file_name' => $fileName,
                        'file_type' => self::ATTACHMENT_FILE_TYPE,
                        'file_url' =>  $fileUrl,
                        'created_by' => $request['created_by'] ?? self::DEFAULT_VALUE,
                        'modified_by' => $request['modified_by'] ?? self::DEFAULT_VALUE,
                    ]);  
            }
        }
    }
    /**
     * Update a total management expense.
     * 
     * @param $request
     * 
     * @return bool|array Returns true if the update is successful. Returns an error array if validation fails or any error occurs during the update process.
     *                    Returns self::ERROR_UNAUTHORIZED if the user access invalid expense
     * 
     * @see validateUpdateRequest()
     * @see enrichRequestWithUserDetails()
     * @see getExpense()
     * @see updateExpense()
     * @see uploadExpenseFiles()
     * 
     */
    public function update($request) : bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $request = $this->enrichRequestWithUserDetails($request);

        $expense = $this->getExpense($request['id'], $request['company_id']);
        
        if (is_null($expense)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $this->updateExpense($expense, $request);

        $this->uploadExpenseFiles($request, $expense->id);

        return true;
    }
    /**
     * Retrieve expense record by ID and company ID.
     *
     * @param int $expenseId ID of the expense
     * @param array $companyIds ID of the user company
     * 
     * @return mixed Returns the expense record
     */
    private function getExpense(int $expenseId, array $companyIds)
    {
        return $this->totalManagementExpenses
            ->join('workers', function ($join) use ($companyIds) {
                $join->on('workers.id', '=', 'total_management_expenses.worker_id')
                    ->whereIn('workers.company_id', $companyIds);
            })
            ->select('total_management_expenses.id', 'total_management_expenses.worker_id', 'total_management_expenses.application_id', 'total_management_expenses.project_id', 'total_management_expenses.title', 'total_management_expenses.type', 'total_management_expenses.payment_reference_number', 'total_management_expenses.payment_date', 'total_management_expenses.amount', 'total_management_expenses.amount_paid', 'total_management_expenses.deduction', 'total_management_expenses.remaining_amount', 'total_management_expenses.remarks', 'total_management_expenses.created_by', 'total_management_expenses.modified_by', 'total_management_expenses.is_payroll', 'total_management_expenses.payroll_id', 'total_management_expenses.month', 'total_management_expenses.year', 'total_management_expenses.invoice_number', 'total_management_expenses.created_at', 'total_management_expenses.updated_at', 'total_management_expenses.deleted_at')
            ->find($expenseId);
    }
    /**
     * Update expense based on the provided request.
     *
     * @param mixed $expense
     * @param $params
     *        worker_id (int) ID of the worker
     *        application_id (int) ID of the application
     *        project_id (int) ID of the project
     *        title (string) title of the expense
     *        type (string) type of the expense
     *        payment_reference_number (string) payment reference number
     *        payment_date (date) payment date
     *        amount (decimal) amount of the expense
     *        remarks (string) remarks of expense
     *        modified_by The ID of the user who modified the expense.
     * 
     * @return void
     */
    private function updateExpense($expense, $params)
    {
        $expense->worker_id = $params['worker_id'] ?? $expense->worker_id;
        $expense->application_id = $params['application_id'] ?? $expense->application_id;
        $expense->project_id = $params['project_id'] ?? $expense->project_id;
        $expense->title = $params['title'] ?? $expense->title;
        $expense->type = $params['type'] ?? $expense->type;
        $expense->payment_reference_number = $params['payment_reference_number'] ?? $expense->payment_reference_number;
        $expense->payment_date = $params['payment_date'] ?? $expense->payment_date;
        $expense->amount = $params['amount'] ?? $expense->amount;
        $expense->remarks = $params['remarks'] ?? $expense->remarks;
        $expense->modified_by = $params['modified_by'] ?? $expense->modified_by;
        $expense->save();
    }
    /**
     * Delete a total management expense.
     * 
     * @param $request
     * 
     * 
     * @return bool Returns true if the deletion is successful  otherwise false
     * 
     * @see getExpenseToDelete()
     * 
     */
    public function delete($request) : bool
    {
        $expense = $this->getExpenseToDelete($request);

        if(is_null($expense)){
            return false;
        }

        $expense->delete();
        return true;
    }
    /**
     * Get the expense to delete.
     *
     * @param array $request
     *              id (int) ID of the expense
     *              company_id (array) ID of the user company
     * 
     * @return mixed Returns the expense record
     */
    private function getExpenseToDelete(array $request)
    {
        return $this->totalManagementExpenses
            ->join('workers', function ($join) use ($request) {
                $join->on('workers.id', '=', 'total_management_expenses.worker_id')
                    ->whereIn('workers.company_id', $request['company_id']);
            })
            ->select('total_management_expenses.id')
            ->find($request['id']);
    }
    /**
     * Delete an attachment associated with a total management expense.
     *
     * @param $request
     * 
     * @return bool Returns true if the deletion is successful  otherwise false
     * 
     * @see getAttachmentToDelete()
     */    
    public function deleteAttachment($request): bool
    {
        $data = $this->getAttachmentToDelete($request);

        if(is_null($data)){
            return false;
        }
        $data->delete();
        return true;
    }
    /**
     * Get the attachment to delete.
     *
     * @param array $request
     *              id (int) ID of the expense attachment
     *              company_id (array) ID of the user company
     * 
     * @return mixed Returns the expense attachment record
     */
    private function getAttachmentToDelete(array $request): mixed
    {
        return $this->totalManagementExpensesAttachments::join('total_management_expenses', 'total_management_expenses.id', 'total_management_expenses_attachments.file_id')
            ->join('workers', function ($join) use ($request) {
                $join->on('workers.id', '=', 'total_management_expenses.worker_id')
                    ->whereIn('workers.company_id', $request['company_id']);
            })
            ->select('total_management_expenses.id')
            ->find($request['id']);
    }
    /**
     * payback submit for a total management expense.
     * 
     * @param $request
     *        id (int) ID of the expense
     *        company_id (array) ID of the user company
     *        amount_paid (float) paid amount
     *        payment_date (date) payment date
     * 
     * @return bool|array Returns true if the payback is successful. Returns an error array if validation fails or any error occurs during the payback process.
     *                    Returns self::ERROR_PAYBACK if payback amount exceed actual amount
     * 
     * 
     * @see validatePayBackRequest()
     * @see enrichRequestWithUserDetails()
     * @see getExpense()
     * @see updateExpenseAfterPayBack()
     * 
     */
    public function payBack($request) : bool|array
    {
        $validationResult = $this->validatePayBackRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $expense = $this->getExpense($request['id'], $request['company_id']);

        $totalPayBack = $expense->deduction + $request['amount_paid'];
        $remainingAmount = $expense->amount - $totalPayBack;

        if($totalPayBack > $expense->amount) {
            return self::ERROR_PAYBACK;
        }

        $this->updateExpenseAfterPayBack($expense, $request, $remainingAmount);

        return true;
    }
    /**
     * Update the expense after payback.
     *
     * @param $expense
     * @param $request
     *        amount_paid (float) paid amount
     *        payment_date (date) payment date
     *        modified_by (int) The ID of the user who modified the payback
     * @param $remainingAmount remaining amount
     * 
     * @return void
     */
    private function updateExpenseAfterPayBack($expense, $request, $remainingAmount)
    {
        $expense->amount_paid = $request['amount_paid'];
        $expense->deduction = $expense->deduction + $request['amount_paid'];
        $expense->payment_date = $request['payment_date'] ?? $expense->payment_date;
        $expense->remaining_amount = $remainingAmount;
        $expense->modified_by = $request['modified_by'] ?? $expense->modified_by;
        $expense->save();
    }
}

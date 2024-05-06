<?php

namespace App\Services;

use App\Models\DirectRecruitmentExpenses;
use App\Models\DirectRecruitmentExpensesAttachments;
use App\Models\DirectrecruitmentApplications;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class DirectRecruitmentExpensesServices
{
    /**
     * @var DirectRecruitmentExpenses
     */
    private DirectRecruitmentExpenses $directRecruitmentExpenses;
    /**
     * @var DirectRecruitmentExpensesAttachments
     */
    private DirectRecruitmentExpensesAttachments $directRecruitmentExpensesAttachments;
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
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * DirectRecruitmentExpensesServices constructor.
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses
     * @param DirectRecruitmentExpensesAttachments $directRecruitmentExpensesAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     */
    public function __construct(
        DirectRecruitmentExpenses            $directRecruitmentExpenses,
        DirectRecruitmentExpensesAttachments $directRecruitmentExpensesAttachments,
        ValidationServices                   $validationServices,
        AuthServices                         $authServices,
        Storage                              $storage,
        DirectrecruitmentApplications        $directrecruitmentApplications
    )
    {
        $this->directRecruitmentExpenses = $directRecruitmentExpenses;
        $this->directRecruitmentExpensesAttachments = $directRecruitmentExpensesAttachments;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }

    /**
     * Creates a new expense record.
     *
     * @param mixed $request The request data.
     * @return mixed Returns an array of expense data if successful, otherwise returns an error message.
     */
    public function create($request): mixed
    {
        $params = $this->getUserParams();
        if (!$this->validationServices->validate($request->toArray(), $this->directRecruitmentExpenses->rules)) {
            return ['validate' => $this->validationServices->errors()];
        }
        $directRecruitmentApplications = $this->directrecruitmentApplications->where('company_id', $params['company_id'])->find($request['application_id']);
        if (is_null($directRecruitmentApplications)) {
            return ['unauthorizedError' => true];
        }
        $expenses = $this->createExpenses($request, $params);
        if (request()->hasFile('attachment')) {
            $this->createAttachments($request, $expenses);
        }
        return $expenses;
    }

    /**
     * Get user parameters.
     *
     * @return array Returns an array containing the user parameters.
     */
    private function getUserParams()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return [
            'created_by' => $user['id'],
            'company_id' => $user['company_id']
        ];
    }

    /**
     * Creates a new expense entry.
     *
     * @param mixed $request The request object that contains the expense data.
     * @param mixed $params Additional parameters for creating the expense.
     *
     * @return mixed The result of the expense creation operation.
     */
    private function createExpenses($request, $params)
    {
        $attributes = $this->getExpenseAttributes($request, $params);
        return $this->directRecruitmentExpenses->create($attributes);
    }

    /**
     * Get the expense attributes.
     *
     * @param Request $request The HTTP request object.
     * @param array $params The additional parameters.
     * @return array The expense attributes.
     */
    private function getExpenseAttributes($request, $params)
    {
        $attributes = $request->all();
        $attributes['created_by'] = $attributes['created_by'] ?? $params['created_by'];
        $attributes['modified_by'] = $attributes['modified_by'] ?? $params['created_by'];
        return $attributes;
    }

    /**
     * Create attachments for the given request and expenses.
     *
     * @param Request $request The request containing the attachments.
     * @param array $expenses The expenses' information.
     * @return void
     */
    private function createAttachments($request, $expenses)
    {
        foreach ($request->file('attachment') as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = "/expenses/{$expenses['id']}$fileName";
            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));
            $fileUrl = $linode->url($filePath);

            $this->directRecruitmentExpensesAttachments::create([
                "file_id" => $expenses['id'],
                "file_name" => $fileName,
                "file_type" => 'EXPENSES',
                "file_url" => $fileUrl
            ]);
        }
    }

    /**
     * Update expenses with the given request.
     *
     * @param Request $request The request containing the expenses data.
     * @return bool|array Returns true if the expenses were successfully updated, otherwise returns an array with error information.
     */
    public function update($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!($this->validationServices->validate($request->toArray(), $this->directRecruitmentExpenses->rulesForUpdation($request->id)))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $directrecruitmentApplications = $this->directrecruitmentApplications->where('company_id', $user['company_id'])->find($request->application_id);
        if (is_null($directrecruitmentApplications)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $expenses = $this->directRecruitmentExpenses->findOrFail($request->id);
        $expenses->fill($request->only('application_id', 'title', 'payment_reference_number', 'payment_date', 'quantity', 'amount', 'remarks', 'created_by'));
        $expenses->modified_by = $user['id'];
        $expenses->save();

        if (request()->hasFile('attachment')) {
            $this->handleAttachment($request);
        }
        return true;
    }

    /**
     * Handle attachments for the given request and user.
     *
     * @param Request $request The request containing the attachments.
     * @return void
     */
    private function handleAttachment($request): void
    {
        $this->directRecruitmentExpensesAttachments->where('file_id', $request['id'])->where('file_type', 'EXPENSES')->delete();
        foreach ($request->file('attachment') as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = '/expenses/' . $request['id'] . $fileName;
            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));
            $fileUrl = $linode->url($filePath);
            $this->directRecruitmentExpensesAttachments::create([
                "file_id" => $request['id'],
                "file_name" => $fileName,
                "file_type" => 'EXPENSES',
                "file_url" => $fileUrl
            ]);
        }
    }


    /**
     * Show the recruitment expense details for the given request.
     *
     * @param Request $request The request containing the expense details.
     * @return mixed The recruitment expense details, or an error response.
     */
    public function show($request)
    {
        $user = $this->authenticateUser();
        $request['company_id'] = $this->getCompanyIdsFromUser($user);

        if (!$this->isValidRequestId($request)) {
            return $this->handleValidationError();
        }

        return $this->getRecruitmentExpenseDetails($request);
    }

    /**
     * Authenticate the user using the JWT token.
     *
     * @return User|null The authenticated user or null if authentication fails.
     */
    private function authenticateUser()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Get the company IDs associated with the given user.
     *
     * @param User $user The user for whom to fetch the company IDs.
     * @return array The array of company IDs.
     */
    private function getCompanyIdsFromUser($user): array
    {
        return $this->authServices->getCompanyIds($user);
    }

    /**
     * Check if the request ID is valid.
     *
     * @param Request $request The request object.
     * @param array $companyIds The company IDs.
     * @return bool Returns true if the request ID is valid, false otherwise.
     */
    private function isValidRequestId($request): bool
    {
        return $this->validationServices->validate($request, ['id' => 'required']);
    }

    /**
     * Handle validation error and return an array containing the validation errors.
     *
     * @return array An array containing the validation errors.
     */
    private function handleValidationError(): array
    {
        return [
            'validate' => $this->validationServices->errors()
        ];
    }

    /**
     * Get the recruitment expense details for the given request.
     *
     * @param $request - The request containing the necessary information.
     * @return mixed Returns the retrieved recruitment expense details.
     */
    private function getRecruitmentExpenseDetails($request): mixed
    {
        return $this->directRecruitmentExpenses
            ->with(['directRecruitmentExpensesAttachments'])
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'directrecruitment_expenses.application_id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('directrecruitment_expenses.*')
            ->find($request['id']);
    }

    /**
     * List expenses with attachments based on the given request.
     *
     * @param mixed $request The request containing the search param and application ID.
     * @return mixed Returns an array of expenses with attachments if validation passes, otherwise returns an array with validation errors.
     */
    public function list($request): mixed
    {
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->directRecruitmentExpenses
            ->leftJoin('directrecruitment_expenses_attachments', 'directrecruitment_expenses.id', '=', 'directrecruitment_expenses_attachments.file_id')
            ->LeftJoin('invoice_items_temp', function ($join) use ($request) {
                $join->on('invoice_items_temp.expense_id', '=', 'directrecruitment_expenses.id')
                    ->where('invoice_items_temp.service_id', '=', 1)
                    ->WhereNull('invoice_items_temp.deleted_at');
            })
            ->where('directrecruitment_expenses.application_id', $request['application_id'])
            ->whereNull('directrecruitment_expenses_attachments.deleted_at')
            ->where(function ($query) use ($request) {
                if (!empty($request['search_param'])) {
                    $query->where('directrecruitment_expenses.title', 'like', "%{$request['search_param']}%")
                        ->orWhere('directrecruitment_expenses.payment_reference_number', 'like', '%' . $request['search_param'] . '%');
                }

            })->select('directrecruitment_expenses.id', 'directrecruitment_expenses.application_id', 'directrecruitment_expenses.title', 'directrecruitment_expenses.payment_reference_number', 'directrecruitment_expenses.payment_date', 'directrecruitment_expenses.quantity', 'directrecruitment_expenses.amount', 'directrecruitment_expenses.remarks', 'directrecruitment_expenses_attachments.file_name', 'directrecruitment_expenses_attachments.file_url', 'directrecruitment_expenses.created_at', 'directrecruitment_expenses.invoice_number', DB::raw('IF(invoice_items_temp.id is NULL, NULL, 1) as expense_flag'))
            ->distinct()
            ->orderBy('directrecruitment_expenses.created_at', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Add other expenses for the given request.
     *
     * @param $request - The request containing the other expenses.
     * @return bool|array Returns true upon successful addition of expenses, otherwise returns an array.
     */
    public function addOtherExpenses($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $this->directRecruitmentExpenses->create([
            'application_id' => $request['expenses_application_id'],
            'title' => $request['expenses_title'] ?? '',
            'payment_reference_number' => $request['expenses_payment_reference_number'] ?? '',
            'payment_date' => ((!empty($request['expenses_payment_date'])) ? $request['expenses_payment_date'] : null),
            'quantity' => 1,
            'amount' => $request['expenses_amount'] ?? '',
            'remarks' => $request['expenses_remarks'] ?? '',
            'created_by' => $params['created_by'] ?? 0,
            'modified_by' => $params['created_by'] ?? 0
        ]);
        return true;
    }

    /**
     * Delete an attachment from direct recruitment expenses attachments.
     *
     * @param Request $request The request containing the attachment ID.
     * @return bool Returns true if the attachment was successfully deleted, false otherwise.
     */
    public function deleteAttachment($request): bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        $data = $this->directRecruitmentExpensesAttachments::join('directrecruitment_expenses', 'directrecruitment_expenses.id', 'directrecruitment_expenses_attachments.file_id')
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_applications.id', '=', 'directrecruitment_expenses.application_id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })->select('directrecruitment_expenses_attachments.id')->find($request['id']);
        if (is_null($data)) {
            return false;
        }
        $data->delete();
        return true;
    }

}

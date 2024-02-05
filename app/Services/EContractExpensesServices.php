<?php

namespace App\Services;

use App\Models\EContractExpenses;
use App\Models\EContractExpensesAttachments;
use App\Models\EContractApplications;
use App\Models\EContractProject;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class EContractExpensesServices
{
    public const ECONTRACT_EXPENSES = 'EContract Expenses';
    public const ATTACHMENT_ACTION_CREATE = 'CREATE';
    public const ATTACHMENT_ACTION_UPDATE = 'UPDATE';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const UNAUTHORIZED_ERROR = 'Unauthorized';

    /**
     * @var EContractExpenses
     */
    private EContractExpenses $eContractExpenses;

    /**
     * @var EContractExpensesAttachments
     */
    private EContractExpensesAttachments $eContractExpensesAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;

    /**
     * @var EContractProject    
     */
    private EContractProject $eContractProject;

    /**
     * Constructor method.
     * 
     * @param EContractExpenses $eContractExpenses Instance of the EContractExpenses class.
     * @param EContractExpensesAttachments $eContractExpensesAttachments Instance of the EContractExpensesAttachments class.
     * @param Storage $storage Instance of the Storage class.
     * @param AuthServices $authServices Instance of the AuthServices class.
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class.
     */
    public function __construct(
        EContractExpenses                $eContractExpenses, 
        EContractExpensesAttachments     $eContractExpensesAttachments, 
        Storage                          $storage, 
        AuthServices                     $authServices, 
        EContractApplications            $eContractApplications, 
        EContractProject                 $eContractProject
    )
    {
        $this->eContractExpenses = $eContractExpenses;
        $this->eContractExpensesAttachments = $eContractExpensesAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->eContractApplications = $eContractApplications;
        $this->eContractProject = $eContractProject;
    }

    /**
     * Creates the validation rules for e-contract expenses list search.
     *
     * @return array The array containing the validation rules.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Creates the validation rules for create a new e-contract expenses.
     *
     * @return array The array containing the validation rules.
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
     * Creates the validation rules for updating the e-contract expenses.
     *
     * @return array The array containing the validation rules.
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
     * Creates the validation rules for pay the e-contract expenses.
     *
     * @return array The array containing the validation rules.
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
     * Returns a paginated list of e-contract expenses based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns an array with a 'validate' key containing the validation errors, if the search request is invalid. Otherwise, returns a paginated list of e-contract expenses.
     */
    public function list($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->eContractExpenses
            ->leftJoin('e-contract_expenses_attachments', function($join) use ($request){
                $join->on('e-contract_expenses.id', '=', 'e-contract_expenses_attachments.file_id')
                ->whereNull('e-contract_expenses_attachments.deleted_at');
            })
            ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
            ->join('e-contract_applications', function($query) use($params) {
                $this->applyEcontractApplicationFilter($query, $params);
            })
            ->where(function ($query) use ($request) {
                $this->applyWorkerFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.amount', 'e-contract_expenses.deduction', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount_paid', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses_attachments.file_name','e-contract_expenses_attachments.file_url', 'e-contract_expenses.invoice_number','e-contract_expenses.created_at')
            ->distinct()
            ->orderBy('e-contract_expenses.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the e-contract expenses with related attachment and (e-contract project and application).
     * 
     * @param array $request The request data containing e-contract expenses id, company id
     * @return mixed Returns the e-contract expenses details with related attachment and (e-contract project and application).
     */
    public function show($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->showEContractExpenses(['id' => $request['id'], 'company_id' => $params['company_id']]);
    }

    /**
     * Creates a new e-contract expenses from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if e-contract [applications or project] is null.
     * - "validate": An array of validation errors, if any.
     * - "isSubmit": A boolean indicating if the e-contract expenses was successfully updated.
     */
    public function create($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];
        $request['created_by'] = $user['id'];

        $applicationData = $this->showEContractApplications($request);
        if (is_null($applicationData)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }
        
        $projectData = $this->showEContractProject($request);
        if (is_null($projectData)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }

        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $expenses = $this->createEContractExpenses($request);

        $this->updateEContractExpensesAttachments(self::ATTACHMENT_ACTION_CREATE, $request, $expenses['id']);

        return true;
    }

    /**
     * Updates the e-contract expenses from the given request data.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract expenses is null.
     * - "isSubmit": A boolean indicating if the e-contract expenses was successfully updated.
     */
    public function update($request): bool|array
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $expense = $this->showEContractExpenses(['id' => $request['id'], 'company_id' => $user['company_id']]);
        if (is_null($expense)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }

        $this->updateEContractExpenses($expense, $request);

        $this->updateEContractExpensesAttachments(self::ATTACHMENT_ACTION_UPDATE, $request, $expense['id']);

        return true;
    }

    /**
     * Delete the e-contract expenses
     * 
     * @param array $request The request data containing the expenses ID and company ID.
     * @return boolean The result of the delete operation containing the deletion status.
     */
    public function delete($request): bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
    
        $expense = $this->showEContractExpenses(['id' => $request['id'], 'company_id' => $user['company_id']]);
        if (is_null($expense)) {
            return false;
        }
        $expense->delete();

        return true;
    }

    /**
     * Delete the e-contract expenses attachment
     * 
     * @param array $request The request data containing the expenses ID and company ID.
     * @return boolean The result of the delete operation containing the deletion status.
     */ 
    public function deleteAttachment($request): bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);

        $data = $this->eContractExpensesAttachments::join('e-contract_expenses', 'e-contract_expenses.id', 'e-contract_expenses_attachments.file_id')
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($user) {
            $this->applyDeleteEContractApplicationTableFilter($query, $user);
        })
        ->select('e-contract_expenses_attachments.id', 'e-contract_expenses_attachments.file_id', 'e-contract_expenses_attachments.file_name', 'e-contract_expenses_attachments.file_type', 'e-contract_expenses_attachments.file_url', 'e-contract_expenses_attachments.created_by', 'e-contract_expenses_attachments.modified_by', 'e-contract_expenses_attachments.created_at', 'e-contract_expenses_attachments.updated_at', 'e-contract_expenses_attachments.deleted_at')
        ->find($request['id']);
        if (is_null($data)) {
            return false;
        }

        $data->delete();
        return true;
    }

    /**
     * Creates a new pay expenses from the given request data.
     *
     * @param array $request The array containing expenses data.
     *                      The array should have the following keys:
     *                      - id: The id of the expenses.
     *                      - amount_paid: The amount paid of the expenses.
     *                      - payment_date: The payment date of the expenses.
     *                      - modified_by: (int) The updated expenses modified by.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract expenses is null.
     * - "isSubmit": A boolean indicating if the pay expenses was successfully updated.
     */
    public function payBack($request): bool|array
    {
        $validationResult = $this->payBackValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $expense = $this->showEContractExpenses(['id' => $request['id'], 'company_id' => $user['company_id']]);
        if (is_null($expense)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $this->updatepayBackExpense($expense, $request);

        return true;
    }

    /**
     * Upload attachment of e-contract expenses.
     *
     * @param string $action The action value find the [create or update] functionality
     * @param array $request The request data containing e-contract expenses attachments
     * @param int $expensesId The attachments was upload against the expenses Id
     */
    public function updateEContractExpensesAttachments($action, $request, $expensesId): void
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContractExpenses/'.$expensesId. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $processData = [
                    'file_id' => $expensesId,
                    'file_name' => $fileName,
                    'file_type' => self::ECONTRACT_EXPENSES,
                    'file_url' =>  $fileUrl,
                ];

                if ($action == self::ATTACHMENT_ACTION_CREATE) {
                    $processData['created_by'] = $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                    $processData['modified_by'] = $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                }
                else
                {
                    $processData['created_by'] = $request['modified_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                    $processData['modified_by'] = $request['modified_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                }

                $this->eContractExpensesAttachments::create($processData);  
            }
        }
    }

    /**
     * Show the e-contract expenses with related attachment and project.
     * 
     * @param array $request The request data containing e-contract expenses id, company id
     * @return mixed Returns the e-contract expenses with related attachment and project.
     */
    public function showEContractExpenses($request): mixed
    {
        return $this->eContractExpenses::with(['eContractExpensesAttachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($request) {
            $this->applyShowEContractApplicationTableFilter($query, $request);
        })
        ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.application_id', 'e-contract_expenses.project_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount', 'e-contract_expenses.amount_paid', 'e-contract_expenses.deduction', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses.created_by', 'e-contract_expenses.modified_by', 'e-contract_expenses.is_payroll', 'e-contract_expenses.payroll_id', 'e-contract_expenses.month', 'e-contract_expenses.year', 'e-contract_expenses.invoice_number', 'e-contract_expenses.created_at', 'e-contract_expenses.updated_at', 'e-contract_expenses.deleted_at')
        ->find($request['id']);
    }
    
    /**
     * Creates a new e-contract expenses from the given request data.
     * 
     * @param array $request The array containing expenses data.
     *                      The array should have the following keys:
     *                      - worker_id: The worker id of the expenses.
     *                      - application_id: The application id of the expenses.
     *                      - project_id: The project id id of the expenses.
     *                      - title: The title of the expenses.
     *                      - type: The type of the expenses.
     *                      - payment_reference_number: The payment reference number of the expenses.
     *                      - payment_date: The payment date of the expenses.
     *                      - amount: The amount of the expenses.
     *                      - remarks: The remarks of the expenses.
     *                      - created_by: The ID of the user who created the expenses.
     * @return expenses The newly created expenses object.
     */
    public function createEContractExpenses($request): mixed
    {
        $expenses = $this->eContractExpenses->create([
            'worker_id' => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'application_id' => $request['application_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'project_id' => $request['project_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'title' => $request['title'] ?? '',
            'type' => $request['type'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : null),
            'amount' => $request['amount'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
        ]);

        return $expenses;
    }

    /**
     * Updates the e-contract expenses from the given request data.
     * 
     * @param array $request The array containing expenses data.
     *                      The array should have the following keys:
     *                      - worker_id: The updated worker id.
     *                      - application_id: The updated application id.
     *                      - project_id: The updated project id.
     *                      - title: The updated title.
     *                      - type: The updated type.
     *                      - payment_reference_number: The updated payment reference number.
     *                      - payment_date: The updated payment date.
     *                      - amount: The updated amount.
     *                      - remarks: The updated remarks.
     *                      - modified_by: The updated expenses modified by.
     */
    public function updateEContractExpenses($expense, $request): void
    {
        $expense->worker_id = $request['worker_id'] ?? $expense->worker_id;
        $expense->application_id = $request['application_id'] ?? $expense->application_id;
        $expense->project_id = $request['project_id'] ?? $expense->project_id;
        $expense->title = $request['title'] ?? $expense->title;
        $expense->type = $request['type'] ?? $expense->type;
        $expense->payment_reference_number = $request['payment_reference_number'] ?? $expense->payment_reference_number;
        $expense->payment_date = $request['payment_date'] ?? $expense->payment_date;
        $expense->amount = $request['amount'] ?? $expense->amount;
        $expense->remarks = $request['remarks'] ?? $expense->remarks;
        $expense->modified_by = $request['modified_by'] ?? $expense->modified_by;
        $expense->save();
    }

    private function listValidateRequest($request): array|bool
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        return true;
    }

    private function applyEcontractApplicationFilter($query, $params)
    {
        $query->on('e-contract_applications.id','=','e-contract_project.application_id')->where('e-contract_applications.company_id', $params['company_id']);
    }

    private function applyWorkerFilter($query, $request)
    {
        $query->where('e-contract_expenses.worker_id', $request['worker_id']);
    }

    private function applySearchFilter($query, $request)
    {
        if (isset($request['search']) && !empty($request['search'])) {
            $query->where('e-contract_expenses.title', 'like', '%'. $request['search']. '%');
        }
    }

    private function showEContractApplications($request)
    {
        return $this->eContractApplications->where('e-contract_applications.company_id', $request['company_id'])
            ->select('e-contract_applications.id')
            ->find($request['application_id']);
    }

    private function showEContractProject($request)
    {
        return $this->eContractProject
            ->join('e-contract_applications', function($query) use($request) {
                $query->on('e-contract_applications.id','=','e-contract_project.application_id')
                ->where('e-contract_applications.company_id', $request['company_id']);
            })
            ->select('e-contract_project.application_id')
            ->find($request['project_id']);
    }

    private function createValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    private function updateValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    private function applyDeleteEContractApplicationTableFilter($query, $user)
    {
        $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
    }

    private function payBackValidateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->payBackValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    private function updatepayBackExpense($expense, $request)
    {
        $totalPayBack = $expense->deduction + $request['amount_paid'];
        $remainingAmount = $expense->amount - $totalPayBack;
        if ($totalPayBack > $expense->amount) {
            return [
                'payBackError' => true
            ];
        }

        $expense->amount_paid = $request['amount_paid'];
        $expense->deduction = $totalPayBack;
        $expense->payment_date = $request['payment_date'] ?? $expense->payment_date;
        $expense->remaining_amount = $remainingAmount;
        $expense->modified_by = $request['modified_by'] ?? $expense->modified_by;
        $expense->save();
    }

    private function applyShowEContractApplicationTableFilter($query, $request)
    {
        $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $request['company_id']);
    }
}
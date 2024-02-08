<?php

namespace App\Services;

use App\Models\EContractCostManagement;
use App\Models\EContractCostManagementAttachments;
use App\Models\EContractProject;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class EContractCostManagementServices
{   
    public const ITEM_SERVICES_ID = 2;
    public const ATTACHMENT_ACTION_CREATE = 'CREATE';
    public const ATTACHMENT_ACTION_UPDATE = 'UPDATE';
    public const MESSAGE_DELETED_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const FILE_TYPE_COST_MANAGEMENT = 'COST MANAGEMENT';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const UNAUTHORIZED_ERROR = 'Unauthorized';

    /**
     * @var EContractCostManagement
     */
    private EContractCostManagement $eContractCostManagement;

    /**
     * @var EContractCostManagementAttachments
     */
    private EContractCostManagementAttachments $eContractCostManagementAttachments;

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
     * @var EContractProject
     */
    private EContractProject $eContractProject;

    /**
     * Constructor method.
     * 
     * @param EContractCostManagement $eContractCostManagement Instance of the EContractCostManagement class.
     * @param EContractCostManagementAttachments $eContractCostManagementAttachments Instance of the EContractCostManagementAttachments class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param AuthServices $authServices Instance of the AuthServices class.
     * @param Storage $storage Instance of the Storage class.
     * @param EContractProject $eContractProject Instance of the EContractProject class.
     * 
     * @return void
     */
    public function __construct(
        EContractCostManagement                $eContractCostManagement,
        EContractCostManagementAttachments     $eContractCostManagementAttachments,
        ValidationServices                     $validationServices,
        AuthServices                           $authServices,
        Storage                                $storage,
        EContractProject                       $eContractProject
    )
    {
        $this->eContractCostManagement = $eContractCostManagement;
        $this->eContractCostManagementAttachments = $eContractCostManagementAttachments;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
        $this->eContractProject = $eContractProject;
    }

    /**
     * Creates the validation rules for creating a new e-contract cost management.
     *
     * @return array The array containing the validation rules.
     */
    public function CreateValidation(): array
    {
        return [
            'project_id' => 'required|regex:/^[0-9]+$/',
            'title' => 'required|max:255',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/'
        ];
    }

    /**
     * Creates the validation rules for updating the e-contract cost management.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'title' => 'required|max:255',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/'
        ];
    }

    /**
     * Creates a new cost from the given request data.
     * 
     * @param $request The request data containing cost details.
     * @return bool|array Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if e-contract project is null.
     * - "validate": An array of validation errors, if any.
     * - "isSubmit": A boolean indicating if the e-contract cost was successfully created.
     */
    public function create($request): bool|array
    {
        $user = $this->getJwtUserAuthenticate();
        $request['company_id'] = $user['company_id'];
        $request['created_by'] = $user['id'];

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

        $eContractCostManagement = $this->createEContractCostManagement($request);

        $this->uploadEContractCostManagementAttachments(self::ATTACHMENT_ACTION_CREATE, $request, $eContractCostManagement['id']);

        return true;
    }

    /**
     * Updates the cost data with the given request.
     * 
     * @param $request The request data containing cost details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract cost management is null.
     * - "isSubmit": A boolean indicating if the e-contract cost was successfully updated.
     */
    public function update($request): bool|array
    {
        $user = $this->getJwtUserAuthenticate();
        $request['modified_by'] = $user['id'];
        
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $eContractCostManagement = $this->showEContractCostManagement(['id' => $request['id'], 'company_id' => $user['company_id']]);
        if (is_null($eContractCostManagement)) {
            return [
                'unauthorizedError' => self::UNAUTHORIZED_ERROR
            ];
        }

        $this->updateEContractCostManagement($eContractCostManagement, $request);

        $this->uploadEContractCostManagementAttachments(self::ATTACHMENT_ACTION_UPDATE, $request, $request['id']);

        return true;
    }
    
    /**
     * Show the e-contract cost management with related e-contract project and application.
     * 
     * @param array $request The request data containing e-contract cost management id,  company id
     * @return mixed Returns the e-contract cost management details with related e-contract project application.
     */
    public function show($request): mixed
    {
        $user = $this->getJwtUserAuthenticate();
        $params['company_id'] = $this->getAuthUserCompanyIds($user);

        $validationResult = $this->showValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->showEContractCostManagement(['id' => $request['id'], 'company_id' => $params['company_id']]);
    }
    
    /**
     * Returns a paginated list of e-contract cost management based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of e-contract cost management.
     */
    public function list($request): mixed
    {
        $user = $this->getJwtUserAuthenticate();
        $params['company_id'] = $this->getAuthUserCompanyIds($user);

        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->eContractCostManagement
            ->leftJoin('e-contract_cost_management_attachments', function($join) use ($request){
                $this->applyEcontractCostManagementAttachmentsTableFilter($join);
            })
            ->LeftJoin('invoice_items_temp', function($join) use ($request){
                $this->applyInvoiceItemsTempTableFilter($join);
            })
            ->join('e-contract_project', 'e-contract_project.id', 'e-contract_cost_management.project_id')
            ->join('e-contract_applications', function($query) use($params) {
                $this->applyEContractApplicationsTableFilter($query, $params);
            })
            ->where(function ($query) use ($request) {
                $this->applyEContractCostManagementTableFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);          
            })->select('e-contract_cost_management.id','e-contract_cost_management.project_id','e-contract_cost_management.title','e-contract_cost_management.payment_reference_number','e-contract_cost_management.payment_date','e-contract_cost_management.quantity','e-contract_cost_management.amount','e-contract_cost_management.remarks', 'e-contract_cost_management.invoice_status', 'e-contract_cost_management_attachments.file_name','e-contract_cost_management_attachments.file_url','e-contract_cost_management.created_at','e-contract_cost_management.invoice_number',\DB::raw('IF(invoice_items_temp.id is NULL, NULL, 1) as expense_flag'))
            ->distinct()
            ->orderBy('e-contract_cost_management.id','DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Delete the e-contract cost management
     * 
     * @param array $request The request data containing the e-contract cost management.
     * @return array The result of the delete operation containing the deletion status and message.
     */  
    public function delete($request): mixed
    {   
        $user = $this->getJwtUserAuthenticate();
        $params['company_id'] = $this->getAuthUserCompanyIds($user);

        $eContractCostManagement = $this->showEContractCostManagement(['id' => $request['id'], 'company_id' => $params['company_id']]);
        if (is_null($eContractCostManagement)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DELETED_NOT_FOUND
            ];
        }

        $eContractCostManagement->eContractCostManagementAttachments()->delete();
        $eContractCostManagement->delete();
        return [
            "isDeleted" => true,
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Delete the e-contract cost management attachment
     * 
     * @param array $request The request data containing the attachment ID.
     * @return array The result of the delete operation containing the deletion status and message.
     */
    public function deleteAttachment($request): mixed
    {   
        $user = $this->getJwtUserAuthenticate();
        $params['company_id'] = $this->getAuthUserCompanyIds($user);

        $data = $this->eContractCostManagementAttachments
        ->join('e-contract_cost_management', 'e-contract_cost_management.id', 'e-contract_cost_management_attachments.file_id')
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_cost_management.project_id')
        ->join('e-contract_applications', function($query) use($params) {
            $this->applyEContractApplicationsTableFilter($query, $params);
        })
        ->select('e-contract_cost_management_attachments.id', 'e-contract_cost_management_attachments.file_id', 'e-contract_cost_management_attachments.file_name', 'e-contract_cost_management_attachments.file_type', 'e-contract_cost_management_attachments.file_url', 'e-contract_cost_management_attachments.created_by', 'e-contract_cost_management_attachments.modified_by', 'e-contract_cost_management_attachments.created_at', 'e-contract_cost_management_attachments.updated_at', 'e-contract_cost_management_attachments.deleted_at')
        ->find($request['id']);
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DELETED_NOT_FOUND
            ];
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    
    /**
     * Creates a new e-contract cost management from the given request data.
     *
     * @param array $request The array containing cost data.
     *                      The array should have the following keys:
     *                      - project_id: The project id of the cost.
     *                      - title: The title of the cost.
     *                      - payment_reference_number: The payment reference number of the cost.
     *                      - payment_date: The payment date of the cost.
     *                      - quantity: The quantity of the cost.
     *                      - amount: The amount of the cost.
     *                      - remarks: The remarks of the cost.
     *                      - created_by: The ID of the user who created the cost.
     *                      - modified_by: The updated cost modified by.
     * 
     * @return cost The newly created project object.
     */
    public function createEContractCostManagement($request)
    {
        return $this->eContractCostManagement->create([
            'project_id' => $request['project_id'],
            'title' => $request['title'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : null),
            'quantity' => $request['quantity'] ?? '',
            'amount' => $request['amount'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }
    
    /**
     * Updates the e-contract cost management from the given request data.
     * 
     * @param array $request The array containing cost data.
     *                      The array should have the following keys:
     *                      - title: The updated title.
     *                      - payment_reference_number: The updated payment reference number.
     *                      - payment_date: The updated payment date.
     *                      - amount: The updated amount.
     *                      - quantity: The updated quantity.
     *                      - remarks: The updated remarks.
     *                      - created_by: The ID of the user who created the cost.
     *                      - modified_by: The updated cost modified by.
     * 
     * @return void
     */
    public function updateEContractCostManagement($eContractCostManagement, $request)
    {
        $eContractCostManagement->title = $request['title'] ?? $eContractCostManagement->title;
        $eContractCostManagement->payment_reference_number = $request['payment_reference_number'] ?? $eContractCostManagement->payment_reference_number;
        $eContractCostManagement->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $eContractCostManagement->payment_date);
        $eContractCostManagement->amount = $request['amount'] ?? $eContractCostManagement->amount;
        $eContractCostManagement->quantity = $request['quantity'] ?? $eContractCostManagement->quantity;
        $eContractCostManagement->remarks = $request['remarks'] ?? $eContractCostManagement->remarks;
        $eContractCostManagement->created_by = $request['created_by'] ?? $eContractCostManagement->created_by;
        $eContractCostManagement->modified_by = $request['modified_by'];
        $eContractCostManagement->save();
    }

    /**
     * Upload attachment of e-contract cost management.
     *
     * @param string $action The action value find the [create or update] functionality
     * @param array $request The request data containing e-contract cost management
     * @param int $costManagementId The attachments was upload against the cost management Id
     * 
     * @return void
     */
    public function uploadEContractCostManagementAttachments($action, $request, $costManagementId)
    {
        if (request()->hasFile('attachment')) {

            if ($action = self::ATTACHMENT_ACTION_UPDATE) {
                $this->eContractCostManagementAttachments->where('file_id', $costManagementId)->where('file_type', 'COST MANAGEMENT')->delete();
            }

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/costManagement/'.$costManagementId. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractCostManagementAttachments::create([
                        "file_id" => $costManagementId,
                        "file_name" => $fileName,
                        "file_type" => self::FILE_TYPE_COST_MANAGEMENT,
                        "file_url" =>  $fileUrl
                    ]);  
            }
        }
    }

    /**
     * Show the e-contract cost management with related attachment and project, application.
     * 
     * @param array $request The request data containing e-contract cost management id, company id
     * @return mixed Returns the e-contract cost management with related attachment and application.
     */
    public function showEContractCostManagement($request): mixed
    {
        return $this->eContractCostManagement->with('eContractCostManagementAttachments')
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_cost_management.project_id')
        ->join('e-contract_applications', function($query) use($request) {
            $this->applyEContractApplicationsTableFilter($query, $request);
        })
        ->select('e-contract_cost_management.id', 'e-contract_cost_management.project_id', 'e-contract_cost_management.title', 'e-contract_cost_management.payment_reference_number', 'e-contract_cost_management.quantity', 'e-contract_cost_management.amount', 'e-contract_cost_management.payment_date', 'e-contract_cost_management.remarks', 'e-contract_cost_management.invoice_id', 'e-contract_cost_management.invoice_status', 'e-contract_cost_management.invoice_number', 'e-contract_cost_management.is_payroll', 'e-contract_cost_management.payroll_id', 'e-contract_cost_management.month', 'e-contract_cost_management.year', 'e-contract_cost_management.created_by', 'e-contract_cost_management.modified_by', 'e-contract_cost_management.created_at', 'e-contract_cost_management.updated_at', 'e-contract_cost_management.deleted_at')
        ->find($request['id']);
    }
    
    /**
     * Show the e-contract project.
     * 
     * @param array $request The request data containing project id, company id
     * @return mixed Returns the e-contract project.
     */
    private function showEContractProject($request)
    {
        return $this->eContractProject
            ->join('e-contract_applications', function($query) use($request) {
                $query->on('e-contract_applications.id','=','e-contract_project.application_id')->where('e-contract_applications.company_id', $request['company_id']);
            })
            ->select('e-contract_project.application_id')
            ->find($request['project_id']);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request->toArray(),$this->CreateValidation()))) {
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request->toArray(),$this->updateValidation()))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function showValidateRequest($request): array|bool
    {
        if (!($this->validationServices->validate($request, ['id' => 'required']))) {
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if (!empty($request['search_param'])) {
            if (!($this->validationServices->validate($request, ['search_param' => 'required|min:3']))) {
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }
    
    /**
     * Apply the "EContract cost management" filter to the join
     *
     * @param Illuminate\Database\Query\Builder $join The join builder instance
     *
     * @return void
     */
    private function applyEcontractCostManagementAttachmentsTableFilter($join)
    {
        $join->on('e-contract_cost_management.id', '=', 'e-contract_cost_management_attachments.file_id')->whereNull('e-contract_cost_management_attachments.deleted_at');
    }
    
    /**
     * Apply the "Invoice items temp" filter to the join
     *
     * @param Illuminate\Database\Query\Builder $join The join builder instance
     *
     * @return void
     */
    private function applyInvoiceItemsTempTableFilter($join)
    {
        $join->on('invoice_items_temp.expense_id', '=', 'e-contract_cost_management.id')
            ->where('invoice_items_temp.service_id', '=', self::ITEM_SERVICES_ID)
            ->WhereNull('invoice_items_temp.deleted_at');
    }
    
    /**
     * Apply the "e-contract applications" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyEContractApplicationsTableFilter($query, $request)
    {
        $query->on('e-contract_applications.id','=','e-contract_project.application_id')->where('e-contract_applications.company_id', $request['company_id']);
    }
    
    /**
     * Apply the "e-contract cost management" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the project id
     *
     * @return void
     */
    private function applyEContractCostManagementTableFilter($query, $request)
    {
        $query->where('e-contract_cost_management.project_id', $request['project_id']);
    }

    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('e-contract_cost_management.title', 'like', "%{$request['search_param']}%")
            ->orWhere('e-contract_cost_management.payment_reference_number', 'like', '%'.$request['search_param'].'%');
        }
    }

    /**
     * get the user of jwt authenticate.
     *
     * @return mixed Returns the user data.
     */
    private function getJwtUserAuthenticate(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * get the auth user of company ids.
     * @param array $user The user data containing the user details
     * 
     * @return mixed Returns the user company ids.
     */
    private function getAuthUserCompanyIds($user): mixed
    {
        return $this->authServices->getCompanyIds($user);
    }
}

<?php

namespace App\Services;

use App\Models\TotalManagementCostManagement;
use App\Models\TotalManagementCostManagementAttachments;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class TotalManagementCostManagementServices
{
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ATTACHMENT_FILE_TYPE = 'COSTMANAGEMENT';
    public const MESSAGE_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const TOTALMANAGEMENT_SERVICE_ID = 3;
    public const DEFAULT_VALUE = 0;

    /**
     * @var totalManagementCostManagement
     */
    private TotalManagementCostManagement $totalManagementCostManagement;
    /**
     * @var totalManagementCostManagementAttachments
     */
    private TotalManagementCostManagementAttachments $totalManagementCostManagementAttachments;
    /**
     * @var validationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var authServices
     */
    private AuthServices $authServices;
    /**
     * @var storage
     */
    private Storage $storage;
    /**
     * TotalManagementCostManagementServices constructor.
     * 
     * @param TotalManagementCostManagement $totalManagementCostManagement The totalManagementCostManagement object.
     * @param TotalManagementCostManagementAttachments $totalManagementCostManagementsAttachments The totalManagementCostManagementsAttachments object.
     * @param ValidationServices $validationServices The validationServices object.
     * @param AuthServices $authServices The authServices object.
     * @param Storage $storage The storage object.
     */
    public function __construct(
            TotalManagementCostManagement                 $totalManagementCostManagement,
            TotalManagementCostManagementAttachments      $totalManagementCostManagementAttachments,
            ValidationServices                      $validationServices,
            AuthServices                            $authServices,
            Storage                                 $storage
    )
    {
        $this->totalManagementCostManagement = $totalManagementCostManagement;
        $this->totalManagementCostManagementAttachments = $totalManagementCostManagementAttachments;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
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
     * Validate the create cost management request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the update cost management request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the show cost management request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateShowRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the list cost management request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListRequest($request): array|bool
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Create a cost management.
     *
     * @param mixed $request The request data.
     *
     * @return mixed Returns the created cost management record
     *               otherwise Returns an error array if validation fails 
     * 
     * @see enrichRequestWithUserDetails()
     * @see validateCreateRequest()
     * @see createTotalManagementCostManagement()
     * @see uploadCostManagementFiles()
     * 
     * 
     */
    public function create($request) : mixed
    {
        $request = $this->enrichRequestWithUserDetails($request);

        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $costManagement = $this->createTotalManagementCostManagement($request);

        $this->uploadCostManagementFiles($request, $costManagement->id);

        return $costManagement;
    }
    /**
     * create total management cost management.
     *
     * @param array $request
     *              application_id (in) ID of the application
     *              project_id (int) ID of the project
     *              title (string) title of the cost management
     *              payment_reference_number (int) payment reference number
     *              payment_date (date) date of the payment
     *              quantity (int) quantity of cost management
     *              amount (float) amount of payment
     *              remarks (text) remarks of payment
     *              created_by The ID of the user who created the cost mangment.
     * 
     * @return mixed Returns the created cost management record.
     */
    private function createTotalManagementCostManagement($request): mixed
    {
        return $this->totalManagementCostManagement->create([
            'application_id' => $request['application_id'],
            'project_id' => $request['project_id'],
            'title' => $request['title'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => $request['payment_date'] ?? null,
            'quantity' => $request['quantity'] ?? '',
            'amount' => $request['amount'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $request['created_by'] ?? self::DEFAULT_VALUE,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_VALUE
        ]);
    }
    /**
     * Upload attachment of cost management.
     *
     * @param array $request
     *              attachment (file) 
     * @param int $costManagement
     * 
     * @return void
     */
    private function uploadCostManagementFiles($request, $costManagementId): void
    {
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/tmCostManagement/' . $costManagementId . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->totalManagementCostManagementAttachments::create([
                    'file_id' => $costManagementId,
                    'file_name' => $fileName,
                    'file_type' => self::ATTACHMENT_FILE_TYPE,
                    'file_url' => $fileUrl,
                ]);
            }
        }
    }
    /**
     * delete attachment of cost management.
     *
     * @param int $costManagementId
     * 
     * @return void
     */
    private function deleteCostManagementFiles($costManagementId): void
    {
        $this->totalManagementCostManagementAttachments->where('file_id', $costManagementId)->where('file_type', self::ATTACHMENT_FILE_TYPE)->delete();
    }
    /**
     * update total management cost management.
     * 
     * @param $request
     * 
     * @return bool|array Returns true if the update is successful. Returns an error array if validation fails or any error occurs during the update process.
     *                    Returns self::ERROR_UNAUTHORIZED if the user access invalid cost management
     * 
     * 
     * @see enrichRequestWithUserDetails()
     * @see validateUpdateRequest()
     * @see getCostManagement()
     * @see updateTotalManagementCostManagement()
     * // @see deleteCostManagementFiles()
     * @see uploadCostManagementFiles()
     * 
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $costManagement = $this->getCostManagement($request['id'], $request['company_id']);
        
        if (is_null($costManagement)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $this->updateTotalManagementCostManagement($costManagement, $request);

        //$this->deleteCostManagementFiles($request['id']);

        $this->uploadCostManagementFiles($request, $costManagement->id);

    return true;

    }

    /**
     * Retrieve cost management record by ID and company ID.
     *
     * @param int $costManagementId
     * @param array $companyIds
     * @return mixed Returns the cost management detail

     */
    private function getCostManagement(int $costManagementId, array $companyIds)
    {
        return $this->totalManagementCostManagement
            ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
            ->join('total_management_applications', function ($join) use ($companyIds) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $companyIds);
            })
            ->select('total_management_cost_management.id', 'total_management_cost_management.application_id', 'total_management_cost_management.project_id', 'total_management_cost_management.title', 'total_management_cost_management.payment_reference_number', 'total_management_cost_management.quantity', 'total_management_cost_management.amount', 'total_management_cost_management.payment_date', 'total_management_cost_management.remarks', 'total_management_cost_management.created_by', 'total_management_cost_management.modified_by', 'total_management_cost_management.invoice_number', 'total_management_cost_management.is_payroll', 'total_management_cost_management.payroll_id', 'total_management_cost_management.month', 'total_management_cost_management.year', 'total_management_cost_management.created_at', 'total_management_cost_management.updated_at', 'total_management_cost_management.deleted_at')
            ->find($costManagementId);
    }
    /**
     * Update cost management based on the provided request.
     *
     * @param mixed $costManagement
     * @param $request
     *        application_id (in) ID of the application
     *        project_id (int) ID of the project
     *        title (string) title of the cost management
     *        payment_reference_number (int) payment reference number
     *        payment_date (date) date of the payment
     *        quantity (int) quantity of cost management
     *        amount (float) amount of payment
     *        remarks (text) remarks of payment
     *        created_by The ID of the user who created the cost mangment.
     *        modified_by The ID of the user who modified the cost mangment.
     * 
     * @return void
     */
    private function updateTotalManagementCostManagement($costManagement, $request)
    {
        $costManagement->application_id = $request['application_id'] ?? $costManagement->application_id;
        $costManagement->project_id = $request['project_id'] ?? $costManagement->project_id;
        $costManagement->title = $request['title'] ?? $costManagement->title;
        $costManagement->payment_reference_number = $request['payment_reference_number'] ?? $costManagement->payment_reference_number;
        $costManagement->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $costManagement->payment_date);
        $costManagement->amount = $request['amount'] ?? $costManagement->amount;
        $costManagement->quantity = $request['quantity'] ?? $costManagement->quantity;
        $costManagement->remarks = $request['remarks'] ?? $costManagement->remarks;
        $costManagement->created_by = $request['created_by'] ?? $costManagement->created_by;
        $costManagement->modified_by = $request['modified_by'];
        $costManagement->save();
    }

    /**
     * show total management cost management.
     * 
     * @param $request
     *        id (int) ID of the cost management record
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the cost management detail with related attachments
     * 
     * @see validateShowRequest()
     * 
     */
    public function show($request) : mixed
    {
        $validationResult = $this->validateShowRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        return $this->totalManagementCostManagement->with('totalManagementCostManagementAttachments')
        ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        })
        ->select('total_management_cost_management.id', 'total_management_cost_management.application_id', 'total_management_cost_management.project_id', 'total_management_cost_management.title', 'total_management_cost_management.payment_reference_number', 'total_management_cost_management.quantity', 'total_management_cost_management.amount', 'total_management_cost_management.payment_date', 'total_management_cost_management.remarks', 'total_management_cost_management.created_by', 'total_management_cost_management.modified_by', 'total_management_cost_management.invoice_number', 'total_management_cost_management.is_payroll', 'total_management_cost_management.payroll_id', 'total_management_cost_management.month', 'total_management_cost_management.year', 'total_management_cost_management.created_at', 'total_management_cost_management.updated_at', 'total_management_cost_management.deleted_at')
        ->find($request['id']);
    }
    
    /**
     * list total management cost management.
     * 
     * @param $request
     *        search_param (string) search search parameter
     *        project_id (int) ID of the project
     * 
     * @return mixed Returns The paginated list of cost management
     * 
     * @see applyCondition()
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
        
        $data = $this->totalManagementCostManagement
            ->leftJoin('total_management_cost_management_attachments', function($join) use ($request){
                $join->on('total_management_cost_management.id', '=', 'total_management_cost_management_attachments.file_id')
                ->whereNull('total_management_cost_management_attachments.deleted_at');
            })
            ->LeftJoin('invoice_items_temp', function($join) use ($request){
                $join->on('invoice_items_temp.expense_id', '=', 'total_management_cost_management.id')
                ->where('invoice_items_temp.service_id', '=', self::TOTALMANAGEMENT_SERVICE_ID)
                ->WhereNull('invoice_items_temp.deleted_at');
            });
        $data = $this->applyCondition($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->ListSelectColumns($data)
                    ->orderBy('total_management_cost_management.created_at','DESC')
                    ->paginate(Config::get('services.paginate_worker_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        project_id (int) ID of the project
     * @param object $data
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->where('total_management_cost_management.project_id', $request['project_id']);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search_param (string) search parameter
     * @param object $data
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search_param'] ?? '';
            if(!empty($search)) {
                $query->where('total_management_cost_management.title', 'like', "%{$search}%")
                ->orWhere('total_management_cost_management.payment_reference_number', 'like', '%'.$search.'%');
            }            
        });
    }

    /**
     * Select the cost management columns from the list cost management query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('total_management_cost_management.id','total_management_cost_management.application_id','total_management_cost_management.project_id','total_management_cost_management.title','total_management_cost_management.payment_reference_number','total_management_cost_management.payment_date','total_management_cost_management.quantity','total_management_cost_management.amount','total_management_cost_management.remarks','total_management_cost_management_attachments.file_name','total_management_cost_management_attachments.file_url','total_management_cost_management.created_at','total_management_cost_management.invoice_number',\DB::raw('IF(invoice_items_temp.id is NULL, NULL, 1) as expense_flag'))
            ->distinct();
    }

    /**
     * delete the cost management.
     *
     * @param $request
     *        id (int) ID of the cost management
     * 
     * @return mixed The result of the delete operation containing the deletion status and message.
     * 
     * @see getCostManagementToDelete()
     * 
     */    
    public function delete($request): mixed
    {
        $totalManagementCostManagement = $this->getCostManagementToDelete($request);

        if(is_null($totalManagementCostManagement)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_NOT_FOUND
            ];
        }
        $totalManagementCostManagement->totalManagementCostManagementAttachments()->delete();
        $totalManagementCostManagement->delete();
        return [
            "isDeleted" => true,
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Get the cost management data to delete.
     *
     * @param $request
     *        id (int) ID of the cost management
     *        company_id ID of the user company
     * 
     * @return mixed Returns the cost management data
     */
    private function getCostManagementToDelete($request)
    {
        return $this->totalManagementCostManagement
            ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $request['company_id']);
            })
            ->select('total_management_cost_management.id')
            ->find($request['id']);
    }

    /**
     * delete the attchment of cost management.
     *
     * @param $request
     *        id (int) ID of the attachment
     * 
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     * 
     * @see getAttachmentToDelete()
     * 
     */    
    public function deleteAttachment($request): mixed
    {
        $data = $this->getAttachmentToDelete($request);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_NOT_FOUND
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Get the attachment data to delete.
     *
     * @param array $request
     *              id (int) ID of the attachment
     *              company_id (array) ID of the user company
     * 
     * @return mixed Returns the cost management attachment data
     */
    private function getAttachmentToDelete($request)
    {
        return $this->totalManagementCostManagementAttachments
            ->join('total_management_cost_management', 'total_management_cost_management.id', 'total_management_cost_management_attachments.file_id')
            ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $request['company_id']);
            })
            ->select('total_management_cost_management_attachments.id')
            ->find($request['id']);
    }

}

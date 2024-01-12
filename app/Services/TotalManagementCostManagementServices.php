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
    public const ATTACHMENT_FILE_TYPE = 'COSTMANAGEMENT';
    public const MESSAGE_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";

    private TotalManagementCostManagement $totalManagementCostManagement;
    private TotalManagementCostManagementAttachments $totalManagementCostManagementAttachments;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * TotalManagementCostManagementServices constructor.
     * 
     * @param TotalManagementCostManagement $totalManagementCostManagement
     * @param TotalManagementCostManagementAttachments $totalManagementCostManagementsAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
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
     * Create a cost management.
     *
     * @param mixed $request The request data.
     *
     * @return mixed The response data.
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        
        $costManagement = $this->createTotalManagementCostManagement($request);

        $this->uploadCostManagementFiles($request, $costManagement->id);

        return $costManagement;
    }
    /**
     * create total management cost management.
     *
     * @param array $request
     * @return mixed
     */
    private function createTotalManagementCostManagement($request): mixed
    {
        return $this->totalManagementCostManagement->create([
            'application_id' => $request['application_id'],
            'project_id' => $request['project_id'],
            'title' => $request['title'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : null),
            'quantity' => $request['quantity'] ?? '',
            'amount' => $request['amount'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
    }
    /**
     * Upload attachment of cost management.
     *
     * @param array $request
     * @param int $costManagement
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
     * @return bool|array
     */
    public function update($request): bool|array
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $params['company_id'] = $this->authServices->getCompanyIds($user);

        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $costManagement = $this->getCostManagement($request['id'], $params['company_id']);

        if (is_null($costManagement)) {
            return ['unauthorizedError' => true];
        }

        $this->updateTotalManagementCostManagement($costManagement, $request);

        $this->deleteCostManagementFiles($request['id']);

        $this->uploadCostManagementFiles($request, $costManagement->id);

    return true;

    }

    /**
     * Retrieve cost management record by ID and company ID.
     *
     * @param int $costManagementId
     * @param array $companyIds
     * @return mixed
     */
    private function getCostManagement(int $costManagementId, array $companyIds)
    {
        return $this->totalManagementCostManagement
            ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
            ->join('total_management_applications', function ($join) use ($companyIds) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $companyIds);
            })
            ->select('total_management_cost_management.*')
            ->find($costManagementId);
    }
    /**
     * Update cost management based on the provided request.
     *
     * @param mixed $costManagement
     * @param $request
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
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->totalManagementCostManagement->with('totalManagementCostManagementAttachments')
        ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        })
        ->select('total_management_cost_management.*')
        ->find($request['id']);
    }
    
    /**
     * list total management cost management.
     * 
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->totalManagementCostManagement
        ->leftJoin('total_management_cost_management_attachments', function($join) use ($request){
            $join->on('total_management_cost_management.id', '=', 'total_management_cost_management_attachments.file_id')
            ->whereNull('total_management_cost_management_attachments.deleted_at');
          })
        ->LeftJoin('invoice_items_temp', function($join) use ($request){
            $join->on('invoice_items_temp.expense_id', '=', 'total_management_cost_management.id')
            ->where('invoice_items_temp.service_id', '=', 3)
            ->WhereNull('invoice_items_temp.deleted_at');
          })
        ->where('total_management_cost_management.project_id', $request['project_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('total_management_cost_management.title', 'like', "%{$request['search_param']}%")
                ->orWhere('total_management_cost_management.payment_reference_number', 'like', '%'.$request['search_param'].'%');
            }            
        })->select('total_management_cost_management.id','total_management_cost_management.application_id','total_management_cost_management.project_id','total_management_cost_management.title','total_management_cost_management.payment_reference_number','total_management_cost_management.payment_date','total_management_cost_management.quantity','total_management_cost_management.amount','total_management_cost_management.remarks','total_management_cost_management_attachments.file_name','total_management_cost_management_attachments.file_url','total_management_cost_management.created_at','total_management_cost_management.invoice_number',\DB::raw('IF(invoice_items_temp.id is NULL, NULL, 1) as expense_flag'))
        ->distinct()
        ->orderBy('total_management_cost_management.created_at','DESC')
        ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * delete the cost management.
     *
     * @param $request
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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

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
    private TotalManagementCostManagement $totalManagementCostManagement;
    private TotalManagementCostManagementAttachments $totalManagementCostManagementAttachments;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * WorkersServices constructor.
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
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        $costManagement = $this->totalManagementCostManagement->create([
            'application_id' => $request['application_id'],
            'project_id' => $request['project_id'],
            'title' => $request['title'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : null),
            'quantity' => $request['quantity'] ?? '',
            'amount' => $request['amount'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/tmCostManagement/'.$costManagement['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->totalManagementCostManagementAttachments::create([
                        "file_id" => $costManagement['id'],
                        "file_name" => $fileName,
                        "file_type" => 'COSTMANAGEMENT',
                        "file_url" =>  $fileUrl
                    ]);  
            }
        }

        return $costManagement;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];

        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementCostManagement->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $costManagement = $this->totalManagementCostManagement->findOrFail($request['id']);
        $costManagement->application_id = $request['application_id'] ?? $costManagement->application_id;
        $costManagement->project_id = $request['project_id'] ?? $costManagement->project_id;
        $costManagement->title = $request['title'] ?? $costManagement->title;
        $costManagement->payment_reference_number = $request['payment_reference_number'] ?? $costManagement->payment_reference_number;
        $costManagement->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $costManagement->payment_date);
        $costManagement->amount = $request['amount'] ?? $costManagement->amount;
        $costManagement->quantity = $request['quantity'] ?? $costManagement->quantity;
        $costManagement->remarks = $request['remarks'] ?? $costManagement->remarks;
        $costManagement->created_by = $request['created_by'] ?? $costManagement->created_by;
        $costManagement->modified_by = $params['modified_by'];
        $costManagement->save();

        if (request()->hasFile('attachment')){

            $this->totalManagementCostManagementAttachments->where('file_id', $request['id'])->where('file_type', 'COSTMANAGEMENT')->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/tmCostManagement/'.$request['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->totalManagementCostManagementAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'COSTMANAGEMENT',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        return true;
    }
    
    
    /**
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
        return $this->totalManagementCostManagement->with('totalManagementCostManagementAttachments')->findOrFail($request['id']);
    }
    
    /**
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
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * delete the specified Vendors data.
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {   
        $totalManagementCostManagement = $this->totalManagementCostManagement::find($request['id']);

        if(is_null($totalManagementCostManagement)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $totalManagementCostManagement->totalManagementCostManagementAttachments()->delete();
        $totalManagementCostManagement->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->totalManagementCostManagementAttachments::find($request['id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

}

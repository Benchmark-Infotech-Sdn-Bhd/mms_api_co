<?php

namespace App\Services;

use App\Models\EContractCostManagement;
use App\Models\EContractCostManagementAttachments;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class EContractCostManagementServices
{
    private EContractCostManagement $eContractCostManagement;
    private EContractCostManagementAttachments $eContractCostManagementAttachments;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * EContractCostManagementServices constructor.
     * @param EContractCostManagement $eContractCostManagement
     * @param EContractCostManagementAttachments $eContractCostManagementsAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            EContractCostManagement                 $eContractCostManagement,
            EContractCostManagementAttachments     $eContractCostManagementAttachments,
            ValidationServices                      $validationServices,
            AuthServices                            $authServices,
            Storage                                 $storage
    )
    {
        $this->eContractCostManagement = $eContractCostManagement;
        $this->eContractCostManagementAttachments = $eContractCostManagementAttachments;
        $this->validationServices = $validationServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
    }
    /**
     * @return array
     */
    public function CreateValidation(): array
    {
        return [
            'project_id' => 'required|regex:/^[0-9]+$/',
            'title' => 'required|max:255',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|max:9|regex:/^[0-9]+(\.[0-9][0-9]?)?$/'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'title' => 'required|max:255',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9-]*$/',
            'payment_date' => 'required|date_format:Y-m-d|before:tomorrow',
            'amount' => 'required|max:9|regex:/^[0-9]+(\.[0-9][0-9]?)?$/'
        ];
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
        if(!($this->validationServices->validate($request->toArray(),$this->CreateValidation()))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        $eContractCostManagement = $this->eContractCostManagement->create([
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
                $filePath = '/eContract/costManagement/'.$eContractCostManagement['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractCostManagementAttachments::create([
                        "file_id" => $eContractCostManagement['id'],
                        "file_name" => $fileName,
                        "file_type" => 'COST MANAGEMENT',
                        "file_url" =>  $fileUrl
                    ]);  
            }
        }

        return true;
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

        if(!($this->validationServices->validate($request->toArray(),$this->updateValidation()))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $eContractCostManagement = $this->eContractCostManagement->findOrFail($request['id']);
        $eContractCostManagement->title = $request['title'] ?? $eContractCostManagement->title;
        $eContractCostManagement->payment_reference_number = $request['payment_reference_number'] ?? $eContractCostManagement->payment_reference_number;
        $eContractCostManagement->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $eContractCostManagement->payment_date);
        $eContractCostManagement->amount = $request['amount'] ?? $eContractCostManagement->amount;
        $eContractCostManagement->quantity = $request['quantity'] ?? $eContractCostManagement->quantity;
        $eContractCostManagement->remarks = $request['remarks'] ?? $eContractCostManagement->remarks;
        $eContractCostManagement->created_by = $request['created_by'] ?? $eContractCostManagement->created_by;
        $eContractCostManagement->modified_by = $params['modified_by'];
        $eContractCostManagement->save();

        if (request()->hasFile('attachment')){

            $this->eContractCostManagementAttachments->where('file_id', $request['id'])->where('file_type', 'COST MANAGEMENT')->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/costManagement/'.$request['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractCostManagementAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'COST MANAGEMENT',
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
        return $this->eContractCostManagement->with('eContractCostManagementAttachments')->findOrFail($request['id']);
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
        return $this->eContractCostManagement
        ->leftJoin('e-contract_cost_management_attachments', function($join) use ($request){
            $join->on('e-contract_cost_management.id', '=', 'e-contract_cost_management_attachments.file_id')
            ->whereNull('e-contract_cost_management_attachments.deleted_at');
          })
        ->LeftJoin('invoice_items_temp', function($join) use ($request){
            $join->on('invoice_items_temp.expense_id', '=', 'e-contract_cost_management.id')
            ->where('invoice_items_temp.service_id', '=', 2)
            ->WhereNull('invoice_items_temp.deleted_at');
          })
        ->where('e-contract_cost_management.project_id', $request['project_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('e-contract_cost_management.title', 'like', "%{$request['search_param']}%")
                ->orWhere('e-contract_cost_management.payment_reference_number', 'like', '%'.$request['search_param'].'%');
            }            
        })->select('e-contract_cost_management.id','e-contract_cost_management.project_id','e-contract_cost_management.title','e-contract_cost_management.payment_reference_number','e-contract_cost_management.payment_date','e-contract_cost_management.quantity','e-contract_cost_management.amount','e-contract_cost_management.remarks', 'e-contract_cost_management.invoice_status', 'e-contract_cost_management_attachments.file_name','e-contract_cost_management_attachments.file_url','e-contract_cost_management.created_at','e-contract_cost_management.invoice_number',\DB::raw('IF(invoice_items_temp.id is NULL, NULL, 1) as expense_flag'))
        ->distinct()
        ->orderBy('e-contract_cost_management.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * delete the data.
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {   
        $eContractCostManagement = $this->eContractCostManagement::find($request['id']);

        if(is_null($eContractCostManagement)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $eContractCostManagement->eContractCostManagementAttachments()->delete();
        $eContractCostManagement->delete();
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
        $data = $this->eContractCostManagementAttachments::find($request['id']); 
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

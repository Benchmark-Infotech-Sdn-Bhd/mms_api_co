<?php

namespace App\Services;

use App\Models\EContractExpenses;
use App\Models\EContractExpensesAttachments;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class EContractExpensesServices
{
    private EContractExpenses $eContractExpenses;
    private EContractExpensesAttachments $eContractExpensesAttachments;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * EContractExpensesServices constructor.
     * @param EContractExpenses $eContractExpenses
     * @param EContractExpensesAttachments $eContractExpensesAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            EContractExpenses                 $eContractExpenses,
            EContractExpensesAttachments      $eContractExpensesAttachments,
            ValidationServices                $validationServices,
            AuthServices                      $authServices,
            Storage                           $storage
    )
    {
        $this->eContractExpenses = $eContractExpenses;
        $this->eContractExpensesAttachments = $eContractExpensesAttachments;
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
        $expense = $this->eContractExpenses->create([
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
                $filePath = '/eContract/expense/'.$expense['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractExpensesAttachments::create([
                        "file_id" => $expense['id'],
                        "file_name" => $fileName,
                        "file_type" => 'EXPENSE',
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

        $expense = $this->eContractExpenses->findOrFail($request['id']);
        $expense->title = $request['title'] ?? $expense->title;
        $expense->payment_reference_number = $request['payment_reference_number'] ?? $expense->payment_reference_number;
        $expense->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $costManagement->payment_date);
        $expense->amount = $request['amount'] ?? $expense->amount;
        $expense->quantity = $request['quantity'] ?? $expense->quantity;
        $expense->remarks = $request['remarks'] ?? $expense->remarks;
        $expense->created_by = $request['created_by'] ?? $expense->created_by;
        $expense->modified_by = $params['modified_by'];
        $expense->save();

        if (request()->hasFile('attachment')){

            $this->eContractExpensesAttachments->where('file_id', $request['id'])->where('file_type', 'EXPENSE')->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContract/expense/'.$request['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractExpensesAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'EXPENSE',
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
        return $this->eContractExpenses->with('eContractExpensesAttachments')->findOrFail($request['id']);
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
        return $this->eContractExpenses
        ->leftJoin('e-contract_expenses_attachments', function($join) use ($request){
            $join->on('e-contract_expenses.id', '=', 'e-contract_expenses_attachments.file_id')
            ->whereNull('e-contract_expenses_attachments.deleted_at');
          })
        ->where('e-contract_expenses.project_id', $request['project_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('e-contract_expenses.title', 'like', "%{$request['search_param']}%")
                ->orWhere('e-contract_expenses.payment_reference_number', 'like', '%'.$request['search_param'].'%');
            }            
        })->select('e-contract_expenses.id','e-contract_expenses.project_id','e-contract_expenses.title','e-contract_expenses.payment_reference_number','e-contract_expenses.payment_date','e-contract_expenses.quantity','e-contract_expenses.amount','e-contract_expenses.remarks', 'e-contract_expenses.invoice_status', 'e-contract_expenses_attachments.file_name','e-contract_expenses_attachments.file_url','e-contract_expenses.created_at')
        ->distinct()
        ->orderBy('e-contract_expenses.id','DESC')
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
        $eContractExpenses = $this->eContractExpenses::find($request['id']);

        if(is_null($eContractExpenses)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $eContractExpenses->eContractExpensesAttachments()->delete();
        $eContractExpenses->delete();
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
        $data = $this->eContractExpensesAttachments::find($request['id']); 
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

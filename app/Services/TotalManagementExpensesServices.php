<?php

namespace App\Services;

use App\Models\TotalManagementExpenses;
use App\Models\TotalManagementExpensesAttachments;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class TotalManagementExpensesServices
{
    private TotalManagementExpenses $totalManagementExpenses;
    private TotalManagementExpensesAttachments $totalManagementExpensesAttachments;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * WorkersServices constructor.
     * @param TotalManagementExpenses $totalManagementExpenses
     * @param TotalManagementExpensesAttachments $totalManagementExpensesAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            TotalManagementExpenses                 $totalManagementExpenses,
            TotalManagementExpensesAttachments      $totalManagementExpensesAttachments,
            ValidationServices                      $validationServices,
            AuthServices                            $authServices,
            Storage                                 $storage
    )
    {
        $this->totalManagementExpenses = $totalManagementExpenses;
        $this->totalManagementExpensesAttachments = $totalManagementExpensesAttachments;
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
        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementExpenses->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        $expenses = $this->totalManagementExpenses->create([
            'application_id' => $request['application_id'],
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
                $filePath = '/drexpenses/'.$expenses['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->totalManagementExpensesAttachments::create([
                        "file_id" => $expenses['id'],
                        "file_name" => $fileName,
                        "file_type" => 'EXPENSES',
                        "file_url" =>  $fileUrl
                    ]);  
            }
        }

        return $expenses;
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

        if(!($this->validationServices->validate($request->toArray(),$this->totalManagementExpenses->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $expenses = $this->totalManagementExpenses->findOrFail($request['id']);
        $expenses->application_id = $request['application_id'] ?? $expenses->application_id;
        $expenses->title = $request['title'] ?? $expenses->title;
        $expenses->payment_reference_number = $request['payment_reference_number'] ?? $expenses->payment_reference_number;
        $expenses->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $expenses->payment_date);
        $expenses->amount = $request['amount'] ?? $expenses->amount;
        $expenses->quantity = $request['quantity'] ?? $expenses->quantity;
        $expenses->remarks = $request['remarks'] ?? $expenses->remarks;
        $expenses->created_by = $request['created_by'] ?? $expenses->created_by;
        $expenses->modified_by = $params['modified_by'];
        $expenses->save();

        if (request()->hasFile('attachment')){

            $this->totalManagementExpensesAttachments->where('file_id', $request['id'])->where('file_type', 'EXPENSES')->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/drexpenses/'.$request['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->totalManagementExpensesAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'EXPENSES',
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
        return $this->totalManagementExpenses->with('totalManagementExpensesAttachments')->findOrFail($request['id']);
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
        return $this->totalManagementExpenses
        ->leftJoin('total_management_expenses_attachments', 'total_management_expenses.id', '=', 'total_management_expenses_attachments.file_id')
        ->where('total_management_expenses.application_id', $request['application_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('total_management_expenses.title', 'like', "%{$request['search_param']}%")
                ->orWhere('total_management_expenses.payment_reference_number', 'like', '%'.$request['search_param'].'%');
            }
            
        })->select('total_management_expenses.id','total_management_expenses.application_id','total_management_expenses.title','total_management_expenses.payment_reference_number','total_management_expenses.payment_date','total_management_expenses.quantity','total_management_expenses.amount','total_management_expenses.remarks','total_management_expenses_attachments.file_name','total_management_expenses_attachments.file_url','total_management_expenses.created_at')
        ->distinct()
        ->orderBy('total_management_expenses.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

}

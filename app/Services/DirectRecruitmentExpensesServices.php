<?php

namespace App\Services;

use App\Models\DirectRecruitmentExpenses;
use App\Models\DirectRecruitmentExpensesAttachments;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

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
     * DirectRecruitmentExpensesServices constructor.
     * @param DirectRecruitmentExpenses $directRecruitmentExpenses
     * @param DirectRecruitmentExpensesAttachments $directRecruitmentExpensesAttachments
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     */
    public function __construct(
            DirectRecruitmentExpenses               $directRecruitmentExpenses,
            DirectRecruitmentExpensesAttachments    $directRecruitmentExpensesAttachments,
            ValidationServices                      $validationServices,
            AuthServices                            $authServices,
            Storage                                 $storage
    )
    {
        $this->directRecruitmentExpenses = $directRecruitmentExpenses;
        $this->directRecruitmentExpensesAttachments = $directRecruitmentExpensesAttachments;
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
        if(!($this->validationServices->validate($request->toArray(),$this->directRecruitmentExpenses->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }
        $expenses = $this->directRecruitmentExpenses->create([
            'application_id' => $request['application_id'],
            'title' => $request['title'] ?? '',
            'payment_reference_number' => $request['payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : null),
            'amount' => $request['amount'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/expenses/'.$expenses['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directRecruitmentExpensesAttachments::create([
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

        if(!($this->validationServices->validate($request->toArray(),$this->directRecruitmentExpenses->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $expenses = $this->directRecruitmentExpenses->findOrFail($request['id']);
        $expenses->application_id = $request['application_id'] ?? $expenses->application_id;
        $expenses->title = $request['title'] ?? $expenses->title;
        $expenses->payment_reference_number = $request['payment_reference_number'] ?? $expenses->payment_reference_number;
        $expenses->payment_date = ((isset($request['payment_date']) && !empty($request['payment_date'])) ? $request['payment_date'] : $expenses->payment_date);
        $expenses->amount = $request['amount'] ?? $expenses->amount;
        $expenses->remarks = $request['remarks'] ?? $expenses->remarks;
        $expenses->created_by = $request['created_by'] ?? $expenses->created_by;
        $expenses->modified_by = $params['modified_by'];
        $expenses->save();

        if (request()->hasFile('attachment')){

            $this->directRecruitmentExpensesAttachments->where('file_id', $request['id'])->where('file_type', 'EXPENSES')->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/expenses/'.$request['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directRecruitmentExpensesAttachments::create([
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
        return $this->directRecruitmentExpenses->with('directRecruitmentExpensesAttachments')->findOrFail($request['id']);
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
        return $this->directRecruitmentExpenses
        ->leftJoin('directrecruitment_expenses_attachments', 'directrecruitment_expenses.id', '=', 'directrecruitment_expenses_attachments.file_id')
        ->where('directrecruitment_expenses.application_id', $request['application_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('directrecruitment_expenses.title', 'like', "%{$request['search_param']}%")
                ->orWhere('directrecruitment_expenses.payment_reference_number', 'like', '%'.$request['search_param'].'%');
            }
            
        })->select('directrecruitment_expenses.id','directrecruitment_expenses.application_id','directrecruitment_expenses.title','directrecruitment_expenses.payment_reference_number','directrecruitment_expenses.payment_date','directrecruitment_expenses.amount','directrecruitment_expenses.remarks','directrecruitment_expenses_attachments.file_name','directrecruitment_expenses_attachments.file_url','directrecruitment_expenses.created_at','directrecruitment_expenses.invoice_number')
        ->distinct()
        ->orderBy('directrecruitment_expenses.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

     /**
     * @param $request
     * @return bool|array
     */
    public function addOtherExpenses($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $expenses = $this->directRecruitmentExpenses->create([
            'application_id' => $request['expenses_application_id'],
            'title' => $request['expenses_title'] ?? '',
            'payment_reference_number' => $request['expenses_payment_reference_number'] ?? '',
            'payment_date' => ((isset($request['expenses_payment_date']) && !empty($request['expenses_payment_date'])) ? $request['expenses_payment_date'] : null),
            'amount' => $request['expenses_amount'] ?? '',
            'remarks' => $request['expenses_remarks'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);
        return true;
    }

}

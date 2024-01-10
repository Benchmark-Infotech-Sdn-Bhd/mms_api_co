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
     * EContractExpensesServices constructor.
     * @param EContractExpenses $eContractExpenses
     * @param EContractExpensesAttachments $eContractExpensesAttachments
     * @param Storage $storage
     * @param AuthServices $authServices
     * @param EContractApplications $eContractApplications
     */
    public function __construct(EContractExpenses $eContractExpenses, EContractExpensesAttachments $eContractExpensesAttachments, Storage $storage, AuthServices $authServices, EContractApplications $eContractApplications, EContractProject $eContractProject)
    {
        $this->eContractExpenses = $eContractExpenses;
        $this->eContractExpensesAttachments = $eContractExpensesAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->eContractApplications = $eContractApplications;
        $this->eContractProject = $eContractProject;
    }
    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * @return array
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
     * @return array
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
     * @return array
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
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->eContractExpenses
            ->leftJoin('e-contract_expenses_attachments', function($join) use ($request){
                $join->on('e-contract_expenses.id', '=', 'e-contract_expenses_attachments.file_id')
                ->whereNull('e-contract_expenses_attachments.deleted_at');
            })
            ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
            ->join('e-contract_applications', function($query) use($params) {
                $query->on('e-contract_applications.id','=','e-contract_project.application_id')
                ->where('e-contract_applications.company_id', $params['company_id']);
            })
            ->where('e-contract_expenses.worker_id', $request['worker_id'])
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('e-contract_expenses.title', 'like', '%'. $request['search']. '%');
                }
            })
            ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.amount', 'e-contract_expenses.deduction', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount_paid', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses_attachments.file_name','e-contract_expenses_attachments.file_url', 'e-contract_expenses.invoice_number','e-contract_expenses.created_at')
            ->distinct()
            ->orderBy('e-contract_expenses.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->eContractExpenses::with(['eContractExpensesAttachments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($params) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $params['company_id']);
        })
        ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.application_id', 'e-contract_expenses.project_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount', 'e-contract_expenses.amount_paid', 'e-contract_expenses.deduction', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses.created_by', 'e-contract_expenses.modified_by', 'e-contract_expenses.is_payroll', 'e-contract_expenses.payroll_id', 'e-contract_expenses.month', 'e-contract_expenses.year', 'e-contract_expenses.invoice_number', 'e-contract_expenses.created_at', 'e-contract_expenses.updated_at', 'e-contract_expenses.deleted_at')
        ->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $applicationData = $this->eContractApplications->where('e-contract_applications.company_id', $user['company_id'])
        ->select('e-contract_applications.id')
        ->find($request['application_id']);
        
        if(is_null($applicationData)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }
        $projectData = $this->eContractProject
        ->join('e-contract_applications', function($query) use($user) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
        })
        ->select('e-contract_project.application_id')
        ->find($request['project_id']);
        
        if(is_null($projectData)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $expenses = $this->eContractExpenses->create([
            'worker_id' => $params['worker_id'] ?? 0,
            'application_id' => $params['application_id'] ?? 0,
            'project_id' => $params['project_id'] ?? 0,
            'title' => $params['title'] ?? '',
            'type' => $params['type'] ?? '',
            'payment_reference_number' => $params['payment_reference_number'] ?? '',
            'payment_date' => ((isset($params['payment_date']) && !empty($params['payment_date'])) ? $params['payment_date'] : null),
            'amount' => $params['amount'] ?? 0,
            'remarks' => $params['remarks'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0,
        ]);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContractExpenses/'.$expenses['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractExpensesAttachments::create([
                        'file_id' => $expenses->id,
                        'file_name' => $fileName,
                        'file_type' => 'EContract Expenses',
                        'file_url' =>  $fileUrl,
                        'created_by' => $params['created_by'] ?? 0,
                        'modified_by' => $params['created_by'] ?? 0,
                    ]);  
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request) : bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];
        $expense = $this->eContractExpenses::join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($user) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
        })
        ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.application_id', 'e-contract_expenses.project_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount', 'e-contract_expenses.amount_paid', 'e-contract_expenses.deduction', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses.created_by', 'e-contract_expenses.modified_by', 'e-contract_expenses.is_payroll', 'e-contract_expenses.payroll_id', 'e-contract_expenses.month', 'e-contract_expenses.year', 'e-contract_expenses.invoice_number', 'e-contract_expenses.created_at', 'e-contract_expenses.updated_at', 'e-contract_expenses.deleted_at')
        ->find($request['id']);
        if(is_null($expense)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }

        $expense->worker_id = $params['worker_id'] ?? $expense->worker_id;
        $expense->application_id = $params['application_id'] ?? $expense->application_id;
        $expense->project_id = $params['project_id'] ?? $expense->project_id;
        $expense->title = $params['title'] ?? $expense->title;
        $expense->type = $params['type'] ?? $expense->type;
        $expense->payment_reference_number = $params['payment_reference_number'] ?? $expense->payment_reference_number;
        $expense->payment_date = $params['payment_date'] ?? $expense->payment_date;
        $expense->amount = $params['amount'] ?? $expense->amount;
        $expense->remarks = $params['remarks'] ?? $expense->remarks;
        $expense->modified_by = $params['modified_by'] ?? $expense->modified_by;
        $expense->save();

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/eContractExpenses/'.$expense['id']. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->eContractExpensesAttachments::create([
                        'file_id' => $expense->id,
                        'file_name' => $fileName,
                        'file_type' => 'EContract Expenses',
                        'file_url' =>  $fileUrl,
                        'created_by' => $params['modified_by'] ?? 0,
                        'modified_by' => $params['modified_by'] ?? 0,
                    ]);  
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return bool
     */
    public function delete($request) : bool
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
    
        $expense = $this->eContractExpenses::join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($user) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
        })
        ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.application_id', 'e-contract_expenses.project_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount', 'e-contract_expenses.amount_paid', 'e-contract_expenses.deduction', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses.created_by', 'e-contract_expenses.modified_by', 'e-contract_expenses.is_payroll', 'e-contract_expenses.payroll_id', 'e-contract_expenses.month', 'e-contract_expenses.year', 'e-contract_expenses.invoice_number', 'e-contract_expenses.created_at', 'e-contract_expenses.updated_at', 'e-contract_expenses.deleted_at')
        ->find($request['id']);

        if(is_null($expense)){
            return false;
        }

        $expense->delete();
        return true;
    }
    /**
     *
     * @param $request
     * @return bool
     */    
    public function deleteAttachment($request): bool
    {   
        //$data = $this->eContractExpensesAttachments::find($request['id']); 

        $user = JWTAuth::parseToken()->authenticate();
        $params['company_id'] = $this->authServices->getCompanyIds($user);
    
        $data = $this->eContractExpensesAttachments::join('e-contract_expenses', 'e-contract_expenses.id', 'e-contract_expenses_attachments.file_id')
        ->join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($user) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
        })
        ->select('e-contract_expenses_attachments.id', 'e-contract_expenses_attachments.file_id', 'e-contract_expenses_attachments.file_name', 'e-contract_expenses_attachments.file_type', 'e-contract_expenses_attachments.file_url', 'e-contract_expenses_attachments.created_by', 'e-contract_expenses_attachments.modified_by', 'e-contract_expenses_attachments.created_at', 'e-contract_expenses_attachments.updated_at', 'e-contract_expenses_attachments.deleted_at')
        ->find($request['id']);

        if(is_null($data)){
            return false;
        }

        $data->delete();
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function payBack($request) : bool|array
    {
        $validator = Validator::make($request, $this->payBackValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        //$expense = $this->eContractExpenses->findOrFail($request['id']);
        $expense = $this->eContractExpenses::join('e-contract_project', 'e-contract_project.id', 'e-contract_expenses.project_id')
        ->join('e-contract_applications', function($query) use($user) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $user['company_id']);
        })
        ->select('e-contract_expenses.id', 'e-contract_expenses.worker_id', 'e-contract_expenses.application_id', 'e-contract_expenses.project_id', 'e-contract_expenses.title', 'e-contract_expenses.type', 'e-contract_expenses.payment_reference_number', 'e-contract_expenses.payment_date', 'e-contract_expenses.amount', 'e-contract_expenses.amount_paid', 'e-contract_expenses.deduction', 'e-contract_expenses.remaining_amount', 'e-contract_expenses.remarks', 'e-contract_expenses.created_by', 'e-contract_expenses.modified_by', 'e-contract_expenses.is_payroll', 'e-contract_expenses.payroll_id', 'e-contract_expenses.month', 'e-contract_expenses.year', 'e-contract_expenses.invoice_number', 'e-contract_expenses.created_at', 'e-contract_expenses.updated_at', 'e-contract_expenses.deleted_at')
        ->find($request['id']);

        if(is_null($expense)){
            return [
                'unauthorizedError' => true
            ];
        }
        $totalPayBack = $expense->deduction + $request['amount_paid'];
        $remainingAmount = $expense->amount - $totalPayBack;
        if($totalPayBack > $expense->amount) {
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

        return true;
    }
}
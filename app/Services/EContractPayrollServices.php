<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Workers;
use App\Models\EContractPayroll;
use App\Models\EContractPayrollAttachments;
use App\Models\EContractPayrollBulkUpload;
use App\Models\EContractExpenses;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EContractPayrollImport;

class EContractPayrollServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var EContractPayroll
     */
    private EContractPayroll $eContractPayroll;
    /**
     * @var EContractPayrollAttachments
     */
    private EContractPayrollAttachments $eContractPayrollAttachments;
    /**
     * @var EContractPayrollBulkUpload
     */
    private EContractPayrollBulkUpload $eContractPayrollBulkUpload;
    /**
     * @var EContractExpenses
     */
    private EContractExpenses $eContractExpenses;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * EContractPayrollServices constructor.
     * @param EContractProject $eContractProject
     * @param EContractPayroll $eContractPayroll
     * @param EContractPayrollAttachments $eContractPayrollAttachments
     * @param EContractPayrollBulkUpload $eContractPayrollBulkUpload
     * @param EContractExpenses $eContractExpenses
     * @param Storage $storage;
     */
    public function __construct(Workers $workers, EContractPayroll $eContractPayroll, EContractPayrollAttachments $eContractPayrollAttachments, Storage $storage, EContractPayrollBulkUpload $eContractPayrollBulkUpload, EContractExpenses $eContractExpenses)
    {
        $this->workers = $workers;
        $this->eContractPayroll = $eContractPayroll;
        $this->eContractPayrollAttachments = $eContractPayrollAttachments;
        $this->storage = $storage;
        $this->eContractPayrollBulkUpload = $eContractPayrollBulkUpload;
        $this->eContractExpenses = $eContractExpenses;
    }
    /**
     * @return array
     */
    public function addValidation(): array
    {
        return [
            'worker_id' => 'required',
            'project_id' => 'required',
            'month' => 'required',
            'year' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function uploadTimesheetValidation(): array
    {
        return [
            'project_id' => 'required',
            'month' => 'required',
            'year' => 'required'
        ];
    }
        /**
     * @return array
     */
    public function importValidation(): array
    {
        return [
            'project_id' => 'required'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function projectDetails($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_employment', 'worker_employment.worker_id','=','workers.id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', '=', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id']) 
            ->where('worker_employment.service_type', 'e-Contract')
            ->select(DB::raw('COUNT(DISTINCT workers.id) as workers'), 'worker_employment.project_id', 'e-contract_project.name')
            ->groupBy('worker_employment.project_id', 'e-contract_project.name')
            ->distinct('workers.id')
            ->get();
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('e-contract_payroll', 'e-contract_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id'])   
            ->where('worker_employment.service_type', 'e-Contract')    
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
                if (isset($request['month']) && !empty($request['month'])) {
                    $query->where('e-contract_payroll.month', $request['month']);
                }
                if (isset($request['year']) && !empty($request['year'])) {
                    $query->where('e-contract_payroll.year', $request['year']);
                }
            })
            ->select('workers.id', 'workers.name', 'workers.passport_number', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'worker_employment.department', 'e-contract_payroll.id as payroll_id', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution')
            ->distinct('workers.id')
            ->orderBy('workers.created_at','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function export($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('e-contract_payroll', 'e-contract_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id']) 
            ->where('worker_employment.service_type', 'e-Contract')       
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
                if (isset($request['month']) && !empty($request['month'])) {
                    $query->where('e-contract_payroll.month', $request['month']);
                }
                if (isset($request['year']) && !empty($request['year'])) {
                    $query->where('e-contract_payroll.year', $request['year']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_bank_details.bank_name', 'workers.passport_number', 'worker_employment.department', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution')
            ->distinct('workers.id')
            ->orderBy('workers.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('e-contract_payroll', 'e-contract_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('e-contract_payroll.id', $request['id'])       
            ->select('workers.id', 'workers.name', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'workers.passport_number', 'worker_employment.department', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution')
            ->distinct('workers.id')->get();
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function import($request, $file): mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->importValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        
        $eContractPayrollBulkUpload = $this->eContractPayrollBulkUpload->create([
                'project_id' => $request['project_id'] ?? '',
                'name' => 'Payroll Bulk Upload',
                'type' => 'Payroll bulk upload'
            ]
        );

        Excel::import(new EContractPayrollImport($params, $eContractPayrollBulkUpload), $file);
        return true;
    } 
    /**
     * @param $request
     * @return bool|array
     */   
    public function add($request): bool|array
    {
        $validator = Validator::make($request, $this->addValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractPayroll = $this->eContractPayroll->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['project_id']],
            ['month', $request['month']],
            ['year', $request['year']],
        ])->first(['id', 'worker_id', 'project_id', 'month', 'year']);

        if(is_null($eContractPayroll)){
            $this->eContractPayroll->create([
                'worker_id' => $request['worker_id'] ?? 0,
                'project_id' => $request['project_id'] ?? 0,
                'month' => $request['month'] ?? 0,
                'year' => $request['year'] ?? 0,
                'basic_salary' => $request['basic_salary'] ?? 0,
                'ot_1_5' => $request['ot_1_5'] ?? 0,
                'ot_2_0' => $request['ot_2_0'] ?? 0,
                'ot_3_0' => $request['ot_3_0'] ?? 0,
                'ph' => $request['ph'] ?? 0,
                'rest_day' => $request['rest_day'] ?? 0,
                'deduction_advance' => $request['deduction_advance'] ?? 0,
                'deduction_accommodation' => $request['deduction_accommodation'] ?? 0,
                'annual_leave' => $request['annual_leave'] ?? 0,
                'medical_leave' => $request['medical_leave'] ?? 0,
                'hospitalisation_leave' => $request['hospitalisation_leave'] ?? 0,
                'amount' => $request['amount'] ?? 0,
                'no_of_workingdays' => $request['no_of_workingdays'] ?? 0,
                'normalday_ot_1_5' => $request['normalday_ot_1_5'] ?? 0,
                'ot_1_5_hrs_amount' => $request['ot_1_5_hrs_amount'] ?? 0,
                'restday_daily_salary_rate' => $request['restday_daily_salary_rate'] ?? 0,
                'hrs_ot_2_0' => $request['hrs_ot_2_0'] ?? 0,
                'ot_2_0_hrs_amount' => $request['ot_2_0_hrs_amount'] ?? 0,
                'public_holiday_ot_3_0' => $request['public_holiday_ot_3_0'] ?? 0,
                'deduction_hostel' => $request['deduction_hostel'] ?? 0,
                'sosco_deduction' => $request['sosco_deduction'] ?? 0,
                'sosco_contribution' => $request['sosco_contribution'] ?? 0,
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0
            ]);
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractPayroll = $this->eContractPayroll->findOrFail($request['id']);

        $eContractPayroll->basic_salary =  $request['basic_salary'] ?? $eContractPayroll->basic_salary;
        $eContractPayroll->ot_1_5 =  $request['ot_1_5'] ?? $eContractPayroll->ot_1_5;
        $eContractPayroll->ot_2_0 =  $request['ot_2_0'] ?? $eContractPayroll->ot_2_0;
        $eContractPayroll->ot_3_0 =  $request['ot_3_0'] ?? $eContractPayroll->ot_3_0;
        $eContractPayroll->ph =  $request['ph'] ?? $eContractPayroll->ph;
        $eContractPayroll->rest_day =  $request['rest_day'] ?? $eContractPayroll->rest_day;
        $eContractPayroll->deduction_advance =  $request['deduction_advance'] ?? $eContractPayroll->deduction_advance;
        $eContractPayroll->deduction_accommodation =  $request['deduction_accommodation'] ?? $eContractPayroll->deduction_accommodation;
        $eContractPayroll->annual_leave =  $request['annual_leave'] ?? $eContractPayroll->annual_leave;
        $eContractPayroll->medical_leave =  $request['medical_leave'] ?? $eContractPayroll->medical_leave;
        $eContractPayroll->hospitalisation_leave =  $request['hospitalisation_leave'] ?? $eContractPayroll->hospitalisation_leave;
        $eContractPayroll->amount =  $request['amount'] ?? $eContractPayroll->amount;
        $eContractPayroll->no_of_workingdays =  $request['no_of_workingdays'] ?? $eContractPayroll->no_of_workingdays;
        $eContractPayroll->normalday_ot_1_5 =  $request['normalday_ot_1_5'] ?? $eContractPayroll->normalday_ot_1_5;
        $eContractPayroll->ot_1_5_hrs_amount =  $request['ot_1_5_hrs_amount'] ?? $eContractPayroll->ot_1_5_hrs_amount;
        $eContractPayroll->restday_daily_salary_rate =  $request['restday_daily_salary_rate'] ?? $eContractPayroll->restday_daily_salary_rate;
        $eContractPayroll->hrs_ot_2_0 =  $request['hrs_ot_2_0'] ?? $eContractPayroll->hrs_ot_2_0;
        $eContractPayroll->ot_2_0_hrs_amount =  $request['ot_2_0_hrs_amount'] ?? $eContractPayroll->ot_2_0_hrs_amount;
        $eContractPayroll->public_holiday_ot_3_0 =  $request['public_holiday_ot_3_0'] ?? $eContractPayroll->public_holiday_ot_3_0;
        $eContractPayroll->deduction_hostel =  $request['deduction_hostel'] ?? $eContractPayroll->deduction_hostel;
        $eContractPayroll->sosco_deduction =  $request['sosco_deduction'] ?? $eContractPayroll->sosco_deduction;
        $eContractPayroll->sosco_contribution =  $request['sosco_contribution'] ?? $eContractPayroll->sosco_contribution;
        $eContractPayroll->modified_by =  $request['modified_by'] ?? $eContractPayroll->modified_by;
        $eContractPayroll->save();

        return true;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function listTimesheet($request): mixed
    {
        return $this->eContractPayrollAttachments
            ->where('file_id', $request['project_id'])
            ->select('id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at')
            ->distinct('id')
            ->orderBy('id','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function viewTimesheet($request): mixed
    {
        return $this->eContractPayrollAttachments
        ->where([
            'file_id' => $request['project_id'],
            'month' => $request['month'],
            'year' => $request['year']
        ])
        ->select('id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at')
        ->get();
    }
    /**
     * upload Timesheet
     * @param $request
     * @return bool|array
     */
    public function uploadTimesheet($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->uploadTimesheetValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractPayrollAttachments = $this->eContractPayrollAttachments
        ->where([
            'file_id' => $request['project_id'],
            'month' => $request['month'],
            'year' => $request['year']
        ])->first(['id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at']);
        
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                
                if(is_null($eContractPayrollAttachments)){

                    $fileName = $file->getClientOriginalName();
                    $filePath = '/eContract/payroll/timesheet/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);

                    $this->eContractPayrollAttachments::create([
                        'file_id' => $request['project_id'],
                        "month" => $request['month'] ?? 0,
                        "year" => $request['year'] ?? 0,
                        "file_name" => $fileName,
                        "file_type" => 'Timesheet',
                        "file_url" =>  $fileUrl,
                        "created_by" =>  $params['created_by'] ?? 0,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]);

                    return true;
                    
                }else{
                    return [
                        'existsError' => true
                    ];
                }
            }
        }
        return false;
    }

    /**
     * upload Timesheet
     * @param $request
     * @return bool|array
     */
    public function authorizePayroll($request): bool|array
    {

        $checkEContractExpenses = $this->eContractExpenses->where('project_id',$request['project_id'])->where('month',$request['month'])->where('year',$request['year'])->count();

        if($checkEContractExpenses > 0) {
            return [
                'existsError' => true
            ];
        }

        $payrollWorkers = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id');
        })
        ->leftJoin('e-contract_payroll', function($query) use ($request) {
            $query->on('e-contract_payroll.worker_id','=','workers.id');
            if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
            $query->whereRaw('e-contract_payroll.id IN (select MAX(TMPAY.id) from e-contract_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
            }
        })
        ->leftJoin('e-contract_project', function($query) {
            $query->on('e-contract_payroll.project_id','=','e-contract_project.id');
        })
        ->where(function ($query) use ($request) {
            if (isset($request['month']) && !empty($request['month'])) {
                $query->where('e-contract_payroll.month', $request['month']);
            }
            if (isset($request['year']) && !empty($request['year'])) {
                $query->where('e-contract_payroll.year', $request['year']);
            }
            if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                $query->whereNull('worker_employment.work_end_date');
                $query->whereNull('worker_employment.remove_date');
            }
        })
        ->select('workers.id as worker_id', 'workers.name', 'e-contract_payroll.id as payroll_id', 'e-contract_payroll.amount', 'e-contract_payroll.sosco_contribution', 'e-contract_project.application_id')
        ->distinct('workers.id')
        ->orderBy('workers.created_at','DESC')->get();

        if(isset($payrollWorkers) && count($payrollWorkers) > 0){
            foreach($payrollWorkers as $result){
                $user = JWTAuth::parseToken()->authenticate();
                $this->eContractExpenses->create([
                    'worker_id' => $result['worker_id'],
                    'application_id' => $result['application_id'],
                    'project_id' => $request['project_id'],
                    'title' => $result['name'],
                    'type' => 'Payroll',
                    'payment_reference_number' => 1,
                    'payment_date' => Carbon::now(),
                    'is_payroll' => 1,
                    'amount' => $result['amount'],
                    'remarks' => $result['name'],
                    'is_payroll' => 1,
                    'payroll_id' => $result['payroll_id'],
                    'month' => $request['month'],
                    'year' => $request['year'],
                    'created_by'    => $user['worker_id'] ?? 0,
                    'modified_by'   => $user['worker_id'] ?? 0,
                ]);
                $this->eContractExpenses->create([
                    'worker_id' => $result['worker_id'],
                    'application_id' => $result['application_id'],
                    'project_id' => $request['project_id'] ?? 0,
                    'title' => "SOCSO Contribution (" . $result['name'] . " )",
                    'type' => 'Payroll',
                    'payment_reference_number' => 1,
                    'payment_date' => Carbon::now(),
                    'is_payroll' => 1,
                    'amount' => $result['amount'],
                    'remarks' => "SOCSO Contribution (" . $result['name'] . " )",
                    'is_payroll' => 1,
                    'payroll_id' => $result['payroll_id'],
                    'month' => $request['month'],
                    'year' => $request['year'],
                    'created_by'    => $user['worker_id'] ?? 0,
                    'modified_by'   => $user['worker_id'] ?? 0,
                ]);
            }
        } else {
            return [
                'noRecords' => true
            ];
        }
        return true;
    }

}
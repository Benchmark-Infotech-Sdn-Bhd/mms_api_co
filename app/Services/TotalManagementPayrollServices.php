<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Workers;
use App\Models\TotalManagementPayroll;
use App\Models\TotalManagementPayrollAttachments;
use App\Models\PayrollBulkUpload;
use App\Models\TotalManagementProject;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PayrollImport;
use App\Models\TotalManagementCostManagement;

class TotalManagementPayrollServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var TotalManagementPayroll
     */
    private TotalManagementPayroll $totalManagementPayroll;
    /**
     * @var TotalManagementPayrollAttachments
     */
    private TotalManagementPayrollAttachments $totalManagementPayrollAttachments;
    /**
     * @var PayrollBulkUpload
     */
    private PayrollBulkUpload $payrollBulkUpload;
    /**
     * @var TotalManagementCostManagement
     */
    private TotalManagementCostManagement $totalManagementCostManagement;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementPayrollServices constructor.
     * @param TotalManagementProject $totalManagementProject
     * @param TotalManagementPayroll $totalManagementPayroll
     * @param TotalManagementPayrollAttachments $totalManagementPayrollAttachments
     * @param PayrollBulkUpload $payrollBulkUpload
     * @param TotalManagementCostManagement $totalManagementCostManagement
     * @param Storage $storage;
     */

    public function __construct(Workers $workers, TotalManagementPayroll $totalManagementPayroll, TotalManagementPayrollAttachments $totalManagementPayrollAttachments, Storage $storage, PayrollBulkUpload $payrollBulkUpload, TotalManagementProject $totalManagementProject, TotalManagementCostManagement $totalManagementCostManagement)
    {
        $this->workers = $workers;
        $this->totalManagementPayroll = $totalManagementPayroll;
        $this->totalManagementPayrollAttachments = $totalManagementPayrollAttachments;
        $this->storage = $storage;
        $this->payrollBulkUpload = $payrollBulkUpload;
        $this->totalManagementProject = $totalManagementProject;
        $this->totalManagementCostManagement = $totalManagementCostManagement;
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
            return $this->totalManagementProject
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        })
        ->where('total_management_project.id',$request['project_id'])
        ->select(DB::raw('COUNT(DISTINCT workers.id) as workers'), 'worker_employment.project_id', 'total_management_project.name')
            ->groupBy('worker_employment.project_id', 'total_management_project.name')
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
            ->leftJoin('total_management_payroll', function($query) use ($request) {
                $query->on('total_management_payroll.worker_id','=','worker_employment.worker_id');
                if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
                }
            })
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id']) 
            ->where('worker_employment.service_type', 'Total Management')      
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
                if (isset($request['month']) && !empty($request['month'])) {
                    $query->where('total_management_payroll.month', $request['month']);
                }
                if (isset($request['year']) && !empty($request['year'])) {
                    $query->where('total_management_payroll.year', $request['year']);
                }
                if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                    $query->whereNull('worker_employment.work_end_date');
                    $query->whereNull('worker_employment.remove_date');
                    $query->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
                }
            })
            ->select('workers.id', 'workers.name', 'workers.passport_number', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'worker_employment.department', 'total_management_payroll.id as payroll_id', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution')
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
            ->leftJoin('total_management_payroll', function($query) use ($request) {
                $query->on('total_management_payroll.worker_id','=','worker_employment.worker_id');
                if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
                }
            })
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $request['company_id']);
            })
            ->where('worker_employment.project_id', $request['project_id']) 
            ->where('worker_employment.service_type', 'Total Management')       
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
                if (isset($request['month']) && !empty($request['month'])) {
                    $query->where('total_management_payroll.month', $request['month']);
                }
                if (isset($request['year']) && !empty($request['year'])) {
                    $query->where('total_management_payroll.year', $request['year']);
                }
                if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                    $query->whereNull('worker_employment.work_end_date');
                    $query->whereNull('worker_employment.remove_date');
                    $query->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
                }
            })
            ->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'workers.passport_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution')
            ->distinct('workers.id')
            ->orderBy('workers.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->totalManagementPayroll
            ->leftJoin('total_management_project', 'total_management_project.id', 'total_management_payroll.project_id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','total_management_payroll.worker_id')
                    ->on('worker_employment.project_id','=','total_management_payroll.project_id')
                    ->where('worker_employment.service_type', 'Total Management') ;
            })
            ->leftJoin('workers', 'workers.id', 'total_management_payroll.worker_id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                     ->whereIn('total_management_applications.company_id', $request['company_id']);
            })
            ->where('total_management_payroll.id', $request['id'])       
            ->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'workers.passport_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution')
            ->distinct('workers.id','total_management_payroll.id')->get();
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
        
        $payrollBulkUpload = $this->payrollBulkUpload->create([
                'project_id' => $request['project_id'] ?? '',
                'name' => 'Payroll Bulk Upload',
                'type' => 'Payroll bulk upload'
            ]
        );

        Excel::import(new PayrollImport($params, $payrollBulkUpload), $file);
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

        $totalManagementPayroll = $this->totalManagementPayroll->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['project_id']],
            ['month', $request['month']],
            ['year', $request['year']],
        ])->first(['id', 'worker_id', 'project_id', 'month', 'year']);

        if(is_null($totalManagementPayroll)){
            $this->totalManagementPayroll->create([
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

        $totalManagementPayroll = $this->totalManagementPayroll
        ->join('total_management_project', 'total_management_project.id', 'total_management_payroll.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                 ->whereIn('total_management_applications.company_id', $request['company_id']);
        })->select('total_management_payroll.*')->find($request['id']);
        if(is_null($totalManagementPayroll)){
            return [
                'unauthorizedError' => true
            ];
        }

        $totalManagementPayroll->basic_salary =  $request['basic_salary'] ?? $totalManagementPayroll->basic_salary;
        $totalManagementPayroll->ot_1_5 =  $request['ot_1_5'] ?? $totalManagementPayroll->ot_1_5;
        $totalManagementPayroll->ot_2_0 =  $request['ot_2_0'] ?? $totalManagementPayroll->ot_2_0;
        $totalManagementPayroll->ot_3_0 =  $request['ot_3_0'] ?? $totalManagementPayroll->ot_3_0;
        $totalManagementPayroll->ph =  $request['ph'] ?? $totalManagementPayroll->ph;
        $totalManagementPayroll->rest_day =  $request['rest_day'] ?? $totalManagementPayroll->rest_day;
        $totalManagementPayroll->deduction_advance =  $request['deduction_advance'] ?? $totalManagementPayroll->deduction_advance;
        $totalManagementPayroll->deduction_accommodation =  $request['deduction_accommodation'] ?? $totalManagementPayroll->deduction_accommodation;
        $totalManagementPayroll->annual_leave =  $request['annual_leave'] ?? $totalManagementPayroll->annual_leave;
        $totalManagementPayroll->medical_leave =  $request['medical_leave'] ?? $totalManagementPayroll->medical_leave;
        $totalManagementPayroll->hospitalisation_leave =  $request['hospitalisation_leave'] ?? $totalManagementPayroll->hospitalisation_leave;
        $totalManagementPayroll->amount =  $request['amount'] ?? $totalManagementPayroll->amount;
        $totalManagementPayroll->no_of_workingdays =  $request['no_of_workingdays'] ?? $totalManagementPayroll->no_of_workingdays;
        $totalManagementPayroll->normalday_ot_1_5 =  $request['normalday_ot_1_5'] ?? $totalManagementPayroll->normalday_ot_1_5;
        $totalManagementPayroll->ot_1_5_hrs_amount =  $request['ot_1_5_hrs_amount'] ?? $totalManagementPayroll->ot_1_5_hrs_amount;
        $totalManagementPayroll->restday_daily_salary_rate =  $request['restday_daily_salary_rate'] ?? $totalManagementPayroll->restday_daily_salary_rate;
        $totalManagementPayroll->hrs_ot_2_0 =  $request['hrs_ot_2_0'] ?? $totalManagementPayroll->hrs_ot_2_0;
        $totalManagementPayroll->ot_2_0_hrs_amount =  $request['ot_2_0_hrs_amount'] ?? $totalManagementPayroll->ot_2_0_hrs_amount;
        $totalManagementPayroll->public_holiday_ot_3_0 =  $request['public_holiday_ot_3_0'] ?? $totalManagementPayroll->public_holiday_ot_3_0;
        $totalManagementPayroll->deduction_hostel =  $request['deduction_hostel'] ?? $totalManagementPayroll->deduction_hostel;
        $totalManagementPayroll->sosco_deduction =  $request['sosco_deduction'] ?? $totalManagementPayroll->sosco_deduction;
        $totalManagementPayroll->sosco_contribution =  $request['sosco_contribution'] ?? $totalManagementPayroll->sosco_contribution;
        $totalManagementPayroll->modified_by =  $request['modified_by'] ?? $totalManagementPayroll->modified_by;
        $totalManagementPayroll->save();

        return true;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function listTimesheet($request): mixed
    {
        return $this->totalManagementPayrollAttachments
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
        return $this->totalManagementPayrollAttachments
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

        $totalManagementPayrollAttachments = $this->totalManagementPayrollAttachments
        ->where([
            'file_id' => $request['project_id'],
            'month' => $request['month'],
            'year' => $request['year']
        ])->first(['id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at']);
        
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                
                if(is_null($totalManagementPayrollAttachments)){

                    $fileName = $file->getClientOriginalName();
                    $filePath = '/totalManagement/payroll/timesheet/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);

                    $this->totalManagementPayrollAttachments::create([
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

        $checkTotalManagementCostManagement = $this->totalManagementCostManagement
        ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        })->where('project_id',$request['project_id'])->where('month',$request['month'])->where('year',$request['year'])->count();
        
        if($checkTotalManagementCostManagement > 0) {
            return [
                'existsError' => true
            ];
        }

        $payrollWorkers = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id');
        })
        ->leftJoin('total_management_payroll', function($query) use ($request) {
            $query->on('total_management_payroll.worker_id','=','workers.id');
            if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
            $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
            }
        })
        ->leftJoin('total_management_project', function($query) {
            $query->on('total_management_payroll.project_id','=','total_management_project.id');
        })
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        })
        ->where(function ($query) use ($request) {
            if (isset($request['month']) && !empty($request['month'])) {
                $query->where('total_management_payroll.month', $request['month']);
            }
            if (isset($request['year']) && !empty($request['year'])) {
                $query->where('total_management_payroll.year', $request['year']);
            }
            if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
                $query->whereNull('worker_employment.work_end_date');
                $query->whereNull('worker_employment.remove_date');
            }
        })
        ->select('workers.id as worker_id', 'workers.name', 'total_management_payroll.id as payroll_id', 'total_management_payroll.amount', 'total_management_payroll.sosco_contribution', 'total_management_project.application_id')
        ->distinct('workers.id')
        ->orderBy('workers.created_at','DESC')->get();

        if(isset($payrollWorkers) && count($payrollWorkers) > 0 ){
            foreach($payrollWorkers as $result){
                $user = JWTAuth::parseToken()->authenticate();
                $this->totalManagementCostManagement->create([
                    'application_id' => $result['application_id'],
                    'project_id' => $request['project_id'],
                    'title' => $result['name'],
                    'type' => 'Payroll',
                    'payment_reference_number' => 1,
                    'payment_date' => Carbon::now(),
                    'is_payroll' => 1,
                    'quantity' => 1,
                    'amount' => $result['amount'],
                    'remarks' => $result['name'],
                    'is_payroll' => 1,
                    'payroll_id' => $result['payroll_id'],
                    'month' => $request['month'],
                    'year' => $request['year'],
                    'created_by'    => $user['worker_id'] ?? 0,
                    'modified_by'   => $user['worker_id'] ?? 0,
                ]);
                $this->totalManagementCostManagement->create([
                    'application_id' => $result['application_id'],
                    'project_id' => $request['project_id'],
                    'title' => "SOCSO Contribution (" . $result['name'] . " )",
                    'type' => 'Payroll',
                    'payment_reference_number' => 1,
                    'payment_date' => Carbon::now(),
                    'is_payroll' => 1,
                    'quantity' => 1,
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
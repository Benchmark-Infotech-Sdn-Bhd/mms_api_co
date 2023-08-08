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
     * @var Storage
     */
    private Storage $storage;
    /**
     * TotalManagementPayrollServices constructor.
     * @param TotalManagementProject $totalManagementProject
     * @param TotalManagementPayroll $totalManagementPayroll
     * @param TotalManagementPayrollAttachments $totalManagementPayrollAttachments
     * @param Storage $storage;
     */
    public function __construct(Workers $workers, TotalManagementPayroll $totalManagementPayroll, TotalManagementPayrollAttachments $totalManagementPayrollAttachments, Storage $storage)
    {
        $this->workers = $workers;
        $this->totalManagementPayroll = $totalManagementPayroll;
        $this->totalManagementPayrollAttachments = $totalManagementPayrollAttachments;
        $this->storage = $storage;
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
     * @param $request
     * @return mixed
     */   
    public function projectDetails($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_employment', 'worker_employment.worker_id','=','workers.id')
            ->where('worker_employment.project_id', $request['project_id']) 
            ->select(DB::raw('COUNT(workers.id) as workers'), 'worker_employment.project_id')
            ->groupBy('worker_employment.project_id')
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
                $query->on('worker_employment.worker_id','=','workers.id')
                    ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('total_management_payroll', 'total_management_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id'])       
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'workers.passport_number', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel')
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
                $query->on('worker_employment.worker_id','=','workers.id')
                    ->whereRaw('worker_employment.id IN (select MAX(WORKER_EMP.id) from worker_employment as WORKER_EMP JOIN workers as WORKER ON WORKER.id = WORKER_EMP.worker_id group by WORKER.id)');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('total_management_payroll', 'total_management_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id'])       
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'workers.passport_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel')
            ->distinct('workers.id')
            ->orderBy('workers.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->totalManagementPayroll->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function import($request): bool|array
    {
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

        $totalManagementPayroll = $this->totalManagementPayroll->findOrFail($request['id']);

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

}
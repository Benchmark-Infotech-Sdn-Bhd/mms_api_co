<?php

namespace App\Jobs;

use App\Models\Workers;
use App\Models\EContractPayrollUploadRecords;
use App\Models\EContractProject;
use App\Models\WorkerEmployment;
use App\Models\EContractPayroll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class EContractPayrollsImport extends Job
{
    private $payrollParameter;
    private $bulkUpload;

    /**
     * Create a new job instance.
     *
     * @param $payrollParameter
     * @param $bulkUpload
     */
    public function __construct($payrollParameter, $bulkUpload)
    {
        $this->payrollParameter = $payrollParameter;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * Execute the job.
     * @return void
     * @throws \JsonException
     */
    public function handle(): void
    { 
        if( !empty($this->payrollParameter['passport_number']) && !empty($this->payrollParameter['project_id']) && !empty($this->payrollParameter['month']) && !empty($this->payrollParameter['year']) ){

            $worker = DB::table('workers')->where('passport_number', $this->payrollParameter['passport_number'])->first('id');

            if(isset($worker->id) && !empty($worker->id)){
                
                Log::info('worker data ID - ' . print_r($worker->id, true));

                // CHECK WORKER EMPLOYMENT DATA
                $workerEmployment = WorkerEmployment::where([
                    ['worker_id', $worker->id],
                    ['project_id', $this->payrollParameter['project_id']],
                    ['service_type', 'e-Contract']
                ])
                ->whereNull('work_end_date')
                ->count();

                Log::info('worker Employment count - ' . print_r($workerEmployment, true));

                if($workerEmployment > 0){

                    Log::info('eContract payroll add - started ');

                    $eContractPayroll = EContractPayroll::updateOrCreate(
                        [
                            'worker_id' => $worker->id,
                            'project_id' => $this->payrollParameter['project_id'],
                            'month' => $this->payrollParameter['month'],
                            'year'=> $this->payrollParameter['year']
                        ],
                        [
                            'basic_salary' => $this->payrollParameter['basic_salary'] ?? 0,
                            'ot_1_5' => $this->payrollParameter['ot_1_5'] ?? 0,
                            'ot_2_0' => $this->payrollParameter['ot_2_0'] ?? 0,
                            'ot_3_0' => $this->payrollParameter['ot_3_0'] ?? 0,
                            'ph' => $this->payrollParameter['ph'] ?? 0,
                            'rest_day' => $this->payrollParameter['rest_day'] ?? 0,
                            'deduction_advance' => $this->payrollParameter['deduction_advance'] ?? 0,
                            'deduction_accommodation' => $this->payrollParameter['deduction_accommodation'] ?? 0,
                            'annual_leave' => $this->payrollParameter['annual_leave'] ?? 0,
                            'medical_leave' => $this->payrollParameter['medical_leave'] ?? 0,
                            'hospitalisation_leave' => $this->payrollParameter['hospitalisation_leave'] ?? 0,
                            'amount' => $this->payrollParameter['amount'] ?? 0,
                            'no_of_workingdays' => $this->payrollParameter['no_of_workingdays'] ?? 0,
                            'normalday_ot_1_5' => $this->payrollParameter['normalday_ot_1_5'] ?? 0,
                            'ot_1_5_hrs_amount' => $this->payrollParameter['ot_1_5_hrs_amount'] ?? 0,
                            'restday_daily_salary_rate' => $this->payrollParameter['restday_daily_salary_rate'] ?? 0,
                            'hrs_ot_2_0' => $this->payrollParameter['hrs_ot_2_0'] ?? 0,
                            'ot_2_0_hrs_amount' => $this->payrollParameter['ot_2_0_hrs_amount'] ?? 0,
                            'public_holiday_ot_3_0' => $this->payrollParameter['public_holiday_ot_3_0'] ?? 0,
                            'deduction_hostel' => $this->payrollParameter['deduction_hostel'] ?? 0,
                            'sosco_deduction' => $this->payrollParameter['sosco_deduction'] ?? 0,
                            'sosco_contribution' => $this->payrollParameter['sosco_contribution'] ?? 0,
                            'created_by' => $this->payrollParameter['created_by'] ?? 0,
                            'modified_by' => $this->payrollParameter['created_by'] ?? 0
                        ]
                    );
        
                    DB::table('e-contract_payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');
        
                    Log::info('eContract payroll add end -  '.$eContractPayroll['id']);

                }else{
                    Log::info('ERROR - WORKER EMPLOYMENT DATA NOT FOUND');
                    DB::table('e-contract_payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
                }

            }else{
                Log::info('ERROR - WORKER DATA NOT FOUND');
                DB::table('e-contract_payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
            }
        }else{
            Log::info('ERROR - EMPTY INPUT');
            DB::table('e-contract_payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
        }
        $this->insertRecord();
    }

    /**
     * @param string $comments
     * @param int $status
     */
    public function insertRecord($comments = '', $status = 1): void
    {
        EContractPayrollUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->payrollParameter),
                'comments' => $comments,
                'status' => $status
            ]
        );
    }
}

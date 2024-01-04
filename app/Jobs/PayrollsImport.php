<?php

namespace App\Jobs;

use App\Models\PayrollUploadRecords;
use App\Models\WorkerEmployment;
use App\Models\TotalManagementPayroll;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PayrollsImport extends Job
{
    private mixed $payrollParameter;
    private mixed $bulkUpload;

    const SERVICE_TYPE = 'Total Management';
    const WORKER_DATA_ERROR = 'ERROR - WORKER DATA NOT FOUND';
    const WORKER_EMPLOYMENT_DATA_ERROR = 'ERROR - WORKER EMPLOYMENT DATA NOT FOUND';
    const INPUT_ERROR = 'ERROR - EMPTY INPUT';
    private mixed $passportNumber;
    private mixed $projectId;
    private mixed $month;
    private mixed $year;

    /**
     * Constructor method for the class.
     *
     * @param mixed $payrollParameter The payroll parameter value.
     * @param mixed $bulkUpload The bulk upload value.
     *
     * @return void
     */
    public function __construct($payrollParameter, $bulkUpload)
    {
        $this->payrollParameter = $payrollParameter;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * Handle method for the class.
     *
     * This method handles the process of extracting payroll parameters,
     * validating them, retrieving the worker ID, retrieving the worker
     * employment data, updating or inserting the payroll data, logging
     * errors and failures, and finally inserting the record.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->extractPayrollParameters();

        if ($this->validPayrollParameters()) {
            $workerId = $this->getWorkerId();
            if ($workerId !== null) {
                Log::info('worker data ID - ' . print_r($workerId, true));
                $workerEmployment = $this->getWorkerEmployment($workerId);

                if ($workerEmployment > 0) {
                    $this->updateOrInsertPayroll($workerId);
                } else {
                    $this->logErrorAndIncrementFailure(self::WORKER_EMPLOYMENT_DATA_ERROR);
                }
            } else {
                $this->logErrorAndIncrementFailure(self::WORKER_DATA_ERROR);
            }
        } else {
            $this->logErrorAndIncrementFailure(self::INPUT_ERROR);
        }
        $this->insertRecord();
    }

    /**
     * Extracts and assigns payroll parameters from the given payrollParameter array.
     * The extracted parameters include passport number, project ID, month, and year.
     *
     * @return void
     */
    protected function extractPayrollParameters()
    {
        $this->passportNumber = $this->payrollParameter['passport_number'];
        $this->projectId = $this->payrollParameter['project_id'];
        $this->month = $this->payrollParameter['month'];
        $this->year = $this->payrollParameter['year'];
    }

    /**
     * Checks if the payroll parameters are valid.
     * The validation is based on whether the passport number,
     * project ID, month, and year are not empty.
     *
     * @return bool Returns true if all payroll parameters are valid, false otherwise.
     */
    protected function validPayrollParameters(): bool
    {
        return !(empty($this->passportNumber) || empty($this->projectId) || empty($this->month) || empty($this->year));
    }

    /**
     * Retrieves and returns the ID of the worker based on the passport number.
     * If no worker is found with the given passport number, it returns null.
     *
     * @return int|null The worker ID, or null if no worker is found.
     */
    protected function getWorkerId()
    {
        $worker = DB::table('workers')->where('passport_number', $this->passportNumber)->first('id');
        return $worker->id ?? null;
    }

    /**
     * Retrieves the employment status of the worker specified by the given worker ID.
     * The employment status is represented as the total number of active employments for the worker.
     *
     * @param int $workerId The ID of the worker to retrieve the employment status for.
     * @return int The total number of active employments for the worker.
     */
    protected function getWorkerEmployment($workerId): int
    {
        return WorkerEmployment::where([
            ['worker_id', $workerId],
            ['project_id', $this->projectId],
            ['service_type', self::SERVICE_TYPE]
        ])
            ->whereNull('work_end_date')
            ->whereNull('remove_date')
            ->count();
    }

    /**
     * Updates or inserts a payroll record for the given worker ID.
     *
     * @param int $workerId The ID of the worker.
     * @return void
     */
    protected function updateOrInsertPayroll($workerId)
    {
        $personalData = $this->getPersonalData($workerId);
        $totalManagementPayroll = TotalManagementPayroll::updateOrCreate($personalData, $this->getServiceData());

        DB::table('payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');
        Log::info('payroll insert end - ' . $totalManagementPayroll['id']);
    }

    /**
     * Logs the error message and increments the total failure count in the payroll_bulk_upload table.
     *
     * @param string $errorMessage The error message to be logged.
     *
     * @return void
     */
    protected function logErrorAndIncrementFailure($errorMessage)
    {
        Log::info($errorMessage);
        DB::table('payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
    }

    /**
     * Returns an array containing the personal data of a worker.
     *
     * @param int $workerId The ID of the worker.
     *
     * @return array The array containing the personal data of the worker.
     */
    protected function getPersonalData($workerId): array
    {
        return [
            'worker_id' => $workerId,
            'project_id' => $this->projectId,
            'month' => $this->month,
            'year' => $this->year
        ];
    }

    /**
     * Retrieves the service data from the payrollParameter array.
     *
     * @return array The service data containing various parameters for the payroll.
     */
    protected function getServiceData(): array
    {
        return [
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
        ];
    }

    /**
     * Inserts a new record into the payroll_upload_records table.
     *
     * @param string $comments (optional) Additional comments for the record. Defaults to an empty string.
     * @param int $status (optional) The status of the record. Defaults to 1.
     *
     * @return void
     */
    public function insertRecord($comments = '', $status = 1): void
    {
        PayrollUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->payrollParameter),
                'comments' => $comments,
                'status' => $status
            ]
        );
    }
}

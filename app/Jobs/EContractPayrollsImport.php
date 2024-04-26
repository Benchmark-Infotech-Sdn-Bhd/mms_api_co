<?php

namespace App\Jobs;

use App\Models\EContractPayrollUploadRecords;
use App\Models\WorkerEmployment;
use App\Models\EContractPayroll;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class EContractPayrollsImport extends Job
{
    private mixed $payrollParameter;
    private mixed $bulkUpload;
    private $dbName;

    /**
     * Constructor method.
     *
     * Initializes a new instance of the class with the specified payroll parameter and bulk upload flag.
     *
     * @param mixed $payrollParameter The payroll parameter.
     * @param bool $bulkUpload Flag indicating whether bulk upload is enabled or not.
     * @param $dbName
     * 
     * @return void
     */
    public function __construct($dbName, $payrollParameter, $bulkUpload)
    {
        $this->dbName = $dbName;
        $this->payrollParameter = $payrollParameter;
        $this->bulkUpload = $bulkUpload;
    }
    /**
     * validate the payroll import request data
     * 
     * @return array
     */
    public function formatValidation(): array
    {
        return [
            'passport_number' => 'required',
            'project_id' => 'required',
            'month' => 'required',
            'year' => 'required'
        ];
    }


    /**
     * Handle method.
     *
     * Handles the payroll parameter and performs necessary actions based on the validation result.
     *
     * 
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices): void
    {
        $comments = '';
        $successFlag = 0;
        $parameters = $this->payrollParameter;
        if ($this->validatePayrollParameters($parameters)) {
            $worker = DB::table('workers')
                ->where('passport_number', $parameters['passport_number'])
                ->first('id');
            if (isset($worker->id)) {
                $this->handleWorker($worker, $parameters);
            } else {
                $this->logAndIncrementFailure('ERROR - WORKER DATA NOT FOUND');
            }
        } else {
            $this->logAndIncrementFailure('ERROR - EMPTY INPUT');
        }
        $this->insertRecord($comments, 1, $successFlag, $parameters['company_id']);
    }

    /**
     * Validates the payroll parameters.
     *
     * Checks if the specified array of parameters contains the required keys and their corresponding values are not empty.
     *
     * @param array $parameters The array of payroll parameters.
     *
     * @return bool True if all the required parameters are present and their values are not empty, false otherwise.
     */
    private function validatePayrollParameters(array $parameters): bool
    {
        return !empty($parameters['passport_number']) &&
            !empty($parameters['project_id']) &&
            !empty($parameters['month']) &&
            !empty($parameters['year']);
    }

    /**
     * Handles a worker.
     *
     * Checks if the worker is employed with the specified project and service type.
     * Logs a message and increments the count based on the result.
     *
     * @param object $worker The worker object.
     * @param array $parameters ['project_id'] The project ID.
     *
     * @return void
     */
    private function handleWorker($worker, array $parameters): void
    {
        $workerEmployment = WorkerEmployment::where([
            ['worker_id', $worker->id],
            ['project_id', $parameters['project_id']],
            ['service_type', 'e-Contract']
        ])
            ->whereNull('work_end_date')
            ->whereNull('remove_date')
            ->count();

        $logMessage = ($workerEmployment > 0)
            ? 'eContract payroll add - started '
            : 'ERROR - WORKER EMPLOYMENT DATA NOT FOUND';
        $this->logAndIncrement($workerEmployment > 0, $worker, $parameters, $logMessage);
    }

    /**
     * Logs a message and increments the total_success or total_failure count in the e-contract_payroll_bulk_upload table.
     *
     * @param bool $isSuccessful Whether the operation was successful or not.
     * @param mixed $worker The worker object or identifier.
     * @param array $parameters Additional parameters.
     * @param string $logMessage The message to log.
     * @return void
     */
    private function logAndIncrement(bool $isSuccessful, $worker, array $parameters, string $logMessage): void
    {
        Log::info($logMessage);
        if ($isSuccessful) {
            $payroll = $this->updateOrCreatePayroll($worker, $parameters);
            Log::info('eContract payroll add end - ' . $payroll['id']);
        }
        DB::table('e-contract_payroll_bulk_upload')
            ->where('id', $this->bulkUpload->id)
            ->increment($isSuccessful ? 'total_success' : 'total_failure');
    }

    /**
     * Logs an information message and increments the 'total_failure' column in the 'e-contract_payroll_bulk_upload' table.
     *
     * @param string $logMessage The message to be logged.
     *
     * @return void
     */
    private function logAndIncrementFailure(string $logMessage): void
    {
        Log::info($logMessage);
        DB::table('e-contract_payroll_bulk_upload')
            ->where('id', $this->bulkUpload->id)
            ->increment('total_failure');
    }

    /**
     * Updates or creates a payroll record in the 'e-contract_payroll' table.
     *
     * @param $worker - The worker object.
     * @param array $parameters The parameters used to update or create the payroll record.
     *        - 'worker_id' (integer): The ID of the worker.
     *        - 'project_id' (integer): The ID of the project.
     *        - 'month' (integer): The month for the payroll record.
     *        - 'year' (integer): The year for the payroll record.
     *        - ... (any other optional parameters): Additional fields for the payroll record.
     *
     * @return EContractPayroll The updated or created EContractPayroll object.
     */
    private function updateOrCreatePayroll($worker, array $parameters): EContractPayroll
    {
        return EContractPayroll::updateOrCreate(
            [
                'worker_id' => $worker->id,
                'project_id' => $parameters['project_id'],
                'month' => $parameters['month'],
                'year' => $parameters['year']
            ],
            array_map(function ($value) {
                return $value ?? 0;
            }, $parameters)
        );
    }

    /**
     * Inserts a new record into the 'e-contract_payroll_upload_records' table.
     *
     * @param string $comments [optional] Additional comments for the record, defaults to an empty string.
     * @param int $status [optional] The status of the record, defaults to 1.
     * @param int $successFlag
     * @param int $companyId
     *
     * @return void
     */
    public function insertRecord($comments = '', $status = 1, $successFlag, $companyId): void
    {
        EContractPayrollUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->payrollParameter),
                'comments' => $comments,
                'status' => $status,
                'success_flag' => $successFlag,
                'company_id' => $companyId
            ]
        );
    }
}

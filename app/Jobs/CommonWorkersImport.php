<?php

namespace App\Jobs;

use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\WorkerVisa;
use App\Models\WorkerBioMedical;
use App\Models\BulkUploadRecords;
use App\Models\WorkerFomema;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerBankDetails;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CommonWorkersImport extends Job
{
    private mixed $workerParameter;
    private mixed $bulkUpload;
    private mixed $workerNonMandatory;
    private $dbName;

    /**
     * Constructor method for the class.
     *
     * @param mixed $workerParameter The value for the workerParameter property.
     * @param bool $bulkUpload The value for the bulkUpload property.
     * @param mixed $workerNonMandatory The value for the workerNonMandatory property.
     * @param $dbName
     * @return void
     */
    public function __construct($dbName, $workerParameter, $bulkUpload, $workerNonMandatory)
    {
        $this->dbName = $dbName;
        $this->workerParameter = $workerParameter;
        $this->bulkUpload = $bulkUpload;
        $this->workerNonMandatory = $workerNonMandatory;
    }

    /**
     * Method to handle the worker insert process.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     * @throws \JsonException
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices): void
    {
        Log::info('Worker insert - started '.$this->dbName);
        $databaseConnectionServices->dbConnectQueue($this->dbName);
        $comments = '';
        $successFlag = 0;
        $validationError = $this->validateParameters();
        $bulkUpdateId = $this->bulkUpload->id;

        if (!empty($validationError)) {
            DB::table('worker_bulk_upload')->where('id', $bulkUpdateId)->increment('total_failure');
            Log::info('ERROR - required params are empty');
            $comments .= 'ERROR - ' . $validationError;
            $this->insertRecord($comments, 1, $successFlag, $this->workerParameter['company_id']);
            return;
        }

        // valid case
        $workerRelationship = DB::table('kin_relationship')
            ->where('name', $this->workerParameter['kin_relationship'])
            ->first('id');


        if (is_null($workerRelationship)) {
            $this->logFailure($comments, $bulkUpdateId, 'Invalid Kin Relationship');
            return;
        }

        Log::info('Row Data - relationship - ' . print_r($workerRelationship->id, true));

        $workerCount = DB::table('workers')
            ->where('passport_number', $this->workerParameter['passport_number'])
            ->count();

        if ($workerCount > 0) {
            $this->logFailure($comments, $bulkUpdateId, 'worker - passport already exist ' . $this->workerParameter['passport_number']);
            return;
        }

        $this->createWorker($workerRelationship);
        $this->logSuccess($bulkUpdateId);
    }

    /**
     * Validates the parameters using the Laravel Validator.
     *
     * @return string Returns an error message if validation fails, or an empty string if validation passes.
     */
    private function validateParameters(): string
    {
        $validator = Validator::make($this->workerParameter, $this->createValidation());
        return $validator->fails() ? str_replace(".,", ", ", implode(",", $validator->messages()->all())) : '';
    }

    /**
     * Logs a failure in the worker import process.
     *
     * @param string $comments Additional comments about the failure.
     * @param int $bulkUpdateId The ID of the bulk update.
     * @param string $reason The reason for the failure.
     * @return void
     */
    private function logFailure(string $comments, int $bulkUpdateId, string $reason): void
    {
        $comments .= 'ERROR - ' . $reason;
        DB::table('worker_bulk_upload')->where('id', $bulkUpdateId)->increment('total_failure');
        Log::info('ERROR - worker import failed  due to ' . $comments);
    }

    /**
     * Logs the success of a bulk update.
     *
     * @param int $bulkUpdateId The ID of the bulk update.
     * @return void
     */
    private function logSuccess(int $bulkUpdateId): void
    {
        DB::table('worker_bulk_upload')->where('id', $bulkUpdateId)->increment('total_success');
        Log::info('Success - ' . 'worker imported');
    }

    /**
     * Creates a worker and its related data.
     *
     * @param string $workerRelationship The worker's relationship
     * @return void
     */
    private function createWorker($workerRelationship): void
    {
        $worker = Workers::create($this->createWorkerParams());

        // create the related data
        WorkerKin::create($this->createWorkerKinParams($worker, $workerRelationship));
        WorkerVisa::create($this->createWorkerVisaParams($worker));
        WorkerBioMedical::create($this->createWorkerBioMedicalParams($worker));
        WorkerFomema::create($this->createWorkerFomemaParams($worker));
        WorkerInsuranceDetails::create($this->createWorkerInsuranceDetailsParams($worker));
        WorkerBankDetails::create($this->createWorkerBankDetailsParams($worker));
    }

    /**
     * Generates an array of parameters for creating a worker.
     *
     * @return array The worker parameters
     */
    private function createWorkerParams(): array
    {
        return [
            'name' => $this->workerParameter['name'] ?? '',
            'gender' => $this->workerParameter['gender'] ?? '',
            'date_of_birth' => $this->workerParameter['date_of_birth'] ?? '',
            'passport_number' => $this->workerParameter['passport_number'] ?? '',
            'passport_valid_until' => $this->workerParameter['passport_valid_until'] ?? '',
            'fomema_valid_until' => null,
            'status' => 1,
            'address' => $this->workerParameter['address'] ?? '',
            'city' => $this->workerParameter['city'] ?? '',
            'state' => $this->workerParameter['state'] ?? '',
            'crm_prospect_id' => $this->workerNonMandatory['crm_prospect_id'] ?? 0,
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'company_id' => $this->workerParameter['company_id'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Creates the parameters for the worker's kin data.
     *
     * @param array $worker The worker object
     * @param string $workerRelationship The worker's relationship
     * @return array The worker's kin data parameters
     */
    private function createWorkerKinParams($worker, $workerRelationship): array
    {
        return [
            'worker_id' => $worker['id'],
            'kin_name' => $this->workerParameter['kin_name'] ?? '',
            'kin_relationship_id' => $workerRelationship->id ?? 0,
            'kin_contact_number' => $this->workerParameter['kin_contact_number'] ?? '',
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Creates the parameters for creating a worker visa.
     *
     * @param array $worker The worker data
     * @return array The worker visa parameters
     */
    private function createWorkerVisaParams($worker): array
    {
        return [
            'worker_id' => $worker['id'],
            'ksm_reference_number' => $this->workerParameter['ksm_reference_number'],
            'work_permit_valid_until' => $this->workerParameter['work_permit_valid_until'] ?? '',
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Creates the parameters for creating a worker's bio-medical data.
     *
     * @param array $worker The worker's data
     * @return array The parameters for creating a worker's bio-medical data
     */
    private function createWorkerBioMedicalParams($worker): array
    {
        return [
            'worker_id' => $worker['id'],
            'bio_medical_reference_number' => $this->workerParameter['bio_medical_reference_number'],
            'bio_medical_valid_until' => $this->workerParameter['bio_medical_valid_until'],
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Creates an array of parameters for creating a worker's Fomema data.
     *
     * @param array $worker The worker's data
     * @return array The Fomema parameters
     */
    private function createWorkerFomemaParams($worker): array
    {
        return [
            'worker_id' => $worker['id'],
            'purchase_date' => $this->workerParameter['purchase_date'] ?? '',
            'clinic_name' => $this->workerParameter['clinic_name'] ?? '',
            'doctor_code' => $this->workerParameter['doctor_code'] ?? '',
            'allocated_xray' => $this->workerParameter['allocated_xray'] ?? '',
            'xray_code' => $this->workerParameter['xray_code'] ?? '',
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Generates the parameters for creating worker insurance details.
     *
     * @param array $worker The worker data
     * @return array The parameters for creating worker insurance details
     */
    private function createWorkerInsuranceDetailsParams($worker): array
    {
        return [
            'worker_id' => $worker['id'],
            'ig_policy_number' => $this->workerParameter['ig_policy_number'],
            'hospitalization_policy_number' => $this->workerParameter['hospitalization_policy_number'],
            'insurance_expiry_date' => $this->workerParameter['insurance_expiry_date'] ?? '',
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }

    /**
     * Creates the parameters for creating a worker's bank details.
     *
     * @param array $worker The worker's data
     * @return array The parameters for creating the worker's bank details
     */
    private function createWorkerBankDetailsParams($worker): array
    {
        return [
            'worker_id' => $worker['id'],
            'bank_name' => $this->workerNonMandatory['bank_name'] ?? '',
            'account_number' => $this->workerNonMandatory['account_number'] ?? '',
            'socso_number' => $this->workerNonMandatory['socso_number'] ?? '',
            'created_by' => $this->workerParameter['created_by'] ?? 0,
            'modified_by' => $this->workerParameter['created_by'] ?? 0,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];
    }


    /**
     * Creates validation rules for worker data.
     *
     * @return array The validation rules for worker data
     */
    public function createValidation(): array
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
            'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/|unique:workers',
            'passport_valid_until' => 'required|date|date_format:Y-m-d',
            'address' => 'required',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'kin_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'kin_relationship' => 'required',
            'kin_contact_number' => 'required|regex:/^[0-9]+$/',
            'ksm_reference_number' => 'required',
            'bio_medical_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/|max:255',
            'bio_medical_valid_until' => 'required|date|date_format:Y-m-d',
            'purchase_date' => 'required',
            'clinic_name' => 'required',
            'doctor_code' => 'required',
            'allocated_xray' => 'required',
            'xray_code' => 'required',
            'ig_policy_number' => 'required',
            'hospitalization_policy_number' => 'required',
            'insurance_expiry_date' => 'required',
            'company_id' => 'required'
        ];
    }

    /**
     * Inserts a record into the BulkUploadRecords table.
     *
     * @param string $comments The comments for the record
     * @param string $status The status of the record
     * @param bool $successFlag The success flag for the record
     * @param int $companyId The ID of the company associated with the record
     * @return void
     */
    public function insertRecord($comments, $status, $successFlag, $companyId): void
    {
        BulkUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->workerParameter),
                'comments' => $comments,
                'status' => $status,
                'success_flag' => $successFlag,
                'company_id' => $companyId
            ]
        );
    }
}

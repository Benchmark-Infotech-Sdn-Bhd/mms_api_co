<?php

namespace App\Imports;

use App\Jobs\CommonWorkersImport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CommonWorkerImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    private mixed $parameters;
    private string $bulkUpload;

    /**
     * __construct method
     *
     * Initializes a new instance of the class.
     *
     * @param mixed $parameters The parameters to initialize the instance with.
     *
     * @param string $bulkUpload (Optional) The file path for bulk upload.
     *
     * @return void
     */
    public function __construct($parameters, $bulkUpload = '')
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * model method
     *
     * Process a single row of worker data for bulk upload.
     *
     * @param array $row The row of worker data to process.
     *
     * @return void
     */
    public function model(array $row)
    {
        try {
            $workerParameter = $this->createWorkerParameter($row);
            $workerNonMandatory = $this->createWorkerNonMandatory($row);

            DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');

            dispatch(new CommonWorkersImport($workerParameter, $this->bulkUpload, $workerNonMandatory))/*->onQueue('common_worker_import')->onConnection('database')*/
            ;
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($exception->getMessage(), true));
        }
    }

    /**
     * Creates a worker parameter array based on the given row data.
     *
     * @param array $row The row data to create the worker parameter from.
     * @return array The worker parameter array.
     */
    private function createWorkerParameter(array $row): array
    {
        return [
            'name' => $row['name'] ?? '',
            'date_of_birth' => $row['date_of_birth'] ? $this->dateConvert($row['date_of_birth']) : '',
            'gender' => $row['gender'] ?? '',
            'passport_number' => isset($row['passport_number']) ? (string)$row['passport_number'] : '',
            'passport_valid_until' => $this->dateConvert($row['passport_valid_until']),
            'address' => $row['address'] ?? '',
            'city' => $row['city'] ?? '',
            'state' => $row['state'] ?? '',
            'kin_name' => $row['kin_name'] ?? '',
            'kin_relationship' => $row['kin_relationship'] ?? '',
            'kin_contact_number' => $row['kin_contact_number'] ?? '',
            'ksm_reference_number' => $row['ksm_reference_number'] ?? '',
            'bio_medical_reference_number' => $row['bio_medical_reference_number'] ?? '',
            'bio_medical_valid_until' => $this->dateConvert($row['bio_medical_valid_until']),
            'work_permit_valid_until' => $this->dateConvert($row['work_permit_valid_until']),
            'purchase_date' => $this->dateConvert($row['purchase_date']),
            'clinic_name' => $row['clinic_name'] ?? '',
            'doctor_code' => $row['doctor_code'] ?? '',
            'allocated_xray' => $row['allocated_xray'] ?? '',
            'xray_code' => $row['xray_code'] ?? '',
            'ig_policy_number' => $row['ig_policy_number'] ?? '',
            'hospitalization_policy_number' => $row['hospitalization_policy_number'] ?? '',
            'insurance_expiry_date' => $this->dateConvert($row['insurance_expiry_date']),
            'created_by' => $this->parameters['created_by'] ?? 0,
            'modified_by' => $this->parameters['created_by'] ?? 0,
            'company_id' => $this->parameters['company_id'] ?? 0,
        ];
    }

    /**
     * Creates a worker non-mandatory parameter array based on the given row data.
     *
     * @param array $row The row data to create the worker non-mandatory parameter from.
     * @return array The worker non-mandatory parameter array.
     */
    private function createWorkerNonMandatory(array $row): array
    {
        return [
            'crm_prospect_id' => $this->parameters['crm_prospect_id'] ?? 0,
            'bank_name' => $row['bank_name'] ?? '',
            'account_number' => $row['account_number'] ?? '',
            'socso_number' => $row['socso_number'] ?? '',
        ];
    }

    /**
     * Converts a given date value from Excel format to a PHP DateTime object and returns it in 'Y-m-d' date format.
     *
     * @param mixed $date The date value to be converted.
     * @return string The formatted date in 'Y-m-d' format.
     */
    public function dateConvert($date)
    {
        return Date::excelToDateTimeObject(intval($date))->format('Y-m-d');
    }

    /**
     * Retrieves the chunk size value.
     *
     * @return int The chunk size value.
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}

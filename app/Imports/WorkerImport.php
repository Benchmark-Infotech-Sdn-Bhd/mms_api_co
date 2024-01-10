<?php

namespace App\Imports;

use App\Jobs\WorkersImport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class WorkerImport implements ToModel, WithChunkReading, WithHeadingRow, WithMultipleSheets
{
    protected const CHUNK_ROW = 250;
    private mixed $parameters;
    private mixed $bulkUpload;

    /**
     * Class constructor.
     *
     * Initializes a new instance of the class.
     *
     * @param mixed $parameters The parameters for the constructor.
     * @param mixed $bulkUpload (Optional) The bulk upload value. Default is an empty string.
     *
     * @return void
     */
    public function __construct($parameters, $bulkUpload = '')
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * Process a single row of data in the model.
     *
     * This method logs the row data, prepares the worker parameter and worker non-mandatory data,
     * increments the total records in the worker bulk upload table, and dispatches a WorkersImport job.
     * If there is any exception occurred, it logs the error message.
     *
     * @param array $row The row data to process.
     *
     * @return void
     */
    public function model(array $row)
    {
        try {
            Log::info('Row Data' . print_r($row, true));

            $workerParameter = $this->prepareWorkerParameter($row);
            $workerNonMandatory = $this->prepareWorkerNonMandatory($row);

            DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');
            dispatch(new WorkersImport($workerParameter, $this->bulkUpload, $workerNonMandatory));
        } catch (Exception $exception) {
            Log::error('Error - ' . print_r($exception->getMessage(), true));
        }
    }

    /**
     * Prepare the worker parameter from the given row.
     *
     * This method takes an array row and prepares the worker parameter array
     * using the values of the row and the parameters passed to the class constructor.
     *
     * @param array $row The row array containing the worker data.
     * @return array The prepared worker parameter array.
     */
    protected function prepareWorkerParameter(array $row): array
    {
        return [
            'onboarding_country_id' => $this->parameters['onboarding_country_id'],
            'agent_code' => $row['agent_code'],
            'application_id' => $this->parameters['application_id'],
            'name' => $row['name'] ?? '',
            'date_of_birth' => $this->dateConvert($row['date_of_birth']),
            'gender' => $row['gender'] ?? '',
            'passport_number' => isset($row['passport_number']) ? (string)$row['passport_number'] : '',
            'passport_valid_until' => $this->dateConvert($row['passport_valid_until']),
            'address' => $row['address'] ?? '',
            'state' => $row['state'] ?? '',
            'kin_name' => $row['kin_name'] ?? '',
            'kin_relationship' => $row['kin_relationship'] ?? '',
            'kin_contact_number' => $row['kin_contact_number'] ?? '',
            'ksm_reference_number' => $row['ksm_reference_number'] ?? '',
            'bio_medical_reference_number' => $row['bio_medical_reference_number'] ?? '',
            'bio_medical_valid_until' => $this->dateConvert($row['bio_medical_valid_until']),
            'created_by' => $this->parameters['created_by'] ?? 0,
            'modified_by' => $this->parameters['modified_by'] ?? 0,
            'company_id' => $this->parameters['company_id'] ?? 0,
        ];
    }

    /**
     * Prepare worker non-mandatory.
     *
     * Prepares and returns an array containing the non-mandatory information for a worker.
     *
     * @param array $row The input array containing the worker details.
     *
     * @return array The prepared worker non-mandatory information as an array.
     */
    protected function prepareWorkerNonMandatory(array $row): array
    {
        return [
            'city' => $row['city'] ?? '',
        ];
    }

    /**
     * Converts an Excel date to a formatted date string.
     *
     * @param int $date The Excel date to convert.
     *
     * @return string The formatted date string in the 'Y-m-d' format.
     */
    public function dateConvert($date)
    {
        return Date::excelToDateTimeObject(intval($date))->format('Y-m-d');
    }

    /**
     * Get the chunk size value.
     *
     * This method returns the chunk size value defined in the class constant CHUNK_ROW.
     *
     * @return int The chunk size value.
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }

    /**
     * Get the sheets.
     *
     * Returns an array containing the current object as the only element.
     *
     * @return array An array containing the current object.
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }
}

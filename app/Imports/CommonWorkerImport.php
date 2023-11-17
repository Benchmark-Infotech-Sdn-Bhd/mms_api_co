<?php

namespace App\Imports;

use App\Jobs\CommonWorkersImport;
use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\KinRelationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CommonWorkerImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    
    private $parameters;
    
    /**
     * @var string
     */
    private $bulkUpload;

    /**
     * Create a new job instance.
     *
     * @param $parameters
     * @param string $bulkUpload
     */
    public function __construct($parameters, $bulkUpload = '')
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
    }
    /**
     * @param array $row
     * @return Model|Model[]|void|null
     */
    public function model(array $row)
    {
        try {
                // Log::info('Row Data' . print_r($row, true));
                
                $workerParameter = [
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
                $workerNonMandatory = [
                    'crm_prospect_id' => $this->parameters['crm_prospect_id'] ?? 0,
                    'bank_name' => $row['bank_name'] ?? '',
                    'account_number' => $row['account_number'] ?? '',
                    'socso_number' => $row['socso_number'] ?? '',
                ];
                // Log::info('Row Data' . print_r($workerParameter, true));
                
                DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');
                dispatch(new CommonWorkersImport($workerParameter, $this->bulkUpload, $workerNonMandatory))/*->onQueue('common_worker_import')->onConnection('database')*/;

        } catch (\Exception $exception) {
            Log::error('Error - ' . print_r($exception->getMessage(), true));
        }
    }

    /**
     * @param $date
     * @return string
     */
    public function dateConvert($date)
    {
        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(intval($date))->format('Y-m-d');
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}

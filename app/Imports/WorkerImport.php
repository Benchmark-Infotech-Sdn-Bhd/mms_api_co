<?php

namespace App\Imports;

use App\Jobs\WorkersImport;
use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\KinRelationship;
use App\Models\WorkerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkerImport implements ToModel, WithChunkReading, WithHeadingRow
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
                Log::info('Row Data' . print_r($row, true));
                
                $workerParameter = [
                    'onboarding_country_id' => $this->parameters['onboarding_country_id'],
                    'agent_id' => $this->parameters['agent_id'],
                    'application_id' => $this->parameters['application_id'],
                    'name' => $row['name'] ?? '',
                    'date_of_birth' => '2013-07-01',//$this->dateConvert($row['date_of_birth']),
                    'gender' => $row['gender'] ?? '',                        
                    'passport_number' => isset($row['passport_number']) ? (string)$row['passport_number'] : '',
                    'passport_valid_until' => '2013-09-01', //isset($row['passport_valid_until']) ? (string)$row['passport_valid_until'] : '',
                    'address' => $row['address'] ?? '',
                    'city' => $row['city'] ?? '',
                    'state' => $row['state'] ?? '',

                    'kin_name' => $row['kin_name'] ?? '',
                    'kin_relationship_id' => $row['kin_relationship_id'] ?? '',
                    'kin_contact_number' => $row['kin_contact_number'] ?? '',
                    'ksm_reference_number' => $row['ksm_reference_number'] ?? '',
                    'bio_medical_reference_number' => $row['bio_medical_reference_number'] ?? '',
                    'bio_medical_valid_until' => $row['bio_medical_valid_until'] ?? '',
                    'created_by' => $row['created_by'] ?? 0,
                    'modified_by' => $row['modified_by'] ?? 0
                ];
                
                DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');
                dispatch(new WorkersImport($workerParameter, $this->bulkUpload));

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
        return $date;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}

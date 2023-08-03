<?php

namespace App\Jobs;

use App\Models\DirectrecruitmentWorkers;
use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\WorkerVisa;
use App\Models\WorkerBioMedical;
use App\Models\BulkUploadRecords;
use App\Services\ManageWorkersServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkersImport extends Job
{
    private $parameters;
    private $bulkUpload;
    private $workerParameter;

    /**
     * Create a new job instance.
     *
     * @param $workerParameter
     * @param $parameters
     * @param $bulkUpload
     */
    public function __construct($workerParameter, $bulkUpload)
    {
        $this->workerParameter = $workerParameter;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * Execute the job.
     *
     * @param ManageWorkersServices $manageWorkersServices
     * @return void
     * @throws \JsonException
     */
    public function handle(ManageWorkersServices $manageWorkersServices): void
    { 

        Log::info('Worker instert - started ');
        DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');

        $worker = Workers::create([            
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
            'created_by'    => $this->workerParameter['created_by'] ?? 0,
            'modified_by'   => $this->workerParameter['created_by'] ?? 0,
            'created_at'    => null,
            'updated_at'    => null
        ]);

        DirectrecruitmentWorkers::create([
            "worker_id" => $worker['id'],
            'onboarding_country_id' => $this->workerParameter['onboarding_country_id'] ?? 0,
            'agent_id' => $this->workerParameter['agent_id'] ?? 0,
            'application_id' => $this->workerParameter['application_id'] ?? 0,
            'created_by'    => $this->workerParameter['created_by'] ?? 0,
            'modified_by'   => $this->workerParameter['created_by'] ?? 0 ,
            'created_at'    => null,
            'updated_at'    => null         
        ]);

        WorkerKin::create([
            "worker_id" => $worker['id'],
            "kin_name" => $this->workerParameter['kin_name'] ?? '',
            "kin_relationship_id" => $this->workerParameter['kin_relationship_id'] ?? '',
            "kin_contact_number" =>  $this->workerParameter['kin_contact_number'] ?? '',
            'created_at'    => null,
            'updated_at'    => null         
        ]);

        WorkerVisa::create([
            "worker_id" => $worker['id'],
            "ksm_reference_number" => $this->workerParameter['ksm_reference_number'],
            "calling_visa_valid_until" =>  null,         
            "entry_visa_valid_until" =>  null,
            "work_permit_valid_until" =>  null
        ]);

        WorkerBioMedical::create([
            "worker_id" => $worker['id'],
            "bio_medical_reference_number" => $this->workerParameter['bio_medical_reference_number'],
            "bio_medical_valid_until" => $this->workerParameter['bio_medical_valid_until'],
        ]);

        Log::info('Worker instertd -  '.$worker['id']);

        $this->insertRecord();
    }

    /**
     * @param string $comments
     * @param int $status
     */
    public function insertRecord($comments = '', $status = 1): void
    {
        BulkUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->workerParameter),
                'comments' => $comments,
                'status' => $status
            ]
        );
    }
}

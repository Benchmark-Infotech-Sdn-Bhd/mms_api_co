<?php

namespace App\Jobs;

use App\Models\Workers;
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
    public function __construct($workerParameter, $parameters, $bulkUpload)
    {
        $this->workerParameter = $workerParameter;
        $this->parameters = $parameters;
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
        DB::table('bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');
        Workers::create($this->workerParameter);
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

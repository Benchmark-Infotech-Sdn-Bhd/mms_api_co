<?php

namespace App\Events;

class WorkerQuotaUpdated extends Event
{
    /**
     * @var int
     */
    public $bulkUploadId;
    /**
     * @var int
     */
    public $workerCount;
    /**
     * @var string
     */
    public $type;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($bulkUploadId, $workerCount, $type)
    {
        $this->bulkUploadId = $bulkUploadId;
        $this->workerCount = $workerCount;
        $this->type = $type;
    }
}

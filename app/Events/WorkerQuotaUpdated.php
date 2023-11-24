<?php

namespace App\Events;

class WorkerQuotaUpdated extends Event
{
    /**
     * @var int
     */
    public $onBoardingCountryId;
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
    public function __construct($onBoardingCountryId, $workerCount, $type)
    {
        $this->onBoardingCountryId = $onBoardingCountryId;
        $this->workerCount = $workerCount;
        $this->type = $type;
    }
}

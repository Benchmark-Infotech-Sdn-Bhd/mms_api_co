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
     * Class constructor.
     *
     * Initializes a new instance of the class.
     *
     * @param int $onBoardingCountryId The onboarding country ID.
     * @param int $workerCount The number of workers.
     * @param string $type The type of the object.
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

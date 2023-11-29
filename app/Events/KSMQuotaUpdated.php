<?php

namespace App\Events;

class KSMQuotaUpdated extends Event
{
    /**
     * @var int
     */
    public $onBoardingCountryId;
    /**
     * @var string
     */
    public $ksmReferenceNumber;
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
    public function __construct($onBoardingCountryId, $ksmReferenceNumber, $workerCount, $type)
    {
        $this->onBoardingCountryId = $onBoardingCountryId;
        $this->ksmReferenceNumber = $ksmReferenceNumber;
        $this->workerCount = $workerCount;
        $this->type = $type;
    }
}

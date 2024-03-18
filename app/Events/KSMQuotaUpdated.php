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
     * Constructor for the class.
     *
     * @param int $onBoardingCountryId The ID of the onboarding country.
     * @param string $ksmReferenceNumber The reference number for KSM.
     * @param int $workerCount The number of workers.
     * @param string $type The type of the object.
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

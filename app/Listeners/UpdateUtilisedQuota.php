<?php

namespace App\Listeners;

use App\Events\WorkerQuotaUpdated;
use App\Models\DirectRecruitmentOnboardingCountry;

class UpdateUtilisedQuota
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WorkerQuotaUpdated  $event
     * @return void
     */
    public function handle(WorkerQuotaUpdated $event)
    {
        $utilisedQuota = 0;
        $countryDetails = DirectRecruitmentOnboardingCountry::findOrFail($event->onBoardingCountryId);
        if($event->type == 'increment') {
            $utilisedQuota = $countryDetails->utilised_quota + $event->workerCount;
        } else if($event->type == 'decrement') {
            $utilisedQuota = (($countryDetails->utilised_quota - $event->workerCount) < 0) ? 0 : $countryDetails->utilised_quota - $event->workerCount;;
        }
        $countryDetails->utilised_quota = $utilisedQuota;
        $countryDetails->save();  
    }
}

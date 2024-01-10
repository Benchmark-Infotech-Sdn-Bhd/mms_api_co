<?php

namespace App\Listeners;

use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Events\KSMQuotaUpdated;

class UpdateKSMUtilisedQuota
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
     * @param  KSMQuotaUpdated  $event
     * @return void
     */
    public function handle(KSMQuotaUpdated $event)
    {
        $utilisedQuota = 0;
        $ksmDetails = OnboardingCountriesKSMReferenceNumber::where('onboarding_country_id', $event->onBoardingCountryId)
                            ->where('ksm_reference_number', $event->ksmReferenceNumber)
                            ->first(['id', 'utilised_quota']);
        if(!is_null($ksmDetails)) {
            if($event->type == 'increment') {
                $utilisedQuota = $ksmDetails->utilised_quota + $event->workerCount;
            } else if($event->type == 'decrement') {
                // echo $event->workerCount;
                $utilisedQuota = (($ksmDetails->utilised_quota - $event->workerCount) < 0) ? 0 : $ksmDetails->utilised_quota - $event->workerCount;
            }
            OnboardingCountriesKSMReferenceNumber::where('id', $ksmDetails->id)->update(['utilised_quota' => $utilisedQuota]);
        }
    }
}

<?php

namespace App\Listeners;

use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Events\KSMQuotaUpdated;

class UpdateKSMUtilisedQuota
{

    /**
     * Handles the KSMQuotaUpdated event.
     *
     * @param KSMQuotaUpdated $event The event object.
     * @return void
     */
    public function handle(KSMQuotaUpdated $event)
    {
        $ksmDetails = OnboardingCountriesKSMReferenceNumber::where('onboarding_country_id', $event->onBoardingCountryId)
            ->where('ksm_reference_number', $event->ksmReferenceNumber)
            ->first(['id', 'utilised_quota']);

        $utilisedQuota = $this->calculateUtilisedQuota($event, $ksmDetails);
        $this->updateUtilisedQuota($ksmDetails, $utilisedQuota);
    }

    /**
     * Calculates the utilised quota based on the event type and worker count.
     *
     * @param object $event The event object that contains the type and worker count.
     * @param object $ksmDetails The ksmDetails object that contains the utilised quota.
     * @return int The updated utilised quota based on the event type and worker count.
     */
    private function calculateUtilisedQuota($event, $ksmDetails)
    {
        if ($event->type == 'increment') {
            return $ksmDetails->utilised_quota + $event->workerCount;
        }

        if ($event->type == 'decrement') {
            return max(0, $ksmDetails->utilised_quota - $event->workerCount);
        }

        return 0;
    }

    /**
     * Updates the utilised quota in the database for the given ksmDetails.
     *
     * @param object $ksmDetails The ksmDetails object that contains the id.
     * @param int $utilisedQuota The updated utilised quota value to be saved in the database.
     *
     * @return void
     */
    private function updateUtilisedQuota($ksmDetails, $utilisedQuota)
    {
        OnboardingCountriesKSMReferenceNumber::where('id', $ksmDetails->id)->update(['utilised_quota' => $utilisedQuota]);
    }
}

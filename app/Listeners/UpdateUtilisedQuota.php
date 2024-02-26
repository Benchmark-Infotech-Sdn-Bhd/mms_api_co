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
     * Handle the WorkerQuotaUpdated event.
     *
     * @param WorkerQuotaUpdated $event
     * @return void
     */
    public function handle(WorkerQuotaUpdated $event)
    {
        $countryDetails = DirectRecruitmentOnboardingCountry::findOrFail($event->onBoardingCountryId);
        $countryDetails->utilised_quota = $this->updateUtilisedQuota($countryDetails->utilised_quota, $event->workerCount, $event->type);
        $countryDetails->save();
    }

    /**
     * Update the utilised quota based on the given parameters.
     *
     * @param int $currentQuota The current utilised quota.
     * @param int $workerCount The number of workers being updated.
     * @param string $type The type of update ('increment' or 'decrement').
     * @return int The updated utilised quota.
     */
    private function updateUtilisedQuota(int $currentQuota, int $workerCount, string $type): int
    {
        if ($type === 'increment') {
            return $currentQuota + $workerCount;
        }
        if ($type === 'decrement') {
            return max(0, $currentQuota - $workerCount);
        }

        return $currentQuota;
    }
}

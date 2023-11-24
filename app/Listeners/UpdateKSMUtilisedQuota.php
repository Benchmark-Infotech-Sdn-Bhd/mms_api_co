<?php

namespace App\Listeners;

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
        //
    }
}

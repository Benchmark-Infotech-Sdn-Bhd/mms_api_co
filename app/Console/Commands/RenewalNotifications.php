<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\NotificationServices;
use Illuminate\Support\Facades\Log;

class RenewalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:RenewalNotifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RenewalNotifications Generation';

    /**
     * @var NotificationServices
     */
    private NotificationServices $notificationServices;

    /**
     * Class constructor.
     *
     * @param NotificationServices $notificationServices The notification services object.
     * @return void
     */
    public function __construct(NotificationServices $notificationServices)
    {
        parent::__construct();
        $this->notificationServices = $notificationServices;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - RenewalNotifications');
        $this->notificationServices->renewalNotifications();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - RenewalNotifications');
    }
}

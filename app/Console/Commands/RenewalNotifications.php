<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\NotificationServices;
use Log;

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
    private $notificationServices;

    /**
     * Create a new command instance.
     *
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
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - RenewalNotifications');
        $data = $this->notificationServices->renewalNotifications();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - RenewalNotifications');
    }
}

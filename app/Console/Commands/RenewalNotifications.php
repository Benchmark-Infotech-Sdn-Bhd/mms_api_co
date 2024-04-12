<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Config;

class RenewalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:RenewalNotifications {database} {type} {cycle}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RenewalNotifications Generation';

    /**
     * @var NotificationServices $notificationServices
     */
    private NotificationServices $notificationServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Class constructor.
     *
     * @param NotificationServices $notificationServices The notification services object.
     * @return void
     */
    public function __construct(NotificationServices $notificationServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->notificationServices = $notificationServices;
        $this->databaseConnectionServices = $databaseConnectionServices;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - Renewal Notifications for Tenant DB - '.$this->argument('database'));
        $data = $this->notificationServices->renewalNotifications($this->argument('type'), $this->argument('cycle'));
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Renewal Notifications for Tenant DB - '.$this->argument('database'));
    }
}

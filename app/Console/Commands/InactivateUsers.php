<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\UserDeactivationServices;
use App\Services\DatabaseConnectionServices;

class InactivateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InactivateUsers {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate Users';

    /**
     * @var UserDeactivationServices $userDeactivationServices
     */
    private UserDeactivationServices $userDeactivationServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Initializes a new instance of the class.
     *
     * @param UserDeactivationServices $userDeactivationServices The audits services object.
     * @return void
     */
    public function __construct(UserDeactivationServices $userDeactivationServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->userDeactivationServices = $userDeactivationServices;
        $this->databaseConnectionServices = $databaseConnectionServices;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - Inactivate Users for Tenant DB - '.$this->argument('database'));
        $data = $this->userDeactivationServices->inactivateUsers();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Inactivate Users for Tenant DB - '.$this->argument('database'));
    }
}

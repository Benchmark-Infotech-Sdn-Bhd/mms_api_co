<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectRecruitmentPostponedServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class UpdateCallingVisaExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateCallingVisaExpiry {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status for calling visa expired postponed workers';

    /**
     * @var DirectRecruitmentPostponedServices $directRecruitmentPostponedServices
     */
    private $directRecruitmentPostponedServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DirectRecruitmentPostponedServices $directRecruitmentPostponedServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->directRecruitmentPostponedServices = $directRecruitmentPostponedServices;
        $this->databaseConnectionServices = $databaseConnectionServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - Update Expiry Status for Tenant DB - '.$this->argument('database'));
        $data = $this->directRecruitmentPostponedServices->updateCallingVisaExpiry();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Update Expiry Status for Tenant DB - '.$this->argument('database'));
    }
}

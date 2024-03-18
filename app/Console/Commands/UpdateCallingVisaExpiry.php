<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectRecruitmentPostponedServices;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseConnectionServices;

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
    private DirectRecruitmentPostponedServices $directRecruitmentPostponedServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Class constructor.
     *
     * @param DirectRecruitmentPostponedServices $directRecruitmentPostponedServices The instance of DirectRecruitmentPostponedServices class.
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
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - Update Expiry Status for Tenant DB - '.$this->argument('database'));
        $data = $this->directRecruitmentPostponedServices->updateCallingVisaExpiry();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Update Expiry Status for Tenant DB - '.$this->argument('database'));
    }
}

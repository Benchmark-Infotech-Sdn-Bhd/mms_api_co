<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectRecruitmentPostponedServices;
use Log;

class UpdateCallingVisaExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateCallingVisaExpiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status for calling visa expired postponed workers';

    /**
     * @var DirectRecruitmentPostponedServices
     */
    private $directRecruitmentPostponedServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DirectRecruitmentPostponedServices $directRecruitmentPostponedServices)
    {
        parent::__construct();
        $this->directRecruitmentPostponedServices = $directRecruitmentPostponedServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Update Expiry Status');
        $data = $this->directRecruitmentPostponedServices->updateCallingVisaExpiry();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Update Expiry Status');
    }
}

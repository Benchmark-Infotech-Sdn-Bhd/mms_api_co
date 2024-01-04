<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DirectRecruitmentPostponedServices;
use Illuminate\Support\Facades\Log;

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
    private DirectRecruitmentPostponedServices $directRecruitmentPostponedServices;

    /**
     * Class constructor.
     *
     * @param DirectRecruitmentPostponedServices $directRecruitmentPostponedServices The instance of DirectRecruitmentPostponedServices class.
     */
    public function __construct(DirectRecruitmentPostponedServices $directRecruitmentPostponedServices)
    {
        parent::__construct();
        $this->directRecruitmentPostponedServices = $directRecruitmentPostponedServices;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Update Expiry Status');
        $this->directRecruitmentPostponedServices->updateCallingVisaExpiry();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Update Expiry Status');
    }
}

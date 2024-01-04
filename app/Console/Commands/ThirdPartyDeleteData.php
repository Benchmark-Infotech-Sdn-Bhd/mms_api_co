<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ThirdPartyLogServices;
use Illuminate\Support\Facades\Log;

class ThirdPartyDeleteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ThirdPartyDeleteData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Third-Party Log Delete Data';

    /**
     * @var ThirdPartyLogServices
     */
    private ThirdPartyLogServices $thirdPartyLogServices;

    /**
     * __construct method
     *
     * This method is the constructor of the class.
     *
     * @param ThirdPartyLogServices $thirdPartyLogServices An instance of the ThirdPartyLogServices class.
     *
     * @return void
     */
    public function __construct(ThirdPartyLogServices $thirdPartyLogServices)
    {
        parent::__construct();
        $this->thirdPartyLogServices = $thirdPartyLogServices;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Third-Party Log Delete Data');
        $data = $this->thirdPartyLogServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Third-Party Log Delete Data');
    }
}

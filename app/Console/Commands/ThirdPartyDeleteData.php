<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ThirdPartyLogServices;
use Log;

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
    private $thirdPartyLogServices;

    /**
     * Create a new command instance.
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
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Third-Party Log Delete Data');
        $data = $this->thirdPartyLogServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Third-Party Log Delete Data');
    }
}

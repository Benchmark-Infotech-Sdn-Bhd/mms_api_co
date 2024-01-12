<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ThirdPartyLogServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class ThirdPartyDeleteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ThirdPartyDeleteData {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Third-Party Log Delete Data';

    /**
     * @var ThirdPartyLogServices $thirdPartyLogServices
     */
    private $thirdPartyLogServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ThirdPartyLogServices $thirdPartyLogServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->thirdPartyLogServices = $thirdPartyLogServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - Third-Party Log Delete Data for Tenant DB - '.$this->argument('database'));
        $data = $this->thirdPartyLogServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Third-Party Log Delete Data for Tenant DB - '.$this->argument('database'));
    }
}

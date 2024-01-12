<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\AuditsServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class AuditsDeleteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AuditsDeleteData {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audits Delete Data';

    /**
     * @var AuditsServices $auditsServices
     */
    private $auditsServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AuditsServices $auditsServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->auditsServices = $auditsServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - Audits Delete Data for Tenant DB - '.$this->argument('database'));
        $data = $this->auditsServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Audits Delete Data for Tenant DB - '.$this->argument('database'));
    }
}

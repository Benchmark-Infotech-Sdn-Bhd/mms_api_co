<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkersServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class WorkerImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WorkerImportFailure {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var WorkersServices $workersServices
     */
    private $workersServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     * @param Workers $workers
     * 
     * @return void
     */
    public function __construct(WorkersServices $workersServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->workersServices = $workersServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - Worker Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->workersServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Worker Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
    }
}

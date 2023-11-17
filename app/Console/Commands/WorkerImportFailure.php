<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkersServices;
use Log;

class WorkerImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WorkerImportFailure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var WorkersServices
     */
    private $workersServices;

    /**
     * Create a new command instance.
     * @param Workers $workers
     * 
     * @return void
     */
    public function __construct(WorkersServices $workersServices)
    {
        parent::__construct();
        $this->workersServices = $workersServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Worker Import Failure Cases Excel Generation');
        $data = $this->workersServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Worker Import Failure Cases Excel Generation');
    }
}

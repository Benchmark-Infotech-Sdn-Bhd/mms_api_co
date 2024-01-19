<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\TotalManagementPayrollServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class TotalManagementPayrollImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TotalManagementPayrollImportFailure {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var TotalManagementPayrollServices $totalManagementPayrollServices
     */
    private $totalManagementPayrollServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     * @param TotalManagementPayrollServices $totalManagementPayrollServices
     * @param DatabaseConnectionServices $databaseConnectionServices
     * 
     * @return void
     */
    public function __construct(TotalManagementPayrollServices $totalManagementPayrollServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->totalManagementPayrollServices = $totalManagementPayrollServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - Total Management Payroll Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
        $this->totalManagementPayrollServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Total Management Payroll Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
    }
}

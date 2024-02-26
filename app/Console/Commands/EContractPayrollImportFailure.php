<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\EContractPayrollServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class EContractPayrollImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EContractPayrollImportFailure {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var EContractPayrollServices $eContractPayrollServices
     */
    private $eContractPayrollServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     * @param EContractPayrollServices $eContractPayrollServices
     * @param DatabaseConnectionServices $databaseConnectionServices
     * 
     * @return void
     */
    public function __construct(EContractPayrollServices $eContractPayrollServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->eContractPayrollServices = $eContractPayrollServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - eContract Payroll Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
        $this->eContractPayrollServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - eContract Payroll Import Failure Cases Excel Generation for Tenant DB - '.$this->argument('database'));
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EContractPayrollServices;
use Log;

class EContractPayrollImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:EContractPayrollImportFailure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var EContractPayrollServices
     */
    private $eContractPayrollServices;

    /**
     * Create a new command instance.
     * @param EContractPayrollServices $eContractPayrollServices
     * 
     * @return void
     */
    public function __construct(EContractPayrollServices $eContractPayrollServices)
    {
        parent::__construct();
        $this->eContractPayrollServices = $eContractPayrollServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - eContract Payroll Import Failure Cases Excel Generation');
        $this->eContractPayrollServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - eContract Payroll Import Failure Cases Excel Generation');
    }
}

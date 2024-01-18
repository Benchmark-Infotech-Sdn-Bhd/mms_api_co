<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TotalManagementPayrollServices;
use Log;

class TotalManagementPayrollImportFailure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TotalManagementPayrollImportFailure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel File For Failure Cases';

    /**
     * @var TotalManagementPayrollServices
     */
    private $totalManagementPayrollServices;

    /**
     * Create a new command instance.
     * @param TotalManagementPayrollServices $totalManagementPayrollServices
     * 
     * @return void
     */
    public function __construct(TotalManagementPayrollServices $totalManagementPayrollServices)
    {
        parent::__construct();
        $this->totalManagementPayrollServices = $totalManagementPayrollServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Total Management Payroll Import Failure Cases Excel Generation');
        $this->totalManagementPayrollServices->prepareExcelForFailureCases();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Total Management Payroll Import Failure Cases Excel Generation');
    }
}

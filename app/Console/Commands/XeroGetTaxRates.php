<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class XeroGetTaxRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetTaxRates {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetTaxRates Generation';

    /**
     * @var InvoiceServices $invoiceServices
     */
    private $invoiceServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InvoiceServices $invoiceServices, DatabaseConnectionServices $databaseConnectionServices)
    {
        parent::__construct();
        $this->invoiceServices = $invoiceServices;
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Tax Rates Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->saveTaxRates();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save Tax Rates Generation for Tenant DB - '.$this->argument('database'));
    }
}

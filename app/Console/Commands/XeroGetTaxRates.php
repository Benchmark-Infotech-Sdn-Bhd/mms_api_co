<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;

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
    private InvoiceServices $invoiceServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * __construct method.
     *
     * Creates a new instance of the class.
     *
     * @param InvoiceServices $invoiceServices An instance of InvoiceServices class.
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
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Tax Rates Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->saveTaxRates();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save Tax Rates Generation for Tenant DB - '.$this->argument('database'));
    }
}

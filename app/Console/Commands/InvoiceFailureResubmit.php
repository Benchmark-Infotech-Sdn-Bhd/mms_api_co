<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;

class InvoiceFailureResubmit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InvoiceFailureResubmit {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoice Failure Resubmit Generation';

    /**
     * @var InvoiceServices $invoiceServices
     */
    private InvoiceServices $invoiceServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * Class constructor.
     *
     * @param InvoiceServices $invoiceServices The invoice services instance.
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
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Failure Invoices Resubmit for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->invoiceReSubmit();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Failure Invoices Resubmit for Tenant DB - '.$this->argument('database'));
    }
}

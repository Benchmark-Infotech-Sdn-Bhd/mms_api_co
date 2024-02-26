<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;

class XeroGetAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetAccounts {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetAccounts Generation';

    /**
     * @var InvoiceServices $invoiceServices
     */
    private InvoiceServices $invoiceServices;

    /**
     * @var DatabaseConnectionServices $databaseConnectionServices
     */
    private $databaseConnectionServices;

    /**
     * __construct
     *
     * @param InvoiceServices $invoiceServices The invoice services object used in the constructor
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
     * Handle the command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Accounts Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->saveAccounts(); 
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save accounts Generation for Tenant DB - '.$this->argument('database'));
    }
}

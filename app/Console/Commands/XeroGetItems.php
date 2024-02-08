<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;

class XeroGetItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetItems {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetItems Generation';

    /**
     * @var InvoiceServices $invoiceServices
     */
    private InvoiceServices $invoiceServices;

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
     * @return void
     */
    public function handle(): void
    {
        $this->databaseConnectionServices->dbConnectQueue($this->argument('database'));
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Items Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->saveItems();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save Items Generation for Tenant DB - '.$this->argument('database'));
    }
}

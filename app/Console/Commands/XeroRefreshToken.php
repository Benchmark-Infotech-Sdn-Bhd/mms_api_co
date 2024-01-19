<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use App\Services\DatabaseConnectionServices;
use Illuminate\Support\Facades\Log;

class XeroRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroRefreshToken {database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroRefreshToken Generation';

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
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Refresh Token Generation for Tenant DB - '.$this->argument('database'));
        $data = $this->invoiceServices->getAccessToken();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Refresh Token Generation for Tenant DB - '.$this->argument('database'));
    }
}

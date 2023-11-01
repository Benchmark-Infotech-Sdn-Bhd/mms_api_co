<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use Log;

class XeroGetAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetAccounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetAccounts Generation';

    /**
     * @var InvoiceServices
     */
    private $invoiceServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InvoiceServices $invoiceServices)
    {
        parent::__construct();
        $this->invoiceServices = $invoiceServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Accounts Generation');
        $data = $this->invoiceServices->saveAccounts();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save accounts Generation');
    }
}

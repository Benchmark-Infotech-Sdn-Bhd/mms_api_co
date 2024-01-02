<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use Log;

class InvoiceFailureResubmit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InvoiceFailureResubmit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invoice Failure Resubmit Generation';

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
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Failure Invoices Resubmit');
        $data = $this->invoiceServices->invoiceReSubmit();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Failure Invoices Resubmit');
    }
}

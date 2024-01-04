<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use Illuminate\Support\Facades\Log;

class XeroGetTaxRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetTaxRates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetTaxRates Generation';

    /**
     * @var InvoiceServices
     */
    private InvoiceServices $invoiceServices;

    /**
     * __construct method.
     *
     * Creates a new instance of the class.
     *
     * @param InvoiceServices $invoiceServices An instance of InvoiceServices class.
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
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Tax Rates Generation');
        $this->invoiceServices->saveTaxRates();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save Tax Rates Generation');
    }
}

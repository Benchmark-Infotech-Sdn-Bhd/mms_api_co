<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use Illuminate\Support\Facades\Log;

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
    private InvoiceServices $invoiceServices;

    /**
     * __construct
     *
     * @param InvoiceServices $invoiceServices The invoice services object used in the constructor
     *
     * @return void
     */
    public function __construct(InvoiceServices $invoiceServices)
    {
        parent::__construct();
        $this->invoiceServices = $invoiceServices;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Accounts Generation');
        $this->invoiceServices->saveAccounts();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save accounts Generation');
    }
}

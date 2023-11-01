<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\InvoiceServices;
use Log;

class XeroGetItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeroGetItems';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XeroGetItems Generation';

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
        Log::channel('cron_activity_logs')->info('Cron Job Started - InvoiceServices Save Items Generation');
        $data = $this->invoiceServices->saveItems();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - InvoiceServices Save Items Generation');
    }
}

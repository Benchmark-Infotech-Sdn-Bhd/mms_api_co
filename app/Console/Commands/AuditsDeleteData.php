<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\AuditsServices;
use Log;

class AuditsDeleteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AuditsDeleteData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audits Delete Data';

    /**
     * @var AuditsServices
     */
    private $auditsServices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AuditsServices $auditsServices)
    {
        parent::__construct();
        $this->auditsServices = $auditsServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Audits Delete Data');
        $data = $this->auditsServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Audits Delete Data');
    }
}

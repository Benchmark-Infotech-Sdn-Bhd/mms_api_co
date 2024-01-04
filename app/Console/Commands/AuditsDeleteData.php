<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\AuditsServices;
use Illuminate\Support\Facades\Log;

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
    private AuditsServices $auditsServices;

    /**
     * Initializes a new instance of the class.
     *
     * @param AuditsServices $auditsServices The audits services object.
     * @return void
     */
    public function __construct(AuditsServices $auditsServices)
    {
        parent::__construct();
        $this->auditsServices = $auditsServices;
    }

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::channel('cron_activity_logs')->info('Cron Job Started - Audits Delete Data');
        $this->auditsServices->delete();
        Log::channel('cron_activity_logs')->info('Cron Job Ended - Audits Delete Data');
    }
}

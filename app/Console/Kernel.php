<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\XeroRefreshToken::class,
        Commands\XeroGetTaxRates::class,
        Commands\XeroGetAccounts::class,
        Commands\XeroGetItems::class,
        Commands\AuditsDeleteData::class,
        Commands\WorkerImportFailure::class,
        Commands\RenewalNotifications::class,
        Commands\UpdateCallingVisaExpiry::class,
        Commands\ThirdPartyDeleteData::class,
        Commands\InvoiceFailureResubmit::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:XeroRefreshToken')->everyFifteenMinutes();
        $schedule->command('command:XeroGetTaxRates')->cron('0 */6 * * *');
        $schedule->command('command:XeroGetAccounts')->cron('0 */6 * * *');
        $schedule->command('command:XeroGetItems')->cron('0 */6 * * *');
        $schedule->command('command:AuditsDeleteData')->cron('0 0 * * *');
        $schedule->command('command:WorkerImportFailure')->everyTwoMinutes();
        $schedule->command('command:RenewalNotifications')->cron('0 0 * * *');
        $schedule->command('command:UpdateCallingVisaExpiry')->cron('0 0 * * *');
        $schedule->command('command:ThirdPartyDeleteData')->cron('0 0 * * *');
        $schedule->command('command:InvoiceFailureResubmit')->everyThirtyMinutes();
    }
}

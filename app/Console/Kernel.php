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
     * Schedule the commands to run.
     *
     * This method is used to schedule various commands to run at specific intervals using the given `$schedule` object.
     *
     * @param Schedule $schedule The scheduler object used to schedule commands.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('command:XeroRefreshToken')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('command:XeroGetTaxRates')->everySixHours();
        $schedule->command('command:XeroGetAccounts')->everySixHours();
        $schedule->command('command:XeroGetItems')->everySixHours();
        $schedule->command('command:AuditsDeleteData')->dailyAt('00:01');
        $schedule->command('command:WorkerImportFailure')->everyTwoMinutes()->withoutOverlapping();
        $schedule->command('command:RenewalNotifications')->dailyAt('00:01');
        $schedule->command('command:UpdateCallingVisaExpiry')->dailyAt('00:01');
        $schedule->command('command:ThirdPartyDeleteData')->dailyAt('00:01');
        $schedule->command('command:InvoiceFailureResubmit')->everyThirtyMinutes()->withoutOverlapping();
    }
}

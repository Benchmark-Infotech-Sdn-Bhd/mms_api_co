<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;

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
        Commands\InvoiceFailureResubmit::class,
        Commands\TotalManagementPayrollImportFailure::class,
        Commands\EContractPayrollImportFailure::class,
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
        $schedule->command('command:XeroRefreshToken '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('command:XeroGetTaxRates '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everySixHours();
        $schedule->command('command:XeroGetAccounts '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everySixHours();
        $schedule->command('command:XeroGetItems '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everySixHours();
        $schedule->command('command:AuditsDeleteData '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->dailyAt('00:01');
        $schedule->command('command:WorkerImportFailure '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everyTwoMinutes()->withoutOverlapping();
        $schedule->command('command:RenewalNotifications '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->weekly();
        $schedule->command('command:UpdateCallingVisaExpiry '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->dailyAt('00:01');
        $schedule->command('command:ThirdPartyDeleteData '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->dailyAt('00:01');
        $schedule->command('command:InvoiceFailureResubmit '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('command:TotalManagementPayrollImportFailure '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everyTwoMinutes()->withoutOverlapping();
        $schedule->command('command:EContractPayrollImportFailure '.Config::get('services.SUB_DOMAIN_DB_NAME_ONE'))->everyTwoMinutes()->withoutOverlapping();
    }
}

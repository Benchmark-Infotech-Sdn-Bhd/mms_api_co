<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Services\DatabaseConnectionServices; 

class RunnerNotificationMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;
    private mixed $user;
    private mixed $message;
    private const CRON_ACTIVITY_LOGS = 'cron_activity_logs';
    private $dbName;

    /**
     * Constructs a new instance of the class.
     *
     * @param $user - The user object to associate with the instance.
     * @param $message - The message to be assigned to the instance.
     */
    public function __construct($dbName, $user, $message)
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Handles the notification mail process for runners.
     *
     * Logs the start of the process and prepares the mail data.
     * If the email address is valid, sends the notification mail using the prepared data.
     * Logs the completion or failure of the process based on the success of the mail sent.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices)
    {   
        Log::channel(self::CRON_ACTIVITY_LOGS)->info('Runners notification mail process started');
        $databaseConnectionServices->dbConnectQueue($this->dbName);

        $input = $this->prepareMailData();

        if ($this->isValidEmail($input['email'])) {
            Mail::send('email.RunnerNotificationMail', ['params' => $input], function ($message) use ($input) {
                $message->to($input['email'])
                    ->subject($input['subject'])
                    ->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
            });
            Log::channel(self::CRON_ACTIVITY_LOGS)->info('Runners notification mail process completed');
        } else {
            Log::channel(self::CRON_ACTIVITY_LOGS)->info('Runners notification mail process failed due to incorrect email id');
        }
    }

    /**
     * Prepare mail data for sending notification email.
     *
     * @return array - An array containing mail data including subject, name, email,
     * mail_subject, and message.
     */
    private function prepareMailData(): array
    {
        return [
            'subject' => "Notification Mail",
            'name' => $this->user['name'],
            'email' => $this->user['email'],
            'mail_subject' => 'You have new notifications on Dispatch',
            'message' => $this->message
        ];
    }

    /**
     * Validates whether the given email address is valid.
     *
     * @param string $email - The email address to be validated.
     *
     * @return bool - Returns true if the email address is valid, false otherwise.
     */
    public function isValidEmail($email)
    {
        return isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

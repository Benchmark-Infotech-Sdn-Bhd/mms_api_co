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

    private $dbName;
    public $user;
    public $message;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dbName, $user, $message)
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices)
    {   
        $databaseConnectionServices->dbConnectQueue($this->dbName);
        
        Log::channel('cron_activity_logs')->info('Runners notification mail process started');

        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = $this->user['email'];
        $input['mail_subject'] = 'You have new notifications on Dispatch';
        $input['message'] = $this->message;

        if($this->emailValidation($input['email'])){
            try{
                Mail::send('email.RunnerNotificationMail', ['params' => $input], function ($message) use ($input) {
                    $message->to($input['email'])
                        ->subject($input['subject']);
                    $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
                });
                Log::channel('cron_activity_logs')->info('Runners notification mail process completed');
            } catch(Exception $e) {
                Log::channel('cron_activity_logs')->info('Error - ' . print_r($e->getMessage(), true));
            }
            
        }else{
            Log::channel('cron_activity_logs')->info('Runners notification mail process failed due to incorrect email id');
        }
        
    }
    /**
     * Email Validation
     */
    public function emailValidation($email)
    {
        if (isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}

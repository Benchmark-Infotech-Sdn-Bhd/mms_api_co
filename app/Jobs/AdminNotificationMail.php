<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AdminNotificationMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $message;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        Log::info('Admin notification mail process started');

        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = $this->user['email'];
        $input['message'] = $this->message;

        if($this->emailValidation($input['email'])){
            Mail::send('email.AdminNotificationMail', ['params' => $input], function ($message) use ($input) {
                $message->to($input['email'])
                    ->subject($input['subject']);
                $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
            });
            Log::info('Admin notification mail process completed');
        }else{
            Log::info('Admin notification mail process failed due to incorrect email id');
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

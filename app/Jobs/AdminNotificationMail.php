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

    public $name;
    public $email;
    public $message;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $email, $message)
    {
        $this->name = $name;
        $this->email = $email;
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
        $input['name'] = $this->name;
        $input['email'] = $this->email;
        $input['message'] = $this->message;

        Mail::send('email.AdminNotificationMail', ['params' => $input], function ($message) use ($input) {
            $message->to($input['email'])
                ->subject($input['subject']);
            $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
        });
        Log::info('Admin notification mail process completed');
    }
}

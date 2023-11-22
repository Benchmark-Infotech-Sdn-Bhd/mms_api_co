<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class EmployerNotificationMail implements ShouldQueue
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
        Log::info('Employer notification mail process started' );

        $mailMessage = $this->message;
        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = 'enock@codtesma.com'; //$this->user['email'];
        $input['mail_subject'] = 'You have new notifications on ';
        $input['mail_subject'] .= isset($mailMessage['fomemaRenewal']['mail_message']) ? 'Fomema/' : '';
        $input['mail_subject'] .= isset($mailMessage['passportRenewal']['mail_message']) ? 'Passport/' : '';
        $input['mail_subject'] .= isset($mailMessage['plksRenewal']['mail_message']) ? 'PLKS/' : '';
        $input['mail_subject'] .= isset($mailMessage['callingVisaRenewal']['mail_message']) ? 'Calling Visa/' : '';
        $input['mail_subject'] .= isset($mailMessage['specialPassRenewal']['mail_message']) ? 'Special Pass/' : '';
        $input['mail_subject'] .= isset($mailMessage['insuranceRenewal']['mail_message']) ? 'Insurance/' : '';
        $input['mail_subject'] .= isset($mailMessage['entryVisaRenewal']['mail_message']) ? 'Entry Visa/' : '';
        $input['mail_subject'] .= ( isset($mailMessage['serviceAgreement']) && !empty($mailMessage['serviceAgreement']) ) ? 'Service Agreement' : '';
        $input['mail_subject'] = rtrim($input['mail_subject'], '/');
        $input['message'] = $this->message;

        if($this->emailValidation($input['email'])){
            Mail::send('email.EmployerNotificationMail', ['params' => $input], function ($message) use ($input) {
                $message->to($input['email'])
                    ->subject($input['subject']);
                $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
            });
    
            Log::info('Employer notification mail process completed' );
        }else{
            Log::info('Employer notification mail process failed due to incorrect email id' );
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

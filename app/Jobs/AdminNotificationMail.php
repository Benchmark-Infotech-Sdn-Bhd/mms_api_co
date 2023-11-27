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

        $mailMessage = $this->message;
        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = $this->user['email'];
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

        $input['passport_attachment_filename'] = isset($mailMessage['passportRenewal']['attachment_filename']) ? $mailMessage['passportRenewal']['attachment_filename'] : '';
        $input['passport_attachment_file'] = isset($mailMessage['passportRenewal']['attachment_file']) ? $mailMessage['passportRenewal']['attachment_file'] : [];

        $input['insurance_attachment_filename'] = isset($mailMessage['insuranceRenewal']['attachment_filename']) ? $mailMessage['insuranceRenewal']['attachment_filename'] : '';
        $input['insurance_attachment_file'] = isset($mailMessage['insuranceRenewal']['attachment_file']) ? $mailMessage['insuranceRenewal']['attachment_file'] : [];

        $input['fomema_attachment_filename'] = isset($mailMessage['fomemaRenewal']['attachment_filename']) ? $mailMessage['fomemaRenewal']['attachment_filename'] : '';
        $input['fomema_attachment_file'] = isset($mailMessage['fomemaRenewal']['attachment_file']) ? $mailMessage['fomemaRenewal']['attachment_file'] : [];

        $input['plks_attachment_filename'] = isset($mailMessage['plksRenewal']['attachment_filename']) ? $mailMessage['plksRenewal']['attachment_filename'] : '';
        $input['plks_attachment_file'] = isset($mailMessage['plksRenewal']['attachment_file']) ? $mailMessage['plksRenewal']['attachment_file'] : [];

        $input['callingvisa_attachment_filename'] = isset($mailMessage['callingVisaRenewal']['attachment_filename']) ? $mailMessage['callingVisaRenewal']['attachment_filename'] : '';
        $input['callingvisa_attachment_file'] = isset($mailMessage['callingVisaRenewal']['attachment_file']) ? $mailMessage['callingVisaRenewal']['attachment_file'] : [];

        $input['specialpass_attachment_filename'] = isset($mailMessage['specialPassRenewal']['attachment_filename']) ? $mailMessage['specialPassRenewal']['attachment_filename'] : '';
        $input['specialpass_attachment_file'] = isset($mailMessage['specialPassRenewal']['attachment_file']) ? $mailMessage['specialPassRenewal']['attachment_file'] : [];

        $input['entryvisa_attachment_filename'] = isset($mailMessage['entryVisaRenewal']['attachment_filename']) ? $mailMessage['entryVisaRenewal']['attachment_filename'] : '';
        $input['entryvisa_attachment_file'] = isset($mailMessage['entryVisaRenewal']['attachment_file']) ? $mailMessage['entryVisaRenewal']['attachment_file'] : [];

        if($this->emailValidation($input['email'])){
            Mail::send('email.AdminNotificationMail', ['params' => $input], function ($message) use ($input) {
                $message->to($input['email'])
                    ->subject($input['subject']);
                $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
                
                if(!empty($input['passport_attachment_filename']) && !empty($input['passport_attachment_file'])){
                    $message->attachData($input['passport_attachment_file'], $input['passport_attachment_filename']);
                }
                if(!empty($input['insurance_attachment_filename']) && !empty($input['insurance_attachment_file'])){
                    $message->attachData($input['insurance_attachment_file'], $input['insurance_attachment_filename']);
                }
                if(!empty($input['fomema_attachment_filename']) && !empty($input['fomema_attachment_file'])){
                    $message->attachData($input['fomema_attachment_file'], $input['fomema_attachment_filename']);
                }
                if(!empty($input['plks_attachment_filename']) && !empty($input['plks_attachment_file'])){
                    $message->attachData($input['plks_attachment_file'], $input['plks_attachment_filename']);
                }
                if(!empty($input['callingvisa_attachment_filename']) && !empty($input['callingvisa_attachment_file'])){
                    $message->attachData($input['callingvisa_attachment_file'], $input['callingvisa_attachment_filename']);
                }
                if(!empty($input['specialpass_attachment_filename']) && !empty($input['specialpass_attachment_file'])){
                    $message->attachData($input['specialpass_attachment_file'], $input['specialpass_attachment_filename']);
                }
                if(!empty($input['entryvisa_attachment_filename']) && !empty($input['entryvisa_attachment_file'])){
                    $message->attachData($input['entryvisa_attachment_file'], $input['entryvisa_attachment_filename']);
                }
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

<?php

namespace App\Services;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationMail;
use App\Mail\ForgotPwdMail;
use App\Mail\InvoiceResubmissionFailedMail;
use App\Mail\Welcome;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;

class EmailServices
{
    public const MESSAGE_SENDING_MAIL_INFO = 'Sending mail - mail service ';

    /**
     * @param $name The name of the user
     * @param $email The email of the user
     * @param $password The password of the user
     * @return boolean Returns true if email was send successfully, otherwise false
     */
    public function sendRegistrationMail($name,$email,$password)
    {
        Mail::to($email)->send(new RegistrationMail($name,$email,$password));
        return true;
    }

    /**
     * @param $params The params data containing user name, email, token, url
     * @return boolean Returns true if email was send successfully, otherwise false
     */
    public function sendForgotPasswordMail($params)
    {
        Mail::to($params['email'])->send(new ForgotPwdMail($params));
        return true;
    }

    /**
     * @param $params The params data containing company_name, company_email, reference_number
     * @return boolean Returns true if email was send successfully, otherwise false
     */
    public function sendInvoiceResubmissionFailedMail($params)
    {
        Log::channel('cron_activity_logs')->info(self::MESSAGE_SENDING_MAIL_INFO . print_r($params));
        Mail::to($params['email'])->send(new InvoiceResubmissionFailedMail($params));
        return true;
    }
}

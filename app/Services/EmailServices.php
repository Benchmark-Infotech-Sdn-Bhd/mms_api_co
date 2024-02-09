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
     * @param $name
     * @param $email
     * @param $password
     * @return mixed | boolean
     */
    public function sendRegistrationMail($name,$email,$password)
    {
        Mail::to($email)->send(new RegistrationMail($name,$email,$password));
        return true;
    }

    /**
     * @param $params
     * @return mixed | boolean
     */
    public function sendForgotPasswordMail($params)
    {
        Mail::to($params['email'])->send(new ForgotPwdMail($params));
        return true;
    }

    /**
     * @param $params
     * @return mixed | boolean
     */
    public function sendInvoiceResubmissionFailedMail($params)
    {
        Log::channel('cron_activity_logs')->info(self::MESSAGE_SENDING_MAIL_INFO . print_r($params));
        Mail::to($params['email'])->send(new InvoiceResubmissionFailedMail($params));
        return true;
    }
}

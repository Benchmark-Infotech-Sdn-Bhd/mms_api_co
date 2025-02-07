<?php

namespace App\Services;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationMail;
use App\Mail\ForgotPwdMail;
use App\Mail\Welcome;
use Illuminate\Mail\Message;

class EmailServices
{
    public function __construct()
    {
    }
    /**
     * @return mixed | boolean
     */
    public function sendWelcomeMail()
    {
        $to_email = 'test@gmail.com';
        Mail::to($to_email)->send(new Welcome());
        return true;
    }
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
}

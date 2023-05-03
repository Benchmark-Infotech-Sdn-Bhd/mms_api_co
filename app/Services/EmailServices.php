<?php

namespace App\Services;
use Illuminate\Support\Facades\Mail;
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
}

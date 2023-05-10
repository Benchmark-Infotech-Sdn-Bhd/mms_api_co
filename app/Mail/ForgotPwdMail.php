<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class ForgotPwdMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = Config::get('services.app_url');
        return $this->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'))->subject('Password Reset Instructions')->view('email.ForgotMail')->with([
            'name' => $this->name,
            'link' => $link,
        ]);
    }
}

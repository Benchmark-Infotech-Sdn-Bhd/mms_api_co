<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $email;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$email,$password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = Config::get('services.FRONTEND_URL');
        return $this->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'))->subject('Registration of new Account')->view('email.RegistrationMail')->with([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'link' => $link,
        ]);
    }
}

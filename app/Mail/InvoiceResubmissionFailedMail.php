<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class InvoiceResubmissionFailedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $params;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'))->subject('Registration of new Account')->view('email.InvoiceResubmissionFailedMail')->with([
            'company_name' => $this->params['company_name'],
            'company_email' => $this->params['company_email'],
            'reference_number' => $this->params['reference_number'],
        ]);
    }
}

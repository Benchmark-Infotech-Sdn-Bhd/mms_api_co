<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as BaseExcel;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PassportRenewalExport;
use App\Exports\InsuranceRenewalExport;
use App\Exports\FomemaRenewalExport;
use App\Exports\PlksRenewalExport;
use App\Exports\CallingVisaRenewalExport;
use App\Exports\SpecialPassRenewalExport;
use App\Exports\EntryVisaRenewalExport;
use App\Exports\ServiceAgreementExport;

class AdminNotificationMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;
    private mixed $user;
    private mixed $message;

    /**
     * Class constructor.
     *
     * @param mixed $user The user for the message.
     * @param string $message The message content.
     * @return void
     */
    public function __construct($user, $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Handle the admin notification mail process.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('cron_activity_logs')->info('Admin notification mail process started');

        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = $this->user['email'];
        $input['message'] = $this->message;
        $input['mail_subject'] = $this->formMailSubject($this->message);
        $input = $this->formMailAttachments($this->message, $input);

        if($this->emailValidation($input['email'])) {
            Mail::send('email.AdminNotificationMail', ['params' => $input], function ($message) use ($input) {
                $message->to($input['email'])->subject($input['subject']);
                $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));
                $this->handleAttachmentsInMail($message, $input);
            });
            Log::channel('cron_activity_logs')->info('Admin notification mail process completed');
        } else {
            Log::channel('cron_activity_logs')->info('Admin notification mail process failed due to incorrect email id');
        }
    }

    /**
     * Generates the subject line for a notification email.
     *
     * @param array $mailMessage An array containing the mail message data.
     *                           The keys of the array are:
     *                           - fomemaRenewal: Indicates whether there is a FOMEMA renewal notification.
     *                           - passportRenewal: Indicates whether there is a passport renewal notification.
     *                           - plksRenewal: Indicates whether there is a PLKS renewal notification.
     *                           - callingVisaRenewal: Indicates whether there is a calling visa renewal notification.
     *                           - specialPassRenewal: Indicates whether there is a special pass renewal notification.
     *                           - insuranceRenewal: Indicates whether there is an insurance renewal notification.
     *                           - entryVisaRenewal: Indicates whether there is an entry visa renewal notification.
     *                           - serviceAgreement: Contains the service agreement notification message.
     *
     * @return string The generated subject line for the notification email.
     */
    private function formMailSubject($mailMessage)
    {
        $subjectDetails = [
            'fomemaRenewal' => 'Fomema/',
            'passportRenewal' => 'Passport/',
            'plksRenewal' => 'PLKS/',
            'callingVisaRenewal' => 'Calling Visa/',
            'specialPassRenewal' => 'Special Pass/',
            'insuranceRenewal' => 'Insurance/',
            'entryVisaRenewal' => 'Entry Visa/'
        ];
        $subject = 'You have new notifications on ';
        foreach ($subjectDetails as $key => $value) {
            if (isset($mailMessage[$key]['mail_message'])) {
                $subject .= $value;
            }
        }
        $subject .= (!empty($mailMessage['serviceAgreement']['mail_message'])) ? 'Service Agreement' : '';
        return rtrim($subject, '/');
    }

    /**
     * Form mail attachments based on given mail message and input data.
     *
     * @param array $mailMessage The mail message data.
     * @param array $input The input data.
     * @return array The modified input data with mail attachments.
     */
    private function formMailAttachments($mailMessage, $input)
    {
        $attachmentDetails = [
            'passportRenewal' => PassportRenewalExport::class,
            'insuranceRenewal' => InsuranceRenewalExport::class,
            'fomemaRenewal' => FomemaRenewalExport::class,
            'plksRenewal' => PlksRenewalExport::class,
            'callingVisaRenewal' => CallingVisaRenewalExport::class,
            'specialPassRenewal' => SpecialPassRenewalExport::class,
            'entryVisaRenewal' => EntryVisaRenewalExport::class,
            'serviceAgreement' => ServiceAgreementExport::class,
        ];
        foreach ($attachmentDetails as $key => $value) {
            if (!empty($mailMessage[$key]['company_id'])) {
                $input[$key . '_attachment_filename'] = $key . ".xlsx";
                $input[$key . '_attachment_file'] = Excel::raw(new $value($mailMessage[$key]['company_id']), BaseExcel::XLSX);
            }
        }
        return $input;
    }

    /**
     * Handle attachments in mail based on given message and input data.
     *
     * @param Message $message The mail message instance.
     * @param array $input The input data.
     */
    private function handleAttachmentsInMail($message, $input)
    {
        foreach ($input as $key => $value) {
            if (str_contains($key, 'attachment_filename') && isset($input[str_replace('_filename', '_file', $key)])) {
                $message->attachData($input[str_replace('_filename', '_file', $key)], $value);
            }
        }
    }

    /**
     * Email Validation
     */
    public function emailValidation($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

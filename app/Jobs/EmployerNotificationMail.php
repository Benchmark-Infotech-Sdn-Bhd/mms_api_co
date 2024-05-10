<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
use App\Services\DatabaseConnectionServices;

class EmployerNotificationMail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $dbName;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;
    private mixed $user;
    private mixed $message;

    const RENEWAL_TYPE = ['fomemaRenewal', 'passportRenewal', 'plksRenewal', 'callingVisaRenewal', 'specialPassRenewal', 'insuranceRenewal', 'entryVisaRenewal'];

    const RENEWAL_EXPORTS = [
        'fomemaRenewal' => FomemaRenewalExport::class,
        'passportRenewal' => PassportRenewalExport::class,
        'plksRenewal' => PlksRenewalExport::class,
        'callingVisaRenewal' => CallingVisaRenewalExport::class,
        'specialPassRenewal' => SpecialPassRenewalExport::class,
        'insuranceRenewal' => InsuranceRenewalExport::class,
        'entryVisaRenewal' => EntryVisaRenewalExport::class,
        'serviceAgreement' => ServiceAgreementExport::class
    ];

    const SERVICE_AGREEMENT = 'serviceAgreement';

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed $user The user representing the user object. It can be of any type.
     * @param string $message The message representing the message to be set. It must be a string.
     *
     * @return void
     */
    public function __construct($dbName, $user, $message)
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Handles the employer notification mail process.
     *
     * This method logs the start of the mail process, builds the mail input, validates the email,
     * and sends the mail. Finally, it logs the completion of the mail process or the failure due
     * to an incorrect email id.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices)
    {
        $databaseConnectionServices->dbConnectQueue($this->dbName);
        Log::info('Employer notification mail process started', ['channel' => 'cron_activity_logs']);

        $mailMessage = $this->message;

        $input = $this->buildMailInput($mailMessage);

        if ($this->emailValidation($input['email'])) {
            $this->sendMail($input);
            Log::info('Employer notification mail process completed', ['channel' => 'cron_activity_logs']);
        } else {
            Log::info('Employer notification mail process failed due to incorrect email id', ['channel' => 'cron_activity_logs']);
        }
    }

    /**
     * Builds the input for a mail message.
     *
     * @param array $mailMessage The mail message data. It must be an array representing the mail message.
     *
     * @return array The built mail input array.
     */
    private function buildMailInput($mailMessage)
    {
        $input = [];
        $input['subject'] = "Notification Mail";
        $input['name'] = $this->user['name'];
        $input['email'] = $this->user['email'];
        $input['mail_subject'] = $this->buildMailSubject($mailMessage);
        $input['message'] = $this->message;

        $this->buildExcelAttachments($input, $mailMessage);
        
        return $input;
    }

    /**
     * Builds the subject line for the email notification.
     *
     * @param array $mailMessage The mail message containing the notification details.
     *
     * @return string The subject line for the email notification.
     */
    private function buildMailSubject($mailMessage)
    {
        $subject = 'You have new notifications on ';

        foreach (self::RENEWAL_TYPE as $type) {
            $subject .= isset($mailMessage[$type]['mail_message']) ? ucwords($type) . '/' : '';
        }

        $subject .= (!empty($mailMessage['serviceAgreement']['mail_message'])) ? 'Service Agreement' : '';
        return rtrim($subject, '/');
    }

    /**
     * Builds the Excel attachments for the mail message.
     *
     * @param array $input The input data containing the attachments. It is passed by reference and modified within the method.
     * @param array $mailMessage The mail message data containing the company IDs for each type of attachment.
     *
     * @return void
     */
    private function buildExcelAttachments(&$input, $mailMessage)
    {
        foreach (self::RENEWAL_EXPORTS as $type => $exportClass) {
            if (!empty($mailMessage[$type]['company_id'])) {
                $filename = strtolower($type) . ".xlsx";
                $input["{$type}_filename"] = $filename;
                if($type == self::SERVICE_AGREEMENT) {
                    // $input["{$type}_file"] = Excel::raw(new $exportClass($mailMessage[$type]['company_id'], $mailMessage[$type]['notification_type'], $mailMessage[$type]['duration'], $mailMessage[$type]['modules']), BaseExcel::XLSX);
                } else {
                    $input["{$type}_file"] = Excel::raw(new $exportClass($mailMessage[$type]['company_id'], $mailMessage[$type]['notification_type'], $mailMessage[$type]['duration']), BaseExcel::XLSX);
                } 
            }
        }
    }

    /**
     * Sends an email using the Mail facade in Laravel.
     *
     * @param array $input The input data for the email. It must be an associative array with the following keys:
     *                    - email: The recipient's email address. It must be a string.
     *                    - subject: The subject of the email. It must be a string.
     *                    - Any additional key-value pairs representing the attachment's file name and content. The keys
     *                      must follow the format "{type}_filename" and the values must be the actual file content.
     *                      The supported types are defined in the RENEWAL_EXPORTS constant.
     *
     * @return void
     */
    private function sendMail($input)
    {
        Mail::send('email.EmployerNotificationMail', ['params' => $input], function ($message) use ($input) {
            $message->to($input['email'])
                ->subject($input['subject']);
            $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));

            foreach (self::RENEWAL_EXPORTS as $type => $exportClass) {
                if (isset($input["{$type}_filename"]) && isset($input["{$type}_file"])) {
                    $message->attachData($input["{$type}_file"], $input["{$type}_filename"]);
                }
            }
        });
    }

    /**
     * Validates an email address.
     *
     * @param string $email The email address to be validated. It must be a string.
     *
     * @return bool Returns true if the email address is valid, false otherwise.
     */
    public function emailValidation($email)
    {
        return isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

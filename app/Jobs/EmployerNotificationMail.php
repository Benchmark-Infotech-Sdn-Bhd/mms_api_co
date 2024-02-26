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
    public $user;
    public $message;
    public $timeout = 7200; // 2 hours
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices)
    {   
        $databaseConnectionServices->dbConnectQueue($this->dbName);
        
        Log::channel('cron_activity_logs')->info('Employer notification mail process started' );

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
        $input['mail_subject'] .= ( isset($mailMessage['serviceAgreement']['mail_message']) && !empty($mailMessage['serviceAgreement']['mail_message']) ) ? 'Service Agreement' : '';
        $input['mail_subject'] = rtrim($input['mail_subject'], '/');
        $input['message'] = $this->message;

        if(isset($mailMessage['passportRenewal']['company_id']) && !empty($mailMessage['passportRenewal']['company_id'])){
            $input['passport_attachment_filename'] = "passportRenewal.xlsx";
            $input['passport_attachment_file'] = Excel::raw(new PassportRenewalExport($mailMessage['passportRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['insuranceRenewal']['company_id']) && !empty($mailMessage['insuranceRenewal']['company_id'])){
            $input['insurance_attachment_filename'] = "insuranceRenewal.xlsx";
            $input['insurance_attachment_file'] = Excel::raw(new InsuranceRenewalExport($mailMessage['insuranceRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['fomemaRenewal']['company_id']) && !empty($mailMessage['fomemaRenewal']['company_id'])){
            $input['fomema_attachment_filename'] = "fomemaRenewal.xlsx";
            $input['fomema_attachment_file'] = Excel::raw(new FomemaRenewalExport($mailMessage['fomemaRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['plksRenewal']['company_id']) && !empty($mailMessage['plksRenewal']['company_id'])){
            $input['plks_attachment_filename'] = "plksRenewal.xlsx";
            $input['plks_attachment_file'] = Excel::raw(new PlksRenewalExport($mailMessage['plksRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['callingVisaRenewal']['company_id']) && !empty($mailMessage['callingVisaRenewal']['company_id'])){
            $input['callingvisa_attachment_filename'] = "callingVisaRenewal.xlsx";
            $input['callingvisa_attachment_file'] = Excel::raw(new CallingVisaRenewalExport($mailMessage['callingVisaRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['specialPassRenewal']['company_id']) && !empty($mailMessage['specialPassRenewal']['company_id'])){
            $input['specialpass_attachment_filename'] = "specialPassRenewal.xlsx";
            $input['specialpass_attachment_file'] = Excel::raw(new SpecialPassRenewalExport($mailMessage['specialPassRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['entryVisaRenewal']['company_id']) && !empty($mailMessage['entryVisaRenewal']['company_id'])){
            $input['entryvisa_attachment_filename'] = "entryVisaRenewal.xlsx";
            $input['entryvisa_attachment_file'] = Excel::raw(new EntryVisaRenewalExport($mailMessage['entryVisaRenewal']['company_id']), BaseExcel::XLSX);
        }

        if(isset($mailMessage['serviceAgreement']['company_id']) && !empty($mailMessage['serviceAgreement']['company_id'])){
            $input['serviceagreement_attachment_filename'] = "serviceAgreement.xlsx";
            $input['serviceagreement_attachment_file'] = Excel::raw(new ServiceAgreementExport($mailMessage['serviceAgreement']['company_id']), BaseExcel::XLSX);
        }

        if($this->emailValidation($input['email'])){
            try{
                Mail::send('email.EmployerNotificationMail', ['params' => $input], function ($message) use ($input) {
                    $message->to($input['email'])
                        ->subject($input['subject']);
                    $message->from(Config::get('services.mail_from_address'), Config::get('services.mail_from_name'));

                    if(isset($input['passport_attachment_filename']) && isset($input['passport_attachment_file'])){
                        $message->attachData($input['passport_attachment_file'], $input['passport_attachment_filename']);
                    }
                    if(isset($input['insurance_attachment_filename']) && isset($input['insurance_attachment_file'])){
                        $message->attachData($input['insurance_attachment_file'], $input['insurance_attachment_filename']);
                    }
                    if(isset($input['fomema_attachment_filename']) && isset($input['fomema_attachment_file'])){
                        $message->attachData($input['fomema_attachment_file'], $input['fomema_attachment_filename']);
                    }
                    if(isset($input['plks_attachment_filename']) && isset($input['plks_attachment_file'])){
                        $message->attachData($input['plks_attachment_file'], $input['plks_attachment_filename']);
                    }
                    if(isset($input['callingvisa_attachment_filename']) && isset($input['callingvisa_attachment_file'])){
                        $message->attachData($input['callingvisa_attachment_file'], $input['callingvisa_attachment_filename']);
                    }
                    if(isset($input['specialpass_attachment_filename']) && isset($input['specialpass_attachment_file'])){
                        $message->attachData($input['specialpass_attachment_file'], $input['specialpass_attachment_filename']);
                    }
                    if(isset($input['entryvisa_attachment_filename']) && isset($input['entryvisa_attachment_file'])){
                        $message->attachData($input['entryvisa_attachment_file'], $input['entryvisa_attachment_filename']);
                    }
                    if(isset($input['serviceagreement_attachment_filename']) && isset($input['serviceagreement_attachment_file'])){
                        $message->attachData($input['serviceagreement_attachment_file'], $input['serviceagreement_attachment_filename']);
                    }

                });
        
                Log::channel('cron_activity_logs')->info('Employer notification mail process completed' );
            } catch(Exception $e) {
                Log::channel('cron_activity_logs')->info('Error - ' . print_r($e->getMessage(), true));
            }
        }else{
            Log::channel('cron_activity_logs')->info('Employer notification mail process failed due to incorrect email id' );
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

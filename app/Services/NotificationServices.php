<?php


namespace App\Services;

use App\Models\Notifications;
use App\Models\User;
use App\Models\Company;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\OnboardingDispatch;
use App\Models\EContractProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Excel as BaseExcel;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PassportRenewalExport;
use App\Exports\InsuranceRenewalExport;
use App\Exports\FomemaRenewalExport;
use App\Exports\PlksRenewalExport;
use App\Exports\CallingVisaRenewalExport;
use App\Exports\SpecialPassRenewalExport;
use App\Exports\EntryVisaRenewalExport;

class NotificationServices
{

    /**
     * NotificationServices constructor.
     * @param Notifications $notifications
     */
    public function __construct(Notifications $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * @param $params
     * @return array
     */
    public function getcount($user)
    {
        $notification = Notifications::where('user_id', $user['id'])->where('read_flag',0)->count('id');
        return ['notification_count' => $notification];
    }

    /**
     * @param $params
     * @return array
     */
    public function list($user)
    {
        $this->renewalNotifications();
        return Notifications::where('user_id', $user['id'])
            ->select('id', 'type', 'title', 'message', 'created_at', 'read_flag')
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    public function insertNotification($params)
    {
        $this->notifications->create([
            'user_id' => $params['user_id'],
            'from_user_id' => $params['from_user_id'],
            'type' => $params['type'],
            'title' => $params['title'],
            'message' => $params['message'],
            'status' => $params['status'],
            'read_flag' => $params['read_flag'],
            'created_by' => $params['created_by'],
            'modified_by' => $params['modified_by']
        ]);
        return true;
    }

    /**
     * @param $params
     * @return array
     */
    public function updateReadStatus($request)
    {
        $notifications = Notifications::where('id', $request['id'])->update(['read_flag' => 1]);
        return  [
            "isUpdated" => $notifications,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * @param $params
     * @return array
     */
    public function renewalNotifications()
    {
        $employeeUsers = User::
        join('user_role_type', 'users.id', '=', 'user_role_type.user_id')
        ->join('role_permission', 'user_role_type.role_id', '=', 'role_permission.role_id')
        ->join('modules', 'role_permission.module_id', '=', 'modules.id')
         ->where('users.user_type', '!=', 'Admin')
        ->where('users.status', 1)
        ->whereNull('users.deleted_at')
        ->whereIn('modules.module_name', Config::get('services.ACCESS_MODULE_TYPE'))
        ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id',DB::raw('GROUP_CONCAT(modules.module_name SEPARATOR ",") AS module_name'))
        ->groupBy('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
        ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

        foreach($employeeUsers as $user){
            $userModules = [];
            if(isset($user['module_name']) && !empty($user['module_name'])){
                $userModules = explode(",", $user['module_name']);
            }

            $message['plksRenewal'] = [];
            $message['callingVisaRenewal'] = [];
            $message['specialPassRenewal'] = [];
            $message['serviceAgreement'] = '';
            $message['fomemaRenewal'] = [];
            $message['passportRenewal'] = [];
            $message['insuranceRenewal'] = [];
            $message['entryVisaRenewal'] = [];

            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules)){
                $message['plksRenewal'] = $this->plksRenewalNotifications($user); 
                $message['callingVisaRenewal'] = $this->callingVisaRenewalNotifications($user); 
                $message['specialPassRenewal'] = $this->specialPassRenewalNotifications($user);
            }
            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules)){
                $message['serviceAgreement'] = $this->serviceAgreementNotifications($user);
            }

            if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], $userModules)){
                $message['fomemaRenewal'] = $this->fomemaRenewalNotifications($user); 
                $message['passportRenewal'] = $this->passportRenewalNotifications($user); 
                $message['insuranceRenewal'] = $this->insuranceRenewalNotifications($user); 
                $message['entryVisaRenewal'] = $this->entryVisaRenewalNotifications($user);
            }
            
            if(!empty($message['fomemaRenewal']) || !empty($message['passportRenewal']) || !empty($message['plksRenewal']) || !empty($message['callingVisaRenewal']) || !empty($message['specialPassRenewal']) || !empty($message['insuranceRenewal']) || !empty($message['entryVisaRenewal']) || !empty($message['serviceAgreement'])){
                dispatch(new \App\Jobs\EmployerNotificationMail($user,$message));
            }
        }

        $adminUsers = User::where('users.user_type', 'Admin')
        ->where('users.status', 1)
        ->whereNull('users.deleted_at')
        ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
        ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

        foreach($adminUsers as $user){
            $message['fomemaRenewal'] = $this->fomemaRenewalNotifications($user); 
            $message['passportRenewal'] = $this->passportRenewalNotifications($user); 
            $message['plksRenewal'] = $this->plksRenewalNotifications($user); 
            $message['callingVisaRenewal'] = $this->callingVisaRenewalNotifications($user); 
            $message['specialPassRenewal'] = $this->specialPassRenewalNotifications($user); 
            $message['insuranceRenewal'] = $this->insuranceRenewalNotifications($user); 
            $message['entryVisaRenewal'] = $this->entryVisaRenewalNotifications($user);
            $message['serviceAgreement'] = $this->serviceAgreementNotifications($user);
            $message['dispatchPending'] = $this->dispatchSummaryNotifications($user);

            if(!empty($message['dispatchPending'])){
                dispatch(new \App\Jobs\RunnerNotificationMail($user,$message['dispatchPending']));
            }
            
            if(!empty($message['fomemaRenewal']) || !empty($message['passportRenewal']) || !empty($message['plksRenewal']) || !empty($message['callingVisaRenewal']) || !empty($message['specialPassRenewal']) || !empty($message['insuranceRenewal']) || !empty($message['entryVisaRenewal']) || !empty($message['serviceAgreement'])){
                dispatch(new \App\Jobs\AdminNotificationMail($user,$message));
            }
            
        }

        return  [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }


    /**
     * @param $params
     * @return array
     */
    public function formNotificationInsertData($user, $count, $type, $title, $message, $duration, $durationType, $mailMessage = null)
    {
        $durationDate = (($durationType == 'MONTHS') ? Carbon::now()->addMonths($duration) : Carbon::now()->addDays($duration));

        $params['user_id'] = $user['id'];
        $params['from_user_id'] = 1;
        $params['type'] = $type;
        $params['title'] = $title;
        $params['message'] = $count." ".$message;
        $params['mail_message'] = $count." ".$mailMessage." - ".$durationDate;
        $params['status'] = 1;
        $params['read_flag'] = 0;
        $params['created_by'] = 1;
        $params['modified_by'] = 1;

        return $params;
    }


    /**
     * @param $params
     * @return array
     */
    public function fomemaRenewalNotifications($user)
    {
        $params = [];
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '<', Carbon::now()->addMonths(3))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0){
            
            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.FOMEMA_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_NOTIFICATION_MESSAGE'), 3, 'MONTHS', Config::get('services.FOMEMA_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "fomemaRenewal.csv";
            $params['attachment_file'] = Excel::raw(new FomemaRenewalExport($user['company_id']), BaseExcel::CSV);
        }        
        return $params;
    }

    /**
     * @param $params
     * @return array
     */
    public function passportRenewalNotifications($user)
    {
        $params = [];
        $passportRenewalNotificationsCount = Workers::whereDate('passport_valid_until', '<', Carbon::now()->addMonths(3))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($passportRenewalNotificationsCount) && $passportRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PASSPORT_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_NOTIFICATION_MESSAGE'), 3, 'MONTHS', Config::get('services.PASSPORT_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "passportRenewal.csv";
            $params['attachment_file'] = Excel::raw(new PassportRenewalExport($user['company_id']), BaseExcel::CSV);
        }      
        return $params;  
    }

    /**
     * @param $params
     * @return array
     */
    public function plksRenewalNotifications($user)
    {
        $params = [];
        $plksRenewalNotificationsCount = Workers::whereDate('plks_expiry_date', '<', Carbon::now()->addMonths(2))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($plksRenewalNotificationsCount) && $plksRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PLKS_NOTIFICATION_TITLE'), Config::get('services.PLKS_NOTIFICATION_MESSAGE'), 2, 'MONTHS', Config::get('services.PLKS_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "plksRenewal.csv";
            $params['attachment_file'] = Excel::raw(new PlksRenewalExport($user['company_id']), BaseExcel::CSV);
        }   
        return $params;     
    }

    /**
     * @param $params
     * @return array
     */
    public function callingVisaRenewalNotifications($user)
    {
        $params = [];
        $callingVisaRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addMonths(1))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($callingVisaRenewalNotificationsCount) && $callingVisaRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.CALLING_VISA_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "callingVisaRenewal.csv";
            $params['attachment_file'] = Excel::raw(new CallingVisaRenewalExport($user['company_id']), BaseExcel::CSV);
        }       
        return $params;
    }

    /**
     * @param $params
     * @return array
     */
    public function specialPassRenewalNotifications($user)
    {
        $params = [];
        $specialPassRenewalNotificationsCount = Workers::whereDate('special_pass_valid_until', '<', Carbon::now()->addMonths(1))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($specialPassRenewalNotificationsCount) && $specialPassRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.SPECIAL_PASS_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "specialPassRenewal.csv";
            $params['attachment_file'] = Excel::raw(new SpecialPassRenewalExport($user['company_id']), BaseExcel::CSV);
        }  
        return $params;      
    }

    /**
     * @param $params
     * @return array
     */
    public function insuranceRenewalNotifications($user)
    {
        $params = [];
        $insuranceRenewalNotifications = Workers::join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addMonths(1))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($insuranceRenewalNotifications) && $insuranceRenewalNotifications != 0){
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.INSURANCE_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.INSURANCE_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "insuranceRenewal.csv";
            $params['attachment_file'] = Excel::raw(new InsuranceRenewalExport($user['company_id']), BaseExcel::CSV);
        }        
        return $params;
    }

    /**
     * @param $params
     * @return array
     */
    public function entryVisaRenewalNotifications($user)
    {
        $params = [];
        $entryVisaRenewalNotifications = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now()->addDays(15))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($entryVisaRenewalNotifications) && $entryVisaRenewalNotifications != 0){
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_NOTIFICATION_MESSAGE'), 15, 'DAYS', Config::get('services.ENTRY_VISA_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['attachment_filename'] = "entryVisaRenewal.csv";
            $params['attachment_file'] = Excel::raw(new EntryVisaRenewalExport($user['company_id']), BaseExcel::CSV);
        } 
        return $params;       
    }

    /**
     * @param $params
     * @return array
     */
    public function dispatchPendingNotifications($user)
    {
        $mailMessage = '';

        $pendingCount = OnboardingDispatch::where('onboarding_dispatch.employee_id', $user['reference_id'])
        ->where('onboarding_dispatch.dispatch_status', 'Assigned')
        ->where('onboarding_dispatch.calltime', '<', Carbon::now())
        ->select('onboarding_dispatch.id', 'onboarding_dispatch.reference_number', 'onboarding_dispatch.employee_id', 'onboarding_dispatch.created_by', 'onboarding_dispatch.dispatch_status', 'onboarding_dispatch.calltime')
        ->distinct('onboarding_dispatch.id', 'onboarding_dispatch.reference_number', 'onboarding_dispatch.employee_id', 'onboarding_dispatch.created_by', 'onboarding_dispatch.dispatch_status', 'onboarding_dispatch.calltime')
        ->get();

        foreach($pendingCount as $row){
            $NotificationParams['user_id'] = $user['id'];
            $NotificationParams['from_user_id'] = $row['created_by'];
            $NotificationParams['type'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['title'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['message'] = $row['reference_number'].' '.Config::get('services.DISPATCH_MAIL_MESSAGE');
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = $row['created_by'];
            $NotificationParams['modified_by'] = $row['created_by'];
            $this->insertNotification($NotificationParams);
            $mailMessage .= $row['reference_number'].' '.Config::get('services.DISPATCH_MAIL_MESSAGE').'<br/>';
        }
        return $mailMessage;       
    }
    /**
     * @param $params
     * @return array
     */
    public function serviceAgreementNotifications($user)
    {
        $mailMessage = '';

        $serviceAgreement = EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
        ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
        ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
        ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
        ->whereDate('e-contract_project.valid_until', '<', Carbon::now()->addMonths(3))
        ->where('e-contract_applications.company_id', $user['company_id'])
        ->get();
        foreach($serviceAgreement as $row){
            $NotificationParams['user_id'] = $user['id'];
            $NotificationParams['from_user_id'] = 1;
            $NotificationParams['type'] = Config::get('services.SERVICE_AGREEMENT_NOTIFICATION_TITLE');
            $NotificationParams['title'] = Config::get('services.SERVICE_AGREEMENT_NOTIFICATION_TITLE');
            $NotificationParams['message'] = $row['company_name'].' - '.$row['name'].' '.Config::get('services.SERVICE_AGREEMENT_MAIL_MESSAGE').' '.$row['valid_until'];
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = 1;
            $NotificationParams['modified_by'] = 1;
            $this->insertNotification($NotificationParams);
            $mailMessage .= $row['company_name'].' - '.$row['name'].' '.Config::get('services.SERVICE_AGREEMENT_MAIL_MESSAGE').' '.$row['valid_until'].' <br/>';
        }
        return $mailMessage;
    }
    /**
     * @param $params
     * @return array
     */
    public function dispatchSummaryNotifications($user)
    {
        $notificationMessage = '';
        $mailMessage = '';
        
        $pending_count = OnboardingDispatch::leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->where('employee.company_id', $user['company_id'])
        ->where('onboarding_dispatch.dispatch_status', 'Assigned')
        ->where('onboarding_dispatch.calltime', '<', Carbon::now())
        ->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        $assigned_count = OnboardingDispatch::leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->where('employee.company_id', $user['company_id'])
        ->where('onboarding_dispatch.dispatch_status', 'Assigned')
        ->where('onboarding_dispatch.calltime', '>', Carbon::now())
        ->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        $completed_count = OnboardingDispatch::leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->where('employee.company_id', $user['company_id'])
        ->where('onboarding_dispatch.dispatch_status', 'Completed')
        ->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        if($pending_count > 0){
            $notificationMessage .= $pending_count.' Dispatches are Pending. ';
            $mailMessage .= $pending_count.' no. of Dispatches are Pending. <br/>';
        }
        if($assigned_count > 0){
            $notificationMessage .= $assigned_count.' Dispatches are Assigned. ';
            $mailMessage .= $assigned_count.' no. of Dispatches are Assigned. <br/>';
        }
        if($completed_count > 0){
            $notificationMessage .= $completed_count.' Dispatches are Completed.';
            $mailMessage .= $completed_count.' no. of Dispatches are Completed. <br/>';
        }

        if(!empty($notificationMessage)){
            $NotificationParams['user_id'] = $user['id'];
            $NotificationParams['from_user_id'] = 1;
            $NotificationParams['type'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['title'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['message'] = $notificationMessage;
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = 1;
            $NotificationParams['modified_by'] = 1;
            $this->insertNotification($NotificationParams);
        }

        return $mailMessage;       
    }
     /**
     * @param $params
     * @return array
     */
    public function insertDispatchNotification($params)
    {
        if(isset($params['company_id']) && !empty($params['company_id']) && !empty($params['message'])){

            $this->insertNotification($params);

            $adminUsers = User::where('users.user_type', 'Admin')
            ->where('users.company_id', $params['company_id'])
            ->where('users.status', 1)
            ->whereNull('users.deleted_at')
            ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

            foreach($adminUsers as $user){
                $params['user_id'] = $user['id'];
                $this->insertNotification($params);
                dispatch(new \App\Jobs\RunnerNotificationMail($user,$params['message']));
            }

            $employeeUsers = User::
            join('user_role_type', 'users.id', '=', 'user_role_type.user_id')
            ->join('role_permission', 'user_role_type.role_id', '=', 'role_permission.role_id')
            ->join('modules', 'role_permission.module_id', '=', 'modules.id')
            ->where('users.company_id', $params['company_id'])
            ->where('users.user_type', '!=', 'Admin')
            ->where('users.id', '!=', $params['user_id'])
            ->where('users.status', 1)
            ->whereNull('users.deleted_at')
            ->where('modules.module_name', Config::get('services.ACCESS_MODULE_TYPE')[10])
            ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id',DB::raw('GROUP_CONCAT(modules.module_name SEPARATOR ",") AS module_name'))
            ->groupBy('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

            foreach($employeeUsers as $user){
                $params['user_id'] = $user['id'];
                $this->insertNotification($params);
                dispatch(new \App\Jobs\RunnerNotificationMail($user,$params['message']));
            }

        }
        
        return true;
    }

}

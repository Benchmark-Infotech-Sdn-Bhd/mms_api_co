<?php


namespace App\Services;

use App\Models\Notifications;
use App\Models\User;
use App\Models\Company;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\OnboardingDispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

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
        ->whereIn('modules.module_name', Config::get('services.WORKER_MODULE_TYPE'))
        ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
        ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

        foreach($employeeUsers as $user){
            $message['fomemaRenewal'] = $this->fomemaRenewalNotifications($user); 
            $message['passportRenewal'] = $this->passportRenewalNotifications($user); 
            $message['plksRenewal'] = $this->plksRenewalNotifications($user); 
            $message['callingVisaRenewal'] = $this->callingVisaRenewalNotifications($user); 
            $message['specialPassRenewal'] = $this->specialPassRenewalNotifications($user); 
            $message['insuranceRenewal'] = $this->insuranceRenewalNotifications($user); 
            $message['entryVisaRenewal'] = $this->entryVisaRenewalNotifications($user);
            $message['dispatchPending'] = $this->dispatchPendingNotifications($user);
            
            if(!empty($message['dispatchPending'])){
                dispatch(new \App\Jobs\RunnerNotificationMail($user,$message['dispatchPending']));
            }
            
            if(!empty($message['fomemaRenewal']) || !empty($message['passportRenewal']) || !empty($message['plksRenewal']) || !empty($message['callingVisaRenewal']) || !empty($message['specialPassRenewal']) || !empty($message['insuranceRenewal']) || !empty($message['entryVisaRenewal'])){
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
            
            if(!empty($message['fomemaRenewal']) || !empty($message['passportRenewal']) || !empty($message['plksRenewal']) || !empty($message['callingVisaRenewal']) || !empty($message['specialPassRenewal']) || !empty($message['insuranceRenewal']) || !empty($message['entryVisaRenewal'])){
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
    public function formNotificationInsertData($user, $count, $type, $title, $message, $duration, $durationType)
    {
        $durationDate = (($durationType == 'MONTHS') ? Carbon::now()->addMonths($duration) : Carbon::now()->addDays($duration));

        $params['user_id'] = $user['id'];
        $params['from_user_id'] = 1;
        $params['type'] = $type;
        $params['title'] = $title;
        $params['message'] = $count." ".$message;
        $params['mail_message'] = $count." ".$message." - ".$durationDate;
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
            
            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.FOMEMA_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_NOTIFICATION_MESSAGE'), 3, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PASSPORT_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_NOTIFICATION_MESSAGE'), 3, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PLKS_NOTIFICATION_TITLE'), Config::get('services.PLKS_NOTIFICATION_MESSAGE'), 2, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.INSURANCE_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
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
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_NOTIFICATION_MESSAGE'), 15, 'DAYS');
            $this->insertNotification($params);
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
            $NotificationParams['user_id'] = $user['reference_id'];
            $NotificationParams['from_user_id'] = $row['created_by'];
            $NotificationParams['type'] = 'Dispatches';
            $NotificationParams['title'] = 'Dispatches';
            $NotificationParams['message'] = $row['reference_number'].' Dispatch is Pending';
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = $row['created_by'];
            $NotificationParams['modified_by'] = $row['created_by'];
            $this->insertNotification($NotificationParams);
            $mailMessage .= $row['reference_number'].' Dispatch is Pending <br/>';
        }
        return $mailMessage;       
    }

}

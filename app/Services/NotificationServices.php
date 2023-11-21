<?php


namespace App\Services;

use App\Models\Notifications;
use App\Models\User;
use App\Models\Company;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
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
        $users = User::
        join('user_role_type', 'users.id', '=', 'user_role_type.user_id')
        ->join('role_permission', 'user_role_type.role_id', '=', 'role_permission.role_id')
        ->join('modules', 'role_permission.module_id', '=', 'modules.id')
        ->where('users.status', 1)
        ->whereNull('users.deleted_at')
        ->whereIn('modules.module_name', Config::get('services.WORKER_MODULE_TYPE'))
        ->select('users.id','users.company_id')
        ->distinct('users.id')->get();

        foreach($users as $user){
            $this->fomemaRenewalNotifications($user); 
            $this->passportRenewalNotifications($user); 
            $this->plksRenewalNotifications($user); 
            $this->callingVisaRenewalNotifications($user); 
            $this->specialPassRenewalNotifications($user); 
            $this->insuranceRenewalNotifications($user); 
            $this->entryVisaRenewalNotifications($user);            
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
        $params['message'] = $count." ".$message." - ".$durationDate;
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
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '<', Carbon::now()->addMonths(3))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0){
            
            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.FOMEMA_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_NOTIFICATION_MESSAGE'), 3, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function passportRenewalNotifications($user)
    {
        $passportRenewalNotificationsCount = Workers::whereDate('passport_valid_until', '<', Carbon::now()->addMonths(3))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($passportRenewalNotificationsCount) && $passportRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PASSPORT_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_NOTIFICATION_MESSAGE'), 3, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function plksRenewalNotifications($user)
    {
        $plksRenewalNotificationsCount = Workers::whereDate('plks_expiry_date', '<', Carbon::now()->addMonths(2))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($plksRenewalNotificationsCount) && $plksRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PLKS_NOTIFICATION_TITLE'), Config::get('services.PLKS_NOTIFICATION_MESSAGE'), 2, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function callingVisaRenewalNotifications($user)
    {
        $callingVisaRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addMonths(1))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($callingVisaRenewalNotificationsCount) && $callingVisaRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function specialPassRenewalNotifications($user)
    {
        $specialPassRenewalNotificationsCount = Workers::whereDate('special_pass_valid_until', '<', Carbon::now()->addMonths(1))->select('id')->where('company_id', $user['company_id'])->count();

        if(isset($specialPassRenewalNotificationsCount) && $specialPassRenewalNotificationsCount != 0){
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function insuranceRenewalNotifications($user)
    {
        $insuranceRenewalNotifications = Workers::join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addMonths(1))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($insuranceRenewalNotifications) && $insuranceRenewalNotifications != 0){
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.INSURANCE_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_NOTIFICATION_MESSAGE'), 1, 'MONTHS');
            $this->insertNotification($params);
        }        
    }

    /**
     * @param $params
     * @return array
     */
    public function entryVisaRenewalNotifications($user)
    {
        $entryVisaRenewalNotifications = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now()->addDays(15))->select('workers.id')->where('workers.company_id', $user['company_id'])->count();

        if(isset($entryVisaRenewalNotifications) && $entryVisaRenewalNotifications != 0){
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_NOTIFICATION_MESSAGE'), 15, 'DAYS');
            $this->insertNotification($params);
        }        
    }

}

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
    public function fomemaRenewalNotifications($user)
    {
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '<', Carbon::now()->addMonths(2))->select('id','name','fomema_valid_until')->where('company_id', $user['company_id'])->count();

        if(isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0){
            $params['user_id'] = $user['id'];
            $params['from_user_id'] = 1;
            $params['type'] = Config::get('services.NOTIFICATION_TYPE');
            $params['title'] = Config::get('services.FOMEMA_NOTIFICATION_TITLE');
            $params['message'] = $fomemaRenewalNotificationsCount." ".Config::get('services.FOMEMA_NOTIFICATION_MESSAGE')." - ".Carbon::now()->addMonths(2);
            $params['status'] = 1;
            $params['read_flag'] = 0;
            $params['created_by'] = 1;
            $params['modified_by'] = 1;

            $this->insertNotification($params);
        }
        
    }

}

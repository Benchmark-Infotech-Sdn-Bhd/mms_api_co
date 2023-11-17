<?php


namespace App\Services;

use App\Models\Notifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

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

}

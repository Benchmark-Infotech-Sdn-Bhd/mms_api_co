<?php


namespace App\Services;

use App\Jobs\AdminNotificationMail;
use App\Jobs\EmployerNotificationMail;
use App\Jobs\RunnerNotificationMail;
use App\Models\Notifications;
use App\Models\User;
use App\Models\Workers;
use App\Models\EContractProject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\OnboardingDispatch;
use App\Models\CompanyRenewalNotification;
use App\Models\TotalManagementProject;

class NotificationServices
{
    public const RENEWAL_NOTIFICATION_ON = 1;
    public const EXPIRED_NOTIFICATION_ON = 1;

    private Notifications $notifications;
    private CompanyRenewalNotification $companyRenewalNotification;

    /**
     * NotificationServices constructor.
     * @param Notifications $notifications
     * @param CompanyRenewalNotification $companyRenewalNotification
     */
    public function __construct(Notifications $notifications, CompanyRenewalNotification $companyRenewalNotification)
    {
        $this->notifications = $notifications;
        $this->companyRenewalNotification = $companyRenewalNotification;
    }

    /**
     * Retrieves the count of unread notifications for a specified user.
     *
     * @param array $user An array containing the user details.
     * @return array Returns an array with the count of unread notifications.
     */
    public function getcount($user): array
    {
        return ['notification_count' => Notifications::where('user_id', $user['id'])->where('read_flag', 0)->count('id')];
    }

    /**
     * Retrieves a paginated list of notifications for a specific user.
     *
     * @param $user array The user for whom the notifications should be retrieved.
     *               The user array should have the following keys:
     *                   - id : The ID of the user.
     *
     * @return LengthAwarePaginator The paginated list of notifications.
     *                     The list contains the following fields:
     *                   - id : The ID of the notification.
     *                   - type : The type of the notification.
     *                   - title : The title of the notification.
     *                   - message : The content of the notification.
     *                   - created_at : The timestamp of when the notification was created.
     *                   - read_flag : Flag indicating whether the notification has been read or not. (1 = read, 0 = unread)
     */
    public function list($user): LengthAwarePaginator
    {
        return Notifications::where('user_id', $user['id'])
            ->select('id', 'type', 'title', 'message', 'created_at', 'read_flag')
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Insert a notification into the database.
     *
     * @param array $params The parameters for the notification.
     *     - user_id (int): The ID of the user to whom the notification is being sent.
     *     - from_user_id (int): The ID of the user sending the notification.
     *     - type (string): The type of notification. (e.g. "email", "sms")
     *     - title (string): The title of the notification.
     *     - message (string): The content/message of the notification.
     *     - status (int): The status of the notification. (e.g. 0 = pending, 1 = sent)
     *     - read_flag (int): The read flag of the notification. (e.g. 0 = unread, 1 = read)
     *     - created_by (int): The ID of the user who created the notification.
     *     - modified_by (int): The ID of the user who last modified the notification.
     *
     * @return bool Returns true if the notification is successfully inserted into the database.
     */
    public function insertNotification($params): bool
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
     * Update the read status of a notification.
     *
     * @param array $request The request data.
     *                      - id (integer) The ID of the notification.
     *
     * @return array The response data.
     *               - isUpdated (integer) The number of rows updated.
     *               - message (string) A success message.
     */
    public function updateReadStatus($request): array
    {
        return [
            "isUpdated" => Notifications::where('id', $request['id'])->update(['read_flag' => 1]),
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Sends renewal notifications to users and admins.
     *
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array Returns an array with the following keys:
     *   - isUpdated (boolean): Indicates whether the notifications were sent successfully.
     *   - message (string): A message indicating the status of the update process.
     */
    public function renewalNotifications(string $renewalType, string $frequency): array
    {
        $this->userRenewalNotification($renewalType, $frequency);
        $this->adminRenewalNotification($renewalType, $frequency);

        return [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Send renewal notification to employee users.
     *
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return void
     */
    public function userRenewalNotification(string $renewalType, string $frequency): void
    {
        $employeeUsers = $this->getEmployeeUsersFromModules();
        foreach ($employeeUsers as $user) {
            $notifications = $this->getUserModulesAndNotification($user, $renewalType, $frequency);
            $this->dispatchEmployerNotificationMail($notifications, $user);
        }
    }

    /**
     * Get employee users from modules.
     *
     * @return Collection
     */
    public function getEmployeeUsersFromModules(): Collection
    {
        return User::
        join('user_role_type', 'users.id', '=', 'user_role_type.user_id')
            ->join('role_permission', 'user_role_type.role_id', '=', 'role_permission.role_id')
            ->join('modules', 'role_permission.module_id', '=', 'modules.id')
            ->where('users.user_type', '!=', 'Admin')
            ->where('users.status', 1)
            ->whereNull('users.deleted_at')
            ->whereIn('modules.module_name', Config::get('services.ACCESS_MODULE_TYPE'))
            ->select('users.id', 'users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id', DB::raw('GROUP_CONCAT(modules.module_name SEPARATOR ",") AS module_name'))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->distinct('users.id', 'users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();
    }

    /**
     * getUserModulesAndNotification
     *
     * Retrieves the user modules and generates corresponding notifications based on the modules.
     *
     * @param $user array The user data.
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The notifications array containing the generated notifications.
     */
    public function getUserModulesAndNotification($user, $renewalType, $frequency): array
    {
        $notifications = [];
        $userModules = $this->getUpdatedUserModules($user);

        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules)) {
            $notifications['plksRenewal'] = $this->plksNotifications($user, $renewalType, $frequency);
            $notifications['callingVisaRenewal'] = $this->callingVisaNotifications($user, $renewalType, $frequency);
            $notifications['specialPassRenewal'] = $this->specialPassNotifications($user, $renewalType, $frequency);
        }
        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], $userModules)) {
            $notifications['serviceAgreement'] = $this->serviceAgreementNotifications($user, $renewalType, $frequency);
        }
        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], $userModules)) {
            $notifications['fomemaRenewal'] = $this->fomemaNotifications($user, $renewalType, $frequency);
            $notifications['passportRenewal'] = $this->passportNotifications($user, $renewalType, $frequency);
            $notifications['insuranceRenewal'] = $this->insuranceNotifications($user, $renewalType, $frequency);
            $notifications['entryVisaRenewal'] = $this->entryVisaNotifications($user, $renewalType, $frequency);
        }
        return $notifications;
    }

    /**
     * Retrieves the updated user modules.
     *
     * @param array $user The user data.
     * @return array The updated user modules.
     */
    private function getUpdatedUserModules($user): array
    {
        $userModules = [];
        if (!empty($user['module_name'])) {
            $userModules = explode(",", $user['module_name']);
        }
        return $userModules;
    }

    /**
     * Dispatches an employer notification email based on the provided notifications and user.
     *
     * @param array $notifications The notifications to include in the email.
     * @param array $user The user receiving the email.
     */
    private function dispatchEmployerNotificationMail($notifications, $user): void
    {
        if (!empty($notifications['fomemaRenewal']) || !empty($notifications['passportRenewal']) || !empty($notifications['plksRenewal']) || !empty($notifications['callingVisaRenewal']) || !empty($notifications['specialPassRenewal']) || !empty($notifications['insuranceRenewal']) || !empty($notifications['entryVisaRenewal']) || !empty($notifications['serviceAgreement'])) {
            dispatch(new EmployerNotificationMail(Config::get('database.connections.mysql.database'), $user, $notifications))->onQueue(Config::get('services.EMPLOYER_NOTIFICATION_MAIL'))->onConnection(Config::get('services.QUEUE_CONNECTION'));
        }
    }

    /**
     * Sends renewal notifications to admin users.
     *
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return void
     */
    public function adminRenewalNotification($renewalType, $frequency): void
    {
        $notifications = [
            'fomema',
            'passport',
            'plks',
            'callingVisa',
            'specialPass',
            'insurance',
            'entryVisa',
            'serviceAgreement',
            'dispatchPending'
        ];

        $adminUsers = User::where('users.user_type', 'Admin')
            ->where('users.status', 1)
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->distinct('users.id', 'users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')->get();

        foreach ($adminUsers as $user) {
            foreach ($notifications as $notification) {
                if($notification == 'dispatchPending'){
                    $message[$notification] = $this->dispatchSummaryNotifications($user);
                }else {
                    $message[$notification] = $this->{$notification.'Notifications'}($user, $renewalType, $frequency);
                }  
            }

            $dispatchConditions = $this->checkDispatchConditions($message);
            if (!empty($message['dispatchPending'])) {
                dispatch(new RunnerNotificationMail(Config::get('database.connections.mysql.database'), $user, $message['dispatchPending']))
                    ->onQueue(Config::get('services.RUNNER_NOTIFICATION_MAIL'))
                    ->onConnection(Config::get('services.QUEUE_CONNECTION'));
            }

            if (!empty($dispatchConditions)) {
                dispatch(new AdminNotificationMail(Config::get('database.connections.mysql.database'), $user, $message))
                    ->onQueue(Config::get('services.ADMIN_NOTIFICATION_MAIL'))
                    ->onConnection(Config::get('services.QUEUE_CONNECTION'));
            }
        }
    }

    /**
     * Check dispatch conditions based on message parameters.
     *
     * @param array $message The message parameters.
     * @return bool Returns true if any of the dispatch conditions are met, false otherwise.
     */
    private function checkDispatchConditions($message): bool
    {
        return (!empty($message['fomema']) || !empty($message['passport']) || !empty($message['plks']) || !empty($message['callingVisa']) || !empty($message['specialPass']) || !empty($message['insurance']) || !empty($message['entryVisa']) || !empty($message['serviceAgreement']));
    }


    /**
     * Generates an array of parameters for inserting a notification into the database.
     *
     * @param array $user The user information.
     * @param int $count The count of the notification.
     * @param string $type The type of the notification.
     * @param string $title The title of the notification.
     * @param string $message The message of the notification.
     * @param int $duration The duration of the notification in days or months.
     * @param string $durationType The type of the duration ('DAYS' or 'MONTHS').
     * @param string|null $mailMessage Optional mail message for the notification.
     * @return array The parameters for inserting the notification.
     */
    public function formNotificationInsertData(
        $user,
        $count,
        $type,
        $title,
        $message,
        $duration,
        $durationType,
        $mailMessage = null,
        $notificationType
    ): array
    {
        $durationDate = $durationType == 'DAYS'
            ? Carbon::now()->addDays($duration)
            : Carbon::now()->addMonths($duration);

        $commonMessage = $notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]
            ? Config::get('services.COMMON_RENEWAL_MAIL_MESSAGE')
            : Config::get('services.COMMON_EXPIRY_MAIL_MESSAGE');

        return [
            'user_id' => $user['id'],
            'from_user_id' => 1,
            'type' => $type,
            'title' => $title,
            'message' => $count . " " . $message,
            'mail_message' =>  $mailMessage . " " . $count . " " . $commonMessage,
            'status' => 1,
            'read_flag' => 0,
            'created_by' => 1,
            'modified_by' => 1,
        ];
    }


    /**
     * Generates Fomema notifications for a user
     *
     * @param array $user The user for whom to generate the notifications
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The generated notification parameters
     * 
     * @see getNotificationDetails()
     * @see fomemaRenewalNotifications()
     * @see fomemaExpiryNotifications()
     */
    public function fomemaNotifications($user, $renewalType, $frequency)
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['FOMEMA Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->fomemaRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->fomemaExpiryNotifications($user, $notificationDetails);
            }
        }
        return $params;
    }
    /**
     * Count and generate Fomema renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's Fomema notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function fomemaRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                    ->whereDate('fomema_valid_until', '>=', Carbon::now())
                                    ->select('id')
                                    ->where('company_id', $user['company_id'])
                                    ->count();

        if (isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.FOMEMA_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.FOMEMA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate Fomema renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's Fomema notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function fomemaExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                    ->whereDate('fomema_valid_until', '<', Carbon::now())
                                    ->select('id')
                                    ->where('company_id', $user['company_id'])
                                    ->count();

        if (isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.FOMEMA_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.FOMEMA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Generates passport notifications for a user
     *
     * @param array $user The user for whom the notifications should be generated
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The generated notification parameters
     * 
     * @see getNotificationDetails()
     * @see passportRenewalNotifications()
     * @see passportExpiryNotifications()
     */
    public function passportNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Passport Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->passportRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->passportExpiryNotifications($user, $notificationDetails);
                
            }
        }
        return $params;
    }

    /**
     * Count and generate passport renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's passport notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function passportRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $passportRenewalNotificationsCount = Workers::whereDate('passport_valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                    ->whereDate('passport_valid_until', '>=', Carbon::now())
                                    ->select('id')
                                    ->where('company_id', $user['company_id'])
                                    ->count();

        if (isset($passportRenewalNotificationsCount) && $passportRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PASSPORT_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.PASSPORT_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate passport renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's passport notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function passportExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $passportRenewalNotificationsCount = Workers::whereDate('passport_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                    ->whereDate('passport_valid_until', '<', Carbon::now())
                                    ->select('id')
                                    ->where('company_id', $user['company_id'])
                                    ->count();

        if (isset($passportRenewalNotificationsCount) && $passportRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.PASSPORT_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.PASSPORT_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate PLKS notifications for a user.
     *
     * @param array $user The user information
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The generated notification parameters
     * 
     * @see getNotificationDetails()
     * @see plksRenewalNotifications()
     * @see plksExpiryNotifications()
     */
    public function plksNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['PLKS Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->plksRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->plksExpiryNotifications($user, $notificationDetails);
            }
        }
        return $params;
    }

    /**
     * Count and generate PLKS renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's plks notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function plksRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $plksRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                ->where(function ($query) {
                                    $query->whereDate('workers.plks_expiry_date', '>=', Carbon::now())
                                    ->orWhereDate('worker_visa.work_permit_valid_until', '>=', Carbon::now());
                                })
                                ->select('workers.id')
                                ->where('workers.company_id', $user['company_id'])
                                ->count();
        
        if (isset($plksRenewalNotificationsCount) && $plksRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PLKS_NOTIFICATION_TITLE'), Config::get('services.PLKS_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.PLKS_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate PLKS renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's plks notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function plksExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $plksRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                            ->where(function($q) use ($notificationDetails) {
                                $q->where(function($query) use ($notificationDetails){
                                    $query->whereDate('workers.plks_expiry_date', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                    ->whereDate('workers.plks_expiry_date', '<', Carbon::now());
                                })
                                ->orWhere(function($query) use ($notificationDetails) {
                                    $query->whereDate('worker_visa.work_permit_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                    ->whereDate('worker_visa.work_permit_valid_until', '<', Carbon::now());
                                });
                            })
                            ->select('workers.id')
                            ->where('workers.company_id', $user['company_id'])
                            ->count();

        if (isset($plksRenewalNotificationsCount) && $plksRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.PLKS_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.PLKS_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.PLKS_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate calling visa notifications for a user.
     *
     * @param array $user The user details.
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The notification parameters.
     * 
     * @see getNotificationDetails()
     * @see callingVisaRenewalNotifications()
     * @see callingVisaExpiryNotifications()
     */
    public function callingVisaNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Calling Visa Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->callingVisaRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->callingVisaExpiryNotifications($user, $notificationDetails);
            }
        }
        return $params;
    }

    /**
     * Count and generate calling visa renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's calling visa notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function callingVisaRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $callingVisaRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                            ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                            ->whereDate('worker_visa.calling_visa_valid_until', '>=', Carbon::now())
                                            ->select('workers.id')
                                            ->where('workers.company_id', $user['company_id'])
                                            ->count();

        if (isset($callingVisaRenewalNotificationsCount) && $callingVisaRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.CALLING_VISA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate calling visa renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's calling visa notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function callingVisaExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $callingVisaRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                            ->whereDate('worker_visa.calling_visa_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                            ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now())
                                            ->select('workers.id')
                                            ->where('workers.company_id', $user['company_id'])
                                            ->count();

        if (isset($callingVisaRenewalNotificationsCount) && $callingVisaRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.CALLING_VISA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }
    
    /**
     * Generates special pass notifications for a given user.
     *
     * @param array $user The user for whom the notifications are generated.
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array The generated notification parameters.
     * 
     * @see getNotificationDetails()
     * @see specialPassRenewalNotifications()
     * @see specialPassExpiryNotifications()
     */
    public function specialPassNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Special Passes Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->specialPassRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->specialPassExpiryNotifications($user, $notificationDetails);
            }
        }
        return $params;
    }

    /**
     * Count and generate special pass renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's special pass notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function specialPassRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $specialPassRenewalNotificationsCount = Workers::whereDate('special_pass_valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                            ->whereDate('special_pass_valid_until', '>=', Carbon::now())
                                            ->select('id')
                                            ->where('company_id', $user['company_id'])
                                            ->count();

        if (isset($specialPassRenewalNotificationsCount) && $specialPassRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.SPECIAL_PASS_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate  special pass renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's special pass notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function specialPassExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $specialPassRenewalNotificationsCount = Workers::whereDate('special_pass_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                            ->whereDate('special_pass_valid_until', '<', Carbon::now())
                                            ->select('id')
                                            ->where('company_id', $user['company_id'])
                                            ->count();

        if (isset($specialPassRenewalNotificationsCount) && $specialPassRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.SPECIAL_PASS_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Checks for insurance renewal notifications for a given user.
     *
     * @param array $user The user for which to check insurance renewal notifications.
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array Returns an array containing the notification parameters if there are insurance renewal notifications, otherwise an empty array.
     * 
     * @see getNotificationDetails()
     * @see insuranceRenewalNotifications()
     * @see insuranceExpiryNotifications()
     */
    public function insuranceNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Insurance Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->insuranceRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->insuranceExpiryNotifications($user, $notificationDetails);
            }
        }
        return $params;
    }

    /**
     * Count and generate insurance renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's insurance notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function insuranceRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $insuranceRenewalNotifications = Workers::join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')
                                        ->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                        ->whereDate('worker_insurance_details.insurance_expiry_date', '>=', Carbon::now())
                                        ->select('workers.id')
                                        ->where('workers.company_id', $user['company_id'])
                                        ->count();

        if (isset($insuranceRenewalNotifications) && $insuranceRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.INSURANCE_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.INSURANCE_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate  insurance renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's insurance notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function insuranceExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $insuranceRenewalNotifications = Workers::join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')
                                        ->whereDate('worker_insurance_details.insurance_expiry_date', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                        ->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now())
                                        ->select('workers.id')
                                        ->where('workers.company_id', $user['company_id'])
                                        ->count();

        if (isset($insuranceRenewalNotifications) && $insuranceRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.INSURANCE_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.INSURANCE_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Retrieves the entry visa renewal notifications for a user.
     *
     * @param array $user An array containing the company_id of the user.
     * @param string $renewalType - notification type, either renewal or expired
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array An array with the notification data if there are entry visa renewal notifications, otherwise an empty array.
     * 
     * @see getNotificationDetails()
     * @see entryVisaRenewalNotifications()
     * @see entryVisaExpiryNotifications()
     */
    public function entryVisaNotifications($user, $renewalType, $frequency): array
    {
        $params = [];
        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Entry Visa Renewal'], $frequency);

        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                $params = $this->entryVisaRenewalNotifications($user, $notificationDetails);
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                $params = $this->entryVisaExpiryNotifications($user, $notificationDetails);
            } 
        }
        return $params;
    }

    /**
     * Count and generate entry visa renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's entry visa notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function entryVisaRenewalNotifications($user, $notificationDetails): array
    {
        $params = [];
        $entryVisaRenewalNotifications = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                    ->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
                                    ->whereDate('worker_visa.entry_visa_valid_until', '>=', Carbon::now())
                                    ->select('workers.id')
                                    ->where('workers.company_id', $user['company_id'])
                                    ->count();

        if (isset($entryVisaRenewalNotifications) && $entryVisaRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_NOTIFICATION_MESSAGE'), $notificationDetails[0]['renewal_duration_in_days'], 'DAYS', Config::get('services.ENTRY_VISA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate  entry visa renewal notifications for a user.
     *
     * @param array $user The user information
     * @param array $notificationDetails - contains company's entry visa notification details
     * 
     * @return array The generated notification parameters
     * 
     */
    public function entryVisaExpiryNotifications($user, $notificationDetails): array
    {
        $params = [];
        $entryVisaRenewalNotifications = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                    ->whereDate('worker_visa.entry_visa_valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
                                    ->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now())
                                    ->select('workers.id')
                                    ->where('workers.company_id', $user['company_id'])
                                    ->count();

        if (isset($entryVisaRenewalNotifications) && $entryVisaRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.EXPIRY_NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_EXPIRY_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_EXPIRY_NOTIFICATION_MESSAGE'), $notificationDetails[0]['expired_duration_in_days'], 'DAYS', Config::get('services.ENTRY_VISA_MAIL_MESSAGE'), Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]);
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Return the parameters for service agreement notifications.
     *
     * @param array $user The user data.
     * @return array The parameters for service agreement notifications.
     */
    public function serviceAgreementNotifications($user, $renewalType, $frequency): array
    {
        $params['mail_message'] = '';
        $params['company_id'] = '';
        $eContractServiceAgreement = [];
        $TotalManagementserviceAgreement = [];
        $serviceAgreement = [];

        $notificationDetails = $this->getNotificationDetails($user['company_id'], $renewalType, Config::get('services.RENEWAL_NOTIFICATION_TYPE')['Service Agreement Renewal'], $frequency);

        $notificationTitle = Config::get('services.SERVICE_AGREEMENT_NOTIFICATION_TITLE');
        if(!empty($notificationDetails)) {
            if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], explode(",",$user['module_name']))) {
                    $eContractServiceAgreement = $this->getServiceAgreement($user, $notificationDetails);
                }
                // if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], explode(",",$user['module_name']))) {
                //     $TotalManagementserviceAgreement = $this->getTotalManagementServiceAgreement($user, $notificationDetails);
                // }

                $serviceAgreement = array_unique(array_merge($eContractServiceAgreement, $TotalManagementserviceAgreement), SORT_REGULAR);

                $notificationMessage = Config::get('services.SERVICE_AGREEMENT_MAIL_MESSAGE');
                if(!empty($serviceAgreement)) {
                    $mailMessage = $this->generateNotificationsAndUpdateMailMessage($serviceAgreement, $user, $notificationTitle, $notificationMessage);
                }
            } else if($renewalType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], explode(",",$user['module_name']))) {
                    $eContractServiceAgreement = $this->getExpiredServiceAgreement($user, $notificationDetails);
                }
                // if(in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], explode(",",$user['module_name']))) {
                    // $TotalManagementserviceAgreement = $this->getTotalManagementExpiredServiceAgreement($user, $notificationDetails);
                // }

                $serviceAgreement = array_unique(array_merge($eContractServiceAgreement, $TotalManagementserviceAgreement), SORT_REGULAR);

                $notificationMessage = Config::get('services.SERVICE_AGREEMENT_EXPIRY_MAIL_MESSAGE');
                if(!empty($serviceAgreement)) {
                    $mailMessage = $this->generateNotificationsAndUpdateMailMessage($serviceAgreement, $user, $notificationTitle, $notificationMessage);
                }
            }
        }

        if (!empty($mailMessage)) {
            $params['company_id'] = $user['company_id'];
            $params['mail_message'] = $mailMessage;
        }
        return $params;
    }

    /**
     * Get service agreements that are expiring within the next 3 months
     *
     * @param array $user An array containing the user's data
     * @return Collection if notification enabled, otherwise void
     */
    private function getServiceAgreement($user, $notificationDetails)
    {
        return EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
            ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->whereDate('e-contract_project.valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
            ->whereDate('e-contract_project.valid_until', '>=', Carbon::now())
            ->where('e-contract_applications.company_id', $user['company_id'])
            ->get()->toArray();
    }

    // /**
    //  * Get service agreements that are expiring within the next 3 months
    //  *
    //  * @param array $user An array containing the user's data
    //  * @return Collection if notification enabled, otherwise void
    //  */
    // private function getTotalManagementServiceAgreement($user, $notificationDetails)
    // {
    //     return TotalManagementProject::leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
    //         ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
    //         ->select('total_management_project.id', 'total_management_project.name', /*'total_management_project.valid_until',*/ 'crm_prospects.company_name')
    //         ->distinct('total_management_project.id', 'total_management_project.name', /*'total_management_project.valid_until',*/ 'crm_prospects.company_name')
    //         // ->whereDate('total_management_project.valid_until', '<', Carbon::now()->addDays($notificationDetails[0]['renewal_duration_in_days']))
    //         // ->whereDate('total_management_project.valid_until', '>=', Carbon::now())
    //         ->where('total_management_applications.company_id', $user['company_id'])
    //         ->get()->toArray();
    // }

    /**
     * Get service agreements that are expiring within the next 3 months
     *
     * @param array $user An array containing the user's data
     * @return Collection if notification enabled, otherwise void
     */
    private function getExpiredServiceAgreement($user, $notificationDetails)
    {
        return EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
            ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->whereDate('e-contract_project.valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
            ->whereDate('e-contract_project.valid_until', '<', Carbon::now())
            ->where('e-contract_applications.company_id', $user['company_id'])
            ->get()->toArray();
    }

//     /**
//     * Get service agreements that are expiring within the next 3 months
//     *
//     * @param array $user An array containing the user's data
//     * @return Collection if notification enabled, otherwise void
//     */
//    private function getTotalManagementExpiredServiceAgreement($user, $notificationDetails)
//    {
//         return TotalManagementProject::leftjoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
//         ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
//         ->select('total_management_project.id', 'total_management_project.name', /*'total_management_project.valid_until',*/ 'crm_prospects.company_name')
//         ->distinct('total_management_project.id', 'total_management_project.name', /*'total_management_project.valid_until',*/ 'crm_prospects.company_name')
//         // ->whereDate('total_management_project.valid_until', '>=', Carbon::now()->subDays($notificationDetails[0]['expired_duration_in_days']))
//         // ->whereDate('total_management_project.valid_until', '<', Carbon::now())
//         ->where('total_management_applications.company_id', $user['company_id'])
//         ->get()->toArray();
//    }

    /**
     * Generates notifications and updates mail message.
     *
     * @param $serviceAgreement - The service agreement array.
     * @param array $user The user array.
     * @param string $notificationTitle The notification title.
     * @param string $notificationMessage The notification message.
     * @return string The generated mail message.
     */
    private function generateNotificationsAndUpdateMailMessage($serviceAgreement, $user, $notificationTitle, $notificationMessage): string
    {
        $mailMessage = '';

        foreach ($serviceAgreement as $row) {
            $params = [
                'user_id' => $user['id'],
                'from_user_id' => 1,
                'type' => $notificationTitle,
                'title' => $notificationTitle,
                'message' => $this->formMessage($row),
                'status' => 1,
                'read_flag' => 0,
                'created_by' => 1,
                'modified_by' => 1,
            ];

            $this->insertNotification($params);
            $mailMessage .= $params['message'] . ' <br/>';
        }

        return $mailMessage;
    }

    /**
     * @param array $data
     * @return string
     */
    private function formMessage($data): string
    {
        return $data['company_name'] . ' - ' . $data['name'];
    }

    /**
     * Inserts a dispatch notification into the system.
     *
     * @param array $params The parameters for the dispatch notification.
     *   - company_id (int) The ID of the company.
     *   - message (string) The message of the notification.
     *
     * @return bool True if the dispatch notification was successfully inserted, false otherwise.
     */
    /* PHP */
    public function insertDispatchNotification($params): bool
    {
        if (!empty($params['company_id']) && !empty($params['message'])) {
            $this->insertNotification($params);
            $this->handleAdminUsers($params);
            $this->handleEmployeeUsers($params);
        }
        return true;
    }

    /**
     * Retrieves the admin users for a specific company.
     *
     * @param int $companyId The ID of the company.
     *
     * @return Collection The collection of admin users matching the specified criteria.
     */
    private function getAdminUsers($companyId): Collection
    {
        return User::where('user_type', 'Admin')
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->select('id', 'name', 'email', 'user_type', 'company_id', 'reference_id')
            ->distinct()
            ->get();
    }

    /**
     * Retrieves a list of employee users based on the provided company and employee ID.
     *
     * @param int $companyId The ID of the company.
     * @param int $employeeId The ID of the employee.
     *
     * @return Collection A collection of employee users.
     */
    private function getEmployeeUsers($companyId, $employeeId): Collection
    {
        return User::join('user_role_type', 'users.id', '=', 'user_role_type.user_id')
            ->join('role_permission', 'user_role_type.role_id', '=', 'role_permission.role_id')
            ->join('modules', 'role_permission.module_id', '=', 'modules.id')
            ->where('users.company_id', $companyId)
            ->where('users.user_type', '!=', 'Admin')
            ->where('users.id', '!=', $employeeId)
            ->where('users.status', 1)
            ->whereNull('users.deleted_at')
            ->where('modules.module_name', Config::get('services.ACCESS_MODULE_TYPE')[10])
            ->select('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id', DB::raw('GROUP_CONCAT(modules.module_name SEPARATOR ",") AS module_name'))
            ->groupBy('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->distinct('users.id','users.name', 'users.email', 'users.user_type', 'users.company_id', 'users.reference_id')
            ->get();
    }

    /**
     * Handles the dispatch notifications for admin users.
     *
     * @param array $params The parameters for handling admin users.
     *   - company_id (int) The ID of the company.
     *
     * @return void
     */
    private function handleAdminUsers($params): void
    {   
        if (DB::getDriverName() !== 'sqlite') {
            $adminUsers = $this->getAdminUsers($params['company_id']);
            foreach ($adminUsers as $user) {
                $params['user_id'] = $user['id'];
                $this->insertNotification($params);
                $this->dispatchNotifications($user, $params['message']);
            }
        }
    }

    /**
     * Handles dispatch notifications for employee users.
     *
     * @param array $params The parameters for the dispatch notification.
     *   - company_id (int) The ID of the company.
     *   - user_id (int) The ID of the user.
     *
     * @return void
     */
    private function handleEmployeeUsers($params): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $employeeUsers = $this->getEmployeeUsers($params['company_id'], $params['user_id']);
            foreach ($employeeUsers as $user) {
                $params['user_id'] = $user['id'];
                $this->insertNotification($params);
                $this->dispatchNotifications($user, $params['message']);
            }
        }
    }

    /**
     * Dispatches notifications to a user.
     *
     * @param mixed $user The user to send the notification to.
     * @param string $message The message of the notification.
     *
     * @return void
     */
    private function dispatchNotifications($user, $message): void
    {
        dispatch(new RunnerNotificationMail(Config::get('database.connections.mysql.database'), $user, $message))
            ->onQueue(Config::get('services.RUNNER_NOTIFICATION_MAIL'))
            ->onConnection(Config::get('services.QUEUE_CONNECTION'));
    }

    /**
     * Process the dispatch summary notification
     * 
     * @param $user
     * 
     * @return mixed Returns the notification message
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
     * Get the current company's notification details
     * 
     * @param int $companyId ID of the current company
     * @param string $notificationType type of the notification whether renewal or expired
     * @param int $notificationId Notification ID from notification master
     * @param string $frequency - notification cycle (daily, weekly, monthly)
     * 
     * @return array Returns the renewal notification details
     */
    public function getNotificationDetails(int $companyId, string $notificationType, int $notificationId, string $frequency): array
    {
        if($notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
           return $this->getRenewalNotificationDetails($companyId, $notificationId, $frequency);
        } else if($notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
           return $this->getExpiredNotificationDetails($companyId, $notificationId, $frequency);
        }
    }

    /** Get the current company's renewal notification details
    * 
    * @param int $company_id ID of the current company
    * @param string $notificationId Notification ID from notification master
    * @param string $frequency - notification cycle (daily, weekly, monthly)
    * 
    * @return array Returns the renewal notification details
    */
   public function getRenewalNotificationDetails(int $companyId, int $notificationId, string $frequency): array
   {
       return $this->companyRenewalNotification->where('notification_id', $notificationId)
                    ->where('company_id', $companyId)
                    ->where('renewal_notification_status', self::RENEWAL_NOTIFICATION_ON)
                    ->where('renewal_frequency_cycle', $frequency)
                    ->select('renewal_notification_status', 'renewal_duration_in_days', 'renewal_frequency_cycle')
                    ->get()->toArray();
   }

   /** Get the current company's expired notification details
    * 
    * @param int $company_id ID of the current company
    * @param string $notificationId Notification ID from notification master
    * @param string $frequency - notification cycle (daily, weekly, monthly)
    * 
    * @return array Returns the expired notification details
    */
    public function getExpiredNotificationDetails(int $companyId, int $notificationId, string $frequency): array
    {
        return $this->companyRenewalNotification->where('notification_id', $notificationId)
                     ->where('company_id', $companyId)
                     ->where('expired_notification_status', self::EXPIRED_NOTIFICATION_ON)
                     ->where('expired_frequency_cycle', $frequency)
                     ->select('expired_notification_status', 'expired_duration_in_days', 'expired_frequency_cycle')
                     ->get()->toArray();
    }
}

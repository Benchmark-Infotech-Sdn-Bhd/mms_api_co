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

class NotificationServices
{
    private Notifications $notifications;

    /**
     * NotificationServices constructor.
     * @param Notifications $notifications
     */
    public function __construct(Notifications $notifications)
    {
        $this->notifications = $notifications;
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
     * @return array Returns an array with the following keys:
     *   - isUpdated (boolean): Indicates whether the notifications were sent successfully.
     *   - message (string): A message indicating the status of the update process.
     */
    public function renewalNotifications(): array
    {
        $this->userRenewalNotification();
        $this->adminRenewalNotification();

        return [
            "isUpdated" => true,
            "message" => "Updated Successfully"
        ];
    }

    /**
     * Send renewal notification to employee users.
     *
     * @return void
     */
    public function userRenewalNotification(): void
    {
        $employeeUsers = $this->getEmployeeUsersFromModules();
        foreach ($employeeUsers as $user) {
            $notifications = $this->getUserModulesAndNotification($user);
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
     * @return array The notifications array containing the generated notifications.
     */
    public function getUserModulesAndNotification($user): array
    {
        $notifications = [];
        $userModules = $this->getUpdatedUserModules($user);

        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules)) {
            $notifications['plksRenewal'] = $this->plksRenewalNotifications($user);
            $notifications['callingVisaRenewal'] = $this->callingVisaRenewalNotifications($user);
            $notifications['specialPassRenewal'] = $this->specialPassRenewalNotifications($user);
        }
        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules)) {
            $notifications['serviceAgreement'] = $this->serviceAgreementNotifications($user);
        }
        if (in_array(Config::get('services.ACCESS_MODULE_TYPE')[4], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[5], $userModules) || in_array(Config::get('services.ACCESS_MODULE_TYPE')[6], $userModules)) {
            $notifications['fomemaRenewal'] = $this->fomemaRenewalNotifications($user);
            $notifications['passportRenewal'] = $this->passportRenewalNotifications($user);
            $notifications['insuranceRenewal'] = $this->insuranceRenewalNotifications($user);
            $notifications['entryVisaRenewal'] = $this->entryVisaRenewalNotifications($user);
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
     * @return void
     */
    public function adminRenewalNotification(): void
    {
        $notifications = [
            'fomemaRenewal',
            'passportRenewal',
            'plksRenewal',
            'callingVisaRenewal',
            'specialPassRenewal',
            'insuranceRenewal',
            'entryVisaRenewal',
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
                    $message[$notification] = $this->{$notification.'Notifications'}($user);
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
        return (!empty($message['fomemaRenewal']) || !empty($message['passportRenewal']) || !empty($message['plksRenewal']) || !empty($message['callingVisaRenewal']) || !empty($message['specialPassRenewal']) || !empty($message['insuranceRenewal']) || !empty($message['entryVisaRenewal']) || !empty($message['serviceAgreement']));
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
        $mailMessage = null
    ): array
    {
        $durationDate = $durationType == 'MONTHS'
            ? Carbon::now()->addMonths($duration)
            : Carbon::now()->addDays($duration);

        return [
            'user_id' => $user['id'],
            'from_user_id' => 1,
            'type' => $type,
            'title' => $title,
            'message' => $count . " " . $message,
            'mail_message' =>  $mailMessage . " " . $count . " " . Config::get('services.COMMON_EXPIRY_MAIL_MESSAGE'),
            'status' => 1,
            'read_flag' => 0,
            'created_by' => 1,
            'modified_by' => 1,
        ];
    }


    /**
     * Generates Fomema renewal notifications for a user
     *
     * @param array $user The user for whom to generate the notifications
     * @return array The generated notification parameters
     */
    public function fomemaRenewalNotifications($user)
    {
        $params = [];
        $fomemaRenewalNotificationsCount = Workers::whereDate('fomema_valid_until', '<', Carbon::now()->addMonths(3))
                                ->whereDate('fomema_valid_until', '>=', Carbon::now())
                                ->select('id')
                                ->where('company_id', $user['company_id'])
                                ->count();

        if (isset($fomemaRenewalNotificationsCount) && $fomemaRenewalNotificationsCount != 0) {

            $params = $this->formNotificationInsertData($user, $fomemaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.FOMEMA_NOTIFICATION_TITLE'), Config::get('services.FOMEMA_NOTIFICATION_MESSAGE'), 3, 'MONTHS', Config::get('services.FOMEMA_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Generates passport renewal notifications for a user
     *
     * @param array $user The user for whom the notifications should be generated
     * @return array The generated notification parameters
     */
    public function passportRenewalNotifications($user): array
    {
        $params = [];
        $passportRenewalNotificationsCount = Workers::whereDate('passport_valid_until', '<', Carbon::now()->addMonths(3))
                                ->whereDate('passport_valid_until', '>=', Carbon::now())
                                ->select('id')
                                ->where('company_id', $user['company_id'])
                                ->count();

        if (isset($passportRenewalNotificationsCount) && $passportRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $passportRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PASSPORT_NOTIFICATION_TITLE'), Config::get('services.PASSPORT_NOTIFICATION_MESSAGE'), 3, 'MONTHS', Config::get('services.PASSPORT_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Count and generate PLKS renewal notifications for a user.
     *
     * @param array $user The user information
     * @return array The generated notification parameters
     */
    public function plksRenewalNotifications($user): array
    {
        $params = [];
        $plksRenewalNotificationsCount = Workers::whereDate('plks_expiry_date', '<', Carbon::now()->addMonths(2))
                                ->whereDate('plks_expiry_date', '>=', Carbon::now())
                                ->select('id')
                                ->where('company_id', $user['company_id'])
                                ->count();

        if (isset($plksRenewalNotificationsCount) && $plksRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $plksRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.PLKS_NOTIFICATION_TITLE'), Config::get('services.PLKS_NOTIFICATION_MESSAGE'), 2, 'MONTHS', Config::get('services.PLKS_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Calls visa renewal notifications for a user.
     *
     * @param array $user The user details.
     * @return array The notification parameters.
     */
    public function callingVisaRenewalNotifications($user): array
    {
        $params = [];
        $callingVisaRenewalNotificationsCount = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                    ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addMonth())
                                    ->whereDate('worker_visa.calling_visa_valid_until', '>=', Carbon::now())
                                    ->select('workers.id')
                                    ->where('workers.company_id', $user['company_id'])
                                    ->count();

        if (isset($callingVisaRenewalNotificationsCount) && $callingVisaRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $callingVisaRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.CALLING_VISA_NOTIFICATION_TITLE'), Config::get('services.CALLING_VISA_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.CALLING_VISA_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Generates special pass renewal notifications for a given user.
     *
     * @param array $user The user for whom the notifications are generated.
     * @return array The generated notification parameters.
     */
    public function specialPassRenewalNotifications($user): array
    {
        $params = [];
        $specialPassRenewalNotificationsCount = Workers::whereDate('special_pass_valid_until', '<', Carbon::now()->addMonth())
                                    ->whereDate('special_pass_valid_until', '>=', Carbon::now())
                                    ->select('id')
                                    ->where('company_id', $user['company_id'])
                                    ->count();

        if (isset($specialPassRenewalNotificationsCount) && $specialPassRenewalNotificationsCount != 0) {
            $params = $this->formNotificationInsertData($user, $specialPassRenewalNotificationsCount, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_TITLE'), Config::get('services.SPECIAL_PASS_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.SPECIAL_PASS_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Checks for insurance renewal notifications for a given user.
     *
     * @param array $user The user for which to check insurance renewal notifications.
     * @return array Returns an array containing the notification parameters if there are insurance renewal notifications, otherwise an empty array.
     */
    public function insuranceRenewalNotifications($user): array
    {
        $params = [];
        $insuranceRenewalNotifications = Workers::join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')
                                ->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addMonth())
                                ->whereDate('worker_insurance_details.insurance_expiry_date', '>=', Carbon::now())
                                ->select('workers.id')
                                ->where('workers.company_id', $user['company_id'])
                                ->count();

        if (isset($insuranceRenewalNotifications) && $insuranceRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $insuranceRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.INSURANCE_NOTIFICATION_TITLE'), Config::get('services.INSURANCE_NOTIFICATION_MESSAGE'), 1, 'MONTHS', Config::get('services.INSURANCE_MAIL_MESSAGE'));
            $this->insertNotification($params);
            $params['company_id'] = $user['company_id'];
        }
        return $params;
    }

    /**
     * Retrieves the entry visa renewal notifications for a user.
     *
     * @param array $user An array containing the company_id of the user.
     * @return array An array with the notification data if there are entry visa renewal notifications, otherwise an empty array.
     */
    public function entryVisaRenewalNotifications($user): array
    {
        $params = [];
        $entryVisaRenewalNotifications = Workers::join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                                ->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now()->addDays(15))
                                ->whereDate('worker_visa.entry_visa_valid_until', '>=', Carbon::now())
                                ->select('workers.id')
                                ->where('workers.company_id', $user['company_id'])
                                ->count();

        if (isset($entryVisaRenewalNotifications) && $entryVisaRenewalNotifications != 0) {
            $params = $this->formNotificationInsertData($user, $entryVisaRenewalNotifications, Config::get('services.NOTIFICATION_TYPE'), Config::get('services.ENTRY_VISA_NOTIFICATION_TITLE'), Config::get('services.ENTRY_VISA_NOTIFICATION_MESSAGE'), 15, 'DAYS', Config::get('services.ENTRY_VISA_MAIL_MESSAGE'));
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
    public function serviceAgreementNotifications($user): array
    {
        $params['mail_message'] = '';
        $params['company_id'] = '';

        $notificationTitle = Config::get('services.SERVICE_AGREEMENT_NOTIFICATION_TITLE');
        $notificationMessage = Config::get('services.SERVICE_AGREEMENT_MAIL_MESSAGE');

        $serviceAgreement = $this->getServiceAgreement($user);

        $mailMessage = $this->generateNotificationsAndUpdateMailMessage($serviceAgreement, $user, $notificationTitle, $notificationMessage);

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
     * @return Collection
     */
    private function getServiceAgreement($user): Collection
    {
        return EContractProject::leftjoin('e-contract_applications', 'e-contract_applications.id', '=', 'e-contract_project.application_id')
            ->leftjoin('crm_prospects', 'crm_prospects.id', '=', 'e-contract_applications.crm_prospect_id')
            ->select('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->distinct('e-contract_project.id', 'e-contract_project.name', 'e-contract_project.valid_until', 'crm_prospects.company_name')
            ->whereDate('e-contract_project.valid_until', '<', Carbon::now()->addMonths(3))
            ->whereDate('e-contract_project.valid_until', '>=', Carbon::now())
            ->where('e-contract_applications.company_id', $user['company_id'])
            ->get();
    }

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
                'message' => $this->formMessage($row, $notificationMessage),
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
     * @param string $notificationMessage
     * @return string
     */
    private function formMessage($data, $notificationMessage): string
    {
        return $data['company_name'] . ' - ' . $data['name'] . ' ' . $notificationMessage . ' ' . $data['valid_until'];
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

}

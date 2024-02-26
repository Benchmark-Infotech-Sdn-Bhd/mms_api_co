<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\OnboardingDispatch;
use App\Models\OnboardingDispatchAttachments;
use App\Services\AuthServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;

class DispatchManagementServices
{
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_ASSIGNED = 'Assigned';

    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';

    /**
     * @var OnboardingDispatch
     */
    private OnboardingDispatch $onboardingDispatch;
    /**
     * @var OnboardingDispatchAttachments
     */
    private OnboardingDispatchAttachments $onboardingDispatchAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var NotificationServices
     */
    private NotificationServices $notificationServices;
    /**
     * @var Employee
     */
    private Employee $employee;

    /**
     * dispatchManagementServices constructor.
     *
     * @param OnboardingDispatch $onboardingDispatch Instance of the OnboardingDispatch class
     * @param OnboardingDispatchAttachments $onboardingDispatchAttachments Instance of the OnboardingDispatchAttachments class
     * @param Storage $storage Instance of the Storage class
     * @param AuthServices $authServices Instance of the AuthServices class
     * @param NotificationServices $notificationServices Instance of the NotificationServices class
     * @param Employee $employee Instance of the Employee class
     *
     * @return void
     *
     */
    public function __construct(
        OnboardingDispatch              $onboardingDispatch,
        OnboardingDispatchAttachments   $onboardingDispatchAttachments,
        Storage                         $storage,
        AuthServices                    $authServices,
        NotificationServices            $notificationServices,
        Employee                        $employee
    )
    {
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingDispatchAttachments = $onboardingDispatchAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->notificationServices = $notificationServices;
        $this->employee = $employee;
    }
    /**
     * validate the update create request data
     *
     * @return array The validation rules for the input data.
     */
    public function createValidation(): array
    {
        return [
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required',
            'reference_number' => 'required',
            'employee_id' => 'required',
            'from' => 'required',
            'calltime' => 'required|date|date_format:Y-m-d',
            'area' => 'required',
            'employer_name' => 'required',
            'phone_number' => 'required'
        ];
    }
    /**
     * validate the update update request data
     *
     * @return array The validation rules for the input data.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required',
            'employee_id' => 'required',
            'from' => 'required',
            'calltime' => 'required|date|date_format:Y-m-d',
            'area' => 'required',
            'employer_name' => 'required',
            'phone_number' => 'required'
        ];
    }

    /**
     * Validate the given request data.
     *
     * @param $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
	    $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * List the Dispatch
     *
     * @param $request The request data containing the 'search', 'status_filter', 'company_id'
     *
     * @return mixed Returns the paginated list of dispatch.
     */
    public function list($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $list = $this->onboardingDispatch
        ->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->whereIn('employee.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $this->applySearchFilter($query,$request);
        })
        ->select('onboarding_dispatch.id', 'employee.employee_name', 'onboarding_dispatch.date', 'onboarding_dispatch.calltime', 'onboarding_dispatch.reference_number')
        ->selectRaw("(CASE WHEN (onboarding_dispatch.dispatch_status = 'Completed') THEN onboarding_dispatch.dispatch_status
        WHEN (onboarding_dispatch.dispatch_status = 'Assigned' AND onboarding_dispatch.calltime > '".Carbon::now()."') THEN onboarding_dispatch.dispatch_status
        ELSE 'Pending' END) as status")
        ->distinct('onboarding_dispatch.id')
        ->orderBy('onboarding_dispatch.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));

        $assigned_count = $this->getAssignedDispatchCount($request);

        $completed_count = $this->getCompletedDispatchCount($request);

        $pending_count = $this->getPendingDispatchCount($request);

        return [
            'assigned_count' => $assigned_count,
            'completed_count' => $completed_count,
            'pending_count' => $pending_count,
            'data' => $list,
        ];
    }

    /**
     * Apply search filter to the query.
     *
     * @param array $request The request data containing the search keyword.
     *
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        $search = $request['search'] ?? '';
        $statusFilter = $request['status_filter'] ?? '';
        if(!empty($search)) {
                $query->where('employee.employee_name', 'like', '%'.$search.'%')
                ->orWhere('onboarding_dispatch.reference_number', 'like', '%'.$search.'%');
        }
        if(!empty($statusFilter)) {
            if($statusFilter == self::STATUS_COMPLETED){
                $query->where('onboarding_dispatch.dispatch_status', self::STATUS_COMPLETED);
            }else if($statusFilter == self::STATUS_ASSIGNED){
                $query->where('onboarding_dispatch.dispatch_status', self::STATUS_ASSIGNED)
                ->where('onboarding_dispatch.calltime', '>', Carbon::now());
            }else{
                $query->where('onboarding_dispatch.dispatch_status', self::STATUS_ASSIGNED)
                ->where('onboarding_dispatch.calltime', '<', Carbon::now());
            }
        }
    }

    /**
     * Get the assigned dispatch count
     *
     * @param array $request The request data containing the company_id key.
     *
     * @return int Returns an assigned dispatch count
     */
    private function getAssignedDispatchCount($request){
        return $this->onboardingDispatch
            ->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
            ->whereIn('employee.company_id', $request['company_id'])
            ->where('onboarding_dispatch.dispatch_status', self::STATUS_ASSIGNED)
            ->where('onboarding_dispatch.calltime', '>', Carbon::now())
            ->distinct('onboarding_dispatch.id')
            ->count('onboarding_dispatch.id');
    }

    /**
     * Get the completed dispatch count
     *
     * @param array $request The request data containing the company_id key.
     *
     * @return int Returns the completed dispatch count
     */
    private function getCompletedDispatchCount($request){
        return $this->onboardingDispatch
            ->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
            ->whereIn('employee.company_id', $request['company_id'])
            ->where('onboarding_dispatch.dispatch_status', self::STATUS_COMPLETED)
            ->distinct('onboarding_dispatch.id')
            ->count('onboarding_dispatch.id');
    }

    /**
     * Get the pending dispatch count
     *
     * @param array $request The request data containing the company_id key.
     *
     * @return int Returns the pending dispatch count
     */
    private function getPendingDispatchCount($request){
        return $this->onboardingDispatch
            ->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
            ->whereIn('employee.company_id', $request['company_id'])
            ->where('onboarding_dispatch.dispatch_status', self::STATUS_ASSIGNED)
            ->where('onboarding_dispatch.calltime', '<', Carbon::now())
            ->distinct('onboarding_dispatch.id')
            ->count('onboarding_dispatch.id');
    }

    /**
     * Show the Dispatch detail
     *
     * @param $request The request data containing the company_id, id key
     *
     * @return mixed Returns the dispatch record
     */
    public function show($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->onboardingDispatch->join('employee', function ($join) use ($request) {
            $join->on('employee.id', '=', 'onboarding_dispatch.employee_id')
                 ->whereIn('employee.company_id', $request['company_id']);
        })->select('onboarding_dispatch.id', 'onboarding_dispatch.onboarding_attestation_id', 'onboarding_dispatch.date', 'onboarding_dispatch.time', 'onboarding_dispatch.reference_number', 'onboarding_dispatch.employee_id', 'onboarding_dispatch.from', 'onboarding_dispatch.calltime', 'onboarding_dispatch.area', 'onboarding_dispatch.employer_name', 'onboarding_dispatch.phone_number', 'onboarding_dispatch.remarks', 'onboarding_dispatch.created_by', 'onboarding_dispatch.modified_by', 'onboarding_dispatch.created_at', 'onboarding_dispatch.updated_at', 'onboarding_dispatch.deleted_at', 'onboarding_dispatch.dispatch_status', 'onboarding_dispatch.job_type', 'onboarding_dispatch.passport', 'onboarding_dispatch.document_name', 'onboarding_dispatch.payment_amount', 'onboarding_dispatch.worker_name', 'onboarding_dispatch.acknowledgement_remarks', 'onboarding_dispatch.acknowledgement_date')->with('dispatchAttachments')->find($request['id']);
    }

    /**
     * create dispatch record
     *
     * @param array $request The request data containing the create data
     *
     * @return mixed Returns the created dispatch data
     */
    private function createDispathRecord($request)
    {
        return $this->onboardingDispatch->create([
            'onboarding_attestation_id' => $request['onboarding_attestation_id'] ?? 0,
            'date' => $request['date'] ?? '',
            'time' => $request['time'] ?? '',
            'reference_number' => $request['reference_number'],
            'employee_id' => $request['employee_id'] ?? '',
            'from' => $request['from'] ?? '',
            'calltime' => $request['calltime'] ?? '',
            'area' => $request['area'] ?? '',
            'employer_name' => $request['employer_name'] ?? '',
            'phone_number' => $request['phone_number'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'job_type' => $request['job_type'] ?? '',
            'passport' => $request['passport'] ?? '',
            'document_name' => $request['document_name'] ?? '',
            'payment_amount' => $request['payment_amount'] ?? 0,
            'worker_name' => $request['worker_name'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
    }

    /**
     * Upload attachment of dispatch.
     *
     * @param array $request
     *              attachment (file)
     * @param array $params
     * @param int $onboardingDispatchId
     *
     * @return void
     */
    private function uploadAttachment($request, $params, $onboardingDispatchId): void
    {
        if (request()->hasFile('attachment') && isset($onboardingDispatchId)) {
            foreach($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/onboardingDispatch/attachment/'. $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->updateOrCreate(
                    [
                        "file_id" => $onboardingDispatchId,
                        "file_type" => 'Attachment',
                    ],
                    [
                    "file_name" => $fileName,
                    "file_url" =>  $fileUrl,
                    "created_by" => $params['created_by'] ?? 0,
                    "modified_by" => $params['modified_by'] ?? 0
                ]);
            }
        }
    }

    /**
     * Upload Acknowledgement attachment of dispatch.
     *
     * @param array $request
     *              attachment (file)
     * @param array $params
     * @param int $onboardingDispatchId
     *
     * @return void
     */
    private function uploadAcknowledgementAttachment($request, $params, $onboardingDispatchId): void
    {
        if (request()->hasFile('acknowledgement_attachment') && isset($onboardingDispatchId)) {
            foreach($request->file('acknowledgement_attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/onboardingDispatch/acknowledgementAttachment/'. $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->updateOrCreate(
                    [
                        "file_id" => $onboardingDispatchId,
                        "file_type" => 'Acknowledgement',
                    ],
                    [
                    "file_name" => $fileName,
                    "file_url" =>  $fileUrl,
                    "created_by" => $params['created_by'] ?? 0,
                    "modified_by" => $params['modified_by'] ?? 0
                ]);
            }
        }
    }

    /**
     * send create dispatch notification.
     *
     * @param array $request
     * @param array $params
     * @param object $user
     *
     * @return void
     */
    private function sendCreateDispatchNotification($request, $params, $user){
        $getUser = $this->getUser($request['employee_id']);
        if($getUser){
            $NotificationParams['user_id'] = $getUser['id'];
            $NotificationParams['from_user_id'] = $params['created_by'];
            $NotificationParams['type'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['title'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
            $NotificationParams['message'] = $request['reference_number'].' Dispatch is Assigned';
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = $params['created_by'];
            $NotificationParams['modified_by'] = $params['created_by'];
            $NotificationParams['company_id'] = $user['company_id'];
            $this->notificationServices->insertDispatchNotification($NotificationParams);
            dispatch(new \App\Jobs\RunnerNotificationMail(Config::get('database.connections.mysql.database'), $getUser,$NotificationParams['message']))->onQueue(Config::get('services.RUNNER_NOTIFICATION_MAIL'))->onConnection(Config::get('services.QUEUE_CONNECTION'));
        }
    }

    /**
     * Create the dispatch
     *
     * @param $request The request data containing the create data
     *
     * @return bool|array Returns An array of validation errors or boolean based on the processing result
     */
    public function create($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $onboardingDispatchCount = $this->onboardingDispatch->count();

        $request['reference_number'] = 'JO00000'.$onboardingDispatchCount + 1;

        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $employeeData = $this->employee::where('company_id', $params['company_id'])->find($request['employee_id']);
        if(is_null($employeeData)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $onboardingDispatch = $this->createDispathRecord($request);

        $this->sendCreateDispatchNotification($request, $params, $user);

        $this->uploadAttachment($request, $params, $onboardingDispatch['id']);

        $this->uploadAcknowledgementAttachment($request, $params, $onboardingDispatch['id']);

        return true;
    }

    /**
     * update the dispatch record
     *
     * @param array $request The request data containing the update data
     * @param array $params
     * @param object $user
     * @param object $onboardingDispatch
     *
     *
     * @return void
     */
    private function updateDispathRecord($request, $params, $user, $onboardingDispatch)
    {
        $onboardingDispatch->date =  $request['date'] ?? $onboardingDispatch->date;
        $onboardingDispatch->time =  $request['time'] ?? $onboardingDispatch->time;
        $onboardingDispatch->employee_id =  $request['employee_id'] ?? $onboardingDispatch->employee_id;
        $onboardingDispatch->from =  $request['from'] ?? $onboardingDispatch->from;
        $onboardingDispatch->calltime =  $request['calltime'] ?? $onboardingDispatch->calltime;
        $onboardingDispatch->area =  $request['area'] ?? $onboardingDispatch->area;
        $onboardingDispatch->employer_name =  $request['employer_name'] ?? $onboardingDispatch->employer_name;
        $onboardingDispatch->phone_number =  $request['phone_number'] ?? $onboardingDispatch->phone_number;
        $onboardingDispatch->remarks =  $request['remarks'] ?? $onboardingDispatch->remarks;
        $onboardingDispatch->job_type =  $request['job_type'] ?? $onboardingDispatch->job_type;
        $onboardingDispatch->passport =  $request['passport'] ?? $onboardingDispatch->passport;
        $onboardingDispatch->document_name =  $request['document_name'] ?? $onboardingDispatch->document_name;
        $onboardingDispatch->payment_amount =  $request['payment_amount'] ?? $onboardingDispatch->payment_amount;
        $onboardingDispatch->worker_name =  $request['worker_name'] ?? $onboardingDispatch->worker_name;
        $onboardingDispatch->acknowledgement_remarks =  $request['acknowledgement_remarks'] ?? $onboardingDispatch->acknowledgement_remarks;
        $onboardingDispatch->modified_by =  $params['modified_by'] ?? $onboardingDispatch->modified_by;

        $acknowledgementRemarks = $request['acknowledgement_remarks'] ?? '';
        if(!empty($acknowledgementRemarks)){
            $onboardingDispatch->dispatch_status = 'Completed';
            $onboardingDispatch->acknowledgement_date = Carbon::now();

            $getUser = $this->getUser($request['employee_id']);
            if($getUser){
                $NotificationParams['user_id'] = $getUser['id'];
                $NotificationParams['from_user_id'] = $params['modified_by'];
                $NotificationParams['type'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
                $NotificationParams['title'] = Config::get('services.DISPATCH_NOTIFICATION_TITLE');
                $NotificationParams['message'] = $onboardingDispatch->reference_number.' Dispatch is Completed';
                $NotificationParams['status'] = 1;
                $NotificationParams['read_flag'] = 0;
                $NotificationParams['created_by'] = $params['modified_by'];
                $NotificationParams['modified_by'] = $params['modified_by'];
                $NotificationParams['company_id'] = $user['company_id'];
                $this->notificationServices->insertDispatchNotification($NotificationParams);
                dispatch(new \App\Jobs\RunnerNotificationMail(Config::get('database.connections.mysql.database'), $getUser,$NotificationParams['message']))->onQueue(Config::get('services.RUNNER_NOTIFICATION_MAIL'))->onConnection(Config::get('services.QUEUE_CONNECTION'));
            }

        }
        $onboardingDispatch->save();
    }
    /**
     * Update the Dispatch
     *
     * @param $request The request data containing the update data
     *
     * @return bool|array Returns An array of validation errors or boolean based on the processing result
     */
    public function update($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $employeeData = $this->employee::where('company_id', $params['company_id'])->find($request['employee_id']);
        if(is_null($employeeData)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $onboardingDispatch = $this->onboardingDispatch->findOrFail($request['id']);

        $this->updateDispathRecord($request, $params, $user, $onboardingDispatch);

        $this->uploadAttachment($request, $params, $request['id']);

        $this->uploadAcknowledgementAttachment($request, $params, $request['id']);

        return true;
    }
    /**
     * Delete the attachment
     *
     * @param $request The request data containing the attachment_id key
     *
     * @return array Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function deleteAttachment($request): array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $data = $this->onboardingDispatchAttachments::join('onboarding_dispatch', 'onboarding_dispatch.id', '=', 'onboarding_dispatch_attachments.file_id')
        ->join('employee', function ($join) use ($request) {
            $join->on('employee.id', '=', 'onboarding_dispatch.employee_id')
                 ->whereIn('employee.company_id', $request['company_id']);
        })
        ->select('onboarding_dispatch_attachments.id')
        ->find($request['attachment_id']);

        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * get the user data
     *
     * @param $referenceId
     *
     * return mixed Returns the user data
     */
    public function getUser($referenceId)
    {
        return User::where('reference_id',$referenceId)->where('user_type','Employee')->first('id', 'name', 'email');
    }
}

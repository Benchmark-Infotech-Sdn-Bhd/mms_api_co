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

class DispatchManagementServices
{
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
     * dispatchManagementServices constructor.
     * @param OnboardingDispatch $onboardingDispatch
     * @param OnboardingDispatchAttachments $onboardingDispatchAttachments
     * @param Storage $storage
     * @param AuthServices $authServices
     * @param NotificationServices $notificationServices
     */
    public function __construct(OnboardingDispatch $onboardingDispatch, OnboardingDispatchAttachments $onboardingDispatchAttachments, Storage $storage, AuthServices $authServices, NotificationServices $notificationServices)
    {
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingDispatchAttachments = $onboardingDispatchAttachments;
        $this->storage = $storage;
        $this->authServices = $authServices;
        $this->notificationServices = $notificationServices;
    }
    /**
     * @return array
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
     * @return array
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
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $list = $this->onboardingDispatch
        ->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->whereIn('employee.company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('employee.employee_name', 'like', '%'.$request['search'].'%')
                ->orWhere('onboarding_dispatch.reference_number', 'like', '%'.$request['search'].'%');
            }
            if(isset($request['status_filter']) && !empty($request['status_filter'])) {
                if($request['status_filter'] == 'Completed'){
                    $query->where('onboarding_dispatch.dispatch_status', 'Completed');
                }else if($request['status_filter'] == 'Assigned'){
                    $query->where('onboarding_dispatch.dispatch_status', 'Assigned')
                    ->where('onboarding_dispatch.calltime', '>', Carbon::now());
                }else{
                    $query->where('onboarding_dispatch.dispatch_status', 'Assigned')
                    ->where('onboarding_dispatch.calltime', '<', Carbon::now());
                }
            }
        })
        ->select('onboarding_dispatch.id', 'employee.employee_name', 'onboarding_dispatch.date', 'onboarding_dispatch.calltime', 'onboarding_dispatch.reference_number')
        ->selectRaw("(CASE WHEN (onboarding_dispatch.dispatch_status = 'Completed') THEN onboarding_dispatch.dispatch_status
        WHEN (onboarding_dispatch.dispatch_status = 'Assigned' AND onboarding_dispatch.calltime > '".Carbon::now()."') THEN onboarding_dispatch.dispatch_status 
        ELSE 'Pending' END) as status")
        ->distinct('onboarding_dispatch.id')
        ->orderBy('onboarding_dispatch.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));

        $assigned_count = $this->onboardingDispatch->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->whereIn('employee.company_id', $request['company_id'])->where('onboarding_dispatch.dispatch_status', 'Assigned')->where('onboarding_dispatch.calltime', '>', Carbon::now())->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        $completed_count = $this->onboardingDispatch->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->whereIn('employee.company_id', $request['company_id'])->where('onboarding_dispatch.dispatch_status', 'Completed')->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        $pending_count = $this->onboardingDispatch->leftJoin('employee', 'employee.id', 'onboarding_dispatch.employee_id')
        ->whereIn('employee.company_id', $request['company_id'])->where('onboarding_dispatch.dispatch_status', 'Assigned')
        ->where('onboarding_dispatch.calltime', '<', Carbon::now())->distinct('onboarding_dispatch.id')->count('onboarding_dispatch.id');

        return [
            'assigned_count' => $assigned_count,
            'completed_count' => $completed_count,
            'pending_count' => $pending_count,
            'data' => $list,
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->onboardingDispatch->with('dispatchAttachments')->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function create($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['created_by'] = $user['id'];

        $onboardingDispatchCount = $this->onboardingDispatch->count();

        $request['reference_number'] = 'JO00000'.$onboardingDispatchCount + 1;

        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        
        $onboardingDispatch = $this->onboardingDispatch->create([
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

        $getUser = $this->getUser($request['employee_id']);
        if($getUser){
            $NotificationParams['user_id'] = $getUser['id'];
            $NotificationParams['from_user_id'] = $params['created_by'];
            $NotificationParams['type'] = 'Dispatches';
            $NotificationParams['title'] = 'Dispatches';
            $NotificationParams['message'] = $request['reference_number'].' Dispatch is Assigned';
            $NotificationParams['status'] = 1;
            $NotificationParams['read_flag'] = 0;
            $NotificationParams['created_by'] = $params['created_by'];
            $NotificationParams['modified_by'] = $params['created_by'];
            $this->notificationServices->insertNotification($NotificationParams);
            
            dispatch(new \App\Jobs\RunnerNotificationMail($getUser,$NotificationParams['message']));
        }
        

        if (request()->hasFile('attachment') && isset($onboardingDispatch['id'])) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/onboardingDispatch/attachment/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->create(
                    [
                    "file_id" => $onboardingDispatch['id'],
                    "file_name" => $fileName,
                    "file_type" => 'Attachment',
                    "file_url" =>  $fileUrl,
                    'created_by' => $params['created_by'] ?? 0,
                    'modified_by' => $params['created_by'] ?? 0
                ]);
            }
        }

        if (request()->hasFile('acknowledgement_attachment') && isset($onboardingDispatch['id'])) {
            foreach($request->file('acknowledgement_attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/onboardingDispatch/acknowledgementAttachment/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->create(
                    [
                    "file_id" => $onboardingDispatch['id'],
                    "file_name" => $fileName,
                    "file_type" => 'Acknowledgement',
                    "file_url" =>  $fileUrl,
                    'created_by' => $params['created_by'] ?? 0,
                    'modified_by' => $params['created_by'] ?? 0
                ]);
            }
        }

        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['modified_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingDispatch = $this->onboardingDispatch->findOrFail($request['id']);
        
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

        if(isset($request['acknowledgement_remarks']) && !empty($request['acknowledgement_remarks'])){
            $onboardingDispatch->dispatch_status = 'Completed'; 
            $onboardingDispatch->acknowledgement_date = Carbon::now(); 

            $getUser = $this->getUser($request['employee_id']);
            if($getUser){
                $NotificationParams['user_id'] = $getUser['id'];
                $NotificationParams['from_user_id'] = $params['modified_by'];
                $NotificationParams['type'] = 'Dispatches';
                $NotificationParams['title'] = 'Dispatches';
                $NotificationParams['message'] = $onboardingDispatch->reference_number.' Dispatch is Completed';
                $NotificationParams['status'] = 1;
                $NotificationParams['read_flag'] = 0;
                $NotificationParams['created_by'] = $params['modified_by'];
                $NotificationParams['modified_by'] = $params['modified_by'];
                $this->notificationServices->insertNotification($NotificationParams);
                
                dispatch(new \App\Jobs\RunnerNotificationMail($getUser,$NotificationParams['message']));
            }

        }
        $onboardingDispatch->save();

        if (request()->hasFile('attachment') && !empty($request['id'])) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/onboardingDispatch/attachment/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->updateOrCreate(
                    [
                        "file_id" => $request['id'],
                        "file_type" => 'Attachment',
                    ],
                    [
                    "file_name" => $fileName,
                    "file_url" =>  $fileUrl,
                    "created_by" => $params['modified_by'] ?? 0,
                    "modified_by" => $params['modified_by'] ?? 0
                ]);
            }
        }
        if (request()->hasFile('acknowledgement_attachment') && isset($request['id'])) {
            foreach($request->file('acknowledgement_attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/onboardingDispatch/acknowledgementAttachment/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->onboardingDispatchAttachments->updateOrCreate(
                    [
                        "file_id" => $request['id'],
                        "file_type" => 'Acknowledgement',
                    ],
                    [
                    "file_name" => $fileName,
                    "file_url" =>  $fileUrl,
                    "created_by" => $params['modified_by'] ?? 0,
                    "modified_by" => $params['modified_by'] ?? 0
                ]);
            }
        }
        return true;
    }
    /**
     * delete attachment
     * @param $request
     * @return array
     */    
    public function deleteAttachment($request): array
    {   
        $data = $this->onboardingDispatchAttachments::find($request['attachment_id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * get user
     * @param $request
     */    
    public function getUser($referenceId)
    {   
        return User::where('reference_id',$referenceId)->where('user_type','Employee')->first('id', 'name', 'email');
    }
}
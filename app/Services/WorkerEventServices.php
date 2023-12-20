<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerEvent;
use App\Models\WorkerEmployment;
use App\Models\WorkerEventAttachments;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthServices;
use Carbon\Carbon;

class WorkerEventServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerEvent
     */
    private WorkerEvent $workerEvent;
    /**
     * @var WorkerEventAttachments
     */
    private WorkerEventAttachments $workerEventAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var WorkerEmployment
     */
    private WorkerEmployment $workerEmployment;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * WorkerEventServices constructor.
     * @param Workers $workers
     * @param WorkerEvent $workerEvent;
     * @param WorkerEventAttachments $workerEventAttachments
     * @param Storage $storage
     * @param WorkerEmployment $workerEmployment
     * @param AuthServices $authServices
     */
    public function __construct(Workers $workers, WorkerEvent $workerEvent, WorkerEventAttachments $workerEventAttachments, Storage $storage, WorkerEmployment $workerEmployment, AuthServices $authServices)
    {
        $this->workers                = $workers;
        $this->workerEvent            = $workerEvent;
        $this->workerEventAttachments = $workerEventAttachments;
        $this->storage                = $storage;
        $this->workerEmployment = $workerEmployment;
        $this->authServices = $authServices;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'worker_id' => 'required',
            'event_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'event_type' => 'required',
            'flight_number' => 'regex:/^[a-zA-Z0-9]*$/',
            'departure_date' => 'date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'event_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'event_type' => 'required',
            'flight_number' => 'regex:/^[a-zA-Z0-9]*$/',
            'departure_date' => 'date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->workerEvent
            ->where('worker_id', $request['worker_id'])
            ->where(function ($query) use ($request) {
            if(isset($request['filter']) && !empty($request['filter'])) {
                $query->where('event_type', $request['filter']);
            }
        })
        ->select('id', 'worker_id', 'event_type', 'created_at', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }        
    /**
     * @param $request
     * @return array|bool
     */
    public function create($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $workerEvent = $this->workerEvent->create([
            'worker_id' => $request['worker_id'] ?? 0,
            'event_date' => $request['event_date'] ?? '',
            'event_type' => $request['event_type'] ?? '',
            'flight_number' => (isset($request['flight_number']) && !empty($request['flight_number'])) ? $request['flight_number'] : NULL,
            'departure_date' => (isset($request['departure_date']) && !empty($request['departure_date'])) ? $request['departure_date'] : NULL,
            'last_working_date' => (isset($request['last_working_day']) && !empty($request['last_working_day'])) ? $request['last_working_day'] : NULL,
            'remarks' => $request['remarks'] ?? '',
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by']
        ]);
        if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]){
            $this->workers->where('id', $request['worker_id'])
            ->update([
                "econtract_status" => $request['event_type'], 
                "modified_by" => $request['created_by']
            ]);
        } else if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]){
            $this->workers->where('id', $request['worker_id'])
            ->update([
                "total_management_status" => $request['event_type'], 
                "modified_by" => $request['created_by']
            ]);
        }
        
        if(isset($request['service_type']) && isset($request['project_id']) && isset($request['last_working_day']) && in_array($request['event_type'], Config::get('services.OTHERS_EVENT_TYPE'))){
            $this->workerEmployment->where([
                'project_id' => $request['project_id'],
                'worker_id' => $request['worker_id'],
                'service_type' => $request['service_type'],
            ])->update([
                'work_end_date' => $request['last_working_day'],
                'updated_at' => Carbon::now(), 
                'modified_by' => $request['created_by'],
                'event_type' => $request['event_type'],
                'event_id' => $workerEvent->id
            ]);
        } 
        
        if(request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'workers/event/' . $request['worker_id']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerEventAttachments->create([
                    'file_id' => $workerEvent->id,
                    'file_name' => $fileName,
                    'file_type' => 'Event Attachment',
                    'file_url' => $fileUrl,
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return array|bool
     */
    public function update($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $maxId = $this->workerEvent->where('worker_id', $request['worker_id'])->max('id');
        if($maxId != $request['id']) {
            return [
                'maxIdError' => true
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        $request['modified_by'] = $user['id'];
        $workerEvent = $this->workerEvent->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->find($request['id']);
        if(is_null($workerEvent)){
            return [
                'noRecords' => true
            ];
        }
        $workerEvent->worker_id = $request['worker_id'] ?? $workerEvent->worker_id;
        $workerEvent->event_date = (isset($request['event_date']) && !empty($request['event_date'])) ? $request['event_date'] : $workerEvent->event_date;
        $workerEvent->event_type = $request['event_type'] ?? $workerEvent->event_type;
        $workerEvent->flight_number = (isset($request['flight_number']) && !empty($request['flight_number'])) ? $request['flight_number'] : $workerEvent->flight_number;
        $workerEvent->departure_date = (isset($request['departure_date']) && !empty($request['departure_date'])) ? $request['departure_date'] : $workerEvent->departure_date;
        $workerEvent->last_working_date = (isset($request['last_working_day']) && !empty($request['last_working_day'])) ? $request['last_working_day'] : $workerEvent->last_working_date;
        $workerEvent->remarks = $request['remarks'] ?? '';
        $workerEvent->modified_by = $request['modified_by'];
        $workerEvent->save();

        if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]){
            $this->workers->where('id', $request['worker_id'])
            ->update([
                "econtract_status" => $request['event_type'], 
                "modified_by" => $request['modified_by']
            ]);
        } else if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]){
            $this->workers->where('id', $request['worker_id'])
            ->update([
                "total_management_status" => $request['event_type'], 
                "modified_by" => $request['modified_by']
            ]);
        }

        if(isset($request['service_type']) && isset($request['project_id']) && isset($request['last_working_day']) && in_array($request['event_type'], Config::get('services.OTHERS_EVENT_TYPE'))){
            $this->workerEmployment->where([
                'project_id' => $request['project_id'],
                'worker_id' => $request['worker_id'],
                'service_type' => $request['service_type'],
            ])->update([
                'work_end_date' => $request['last_working_day'],
                'updated_at' => Carbon::now(), 
                'modified_by' => $request['modified_by'],
                'event_type' => $request['event_type'],
                'event_id' => $workerEvent->id
            ]);
        } 

        if(request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'workers/event/' . $request['worker_id']. '/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerEventAttachments->create([
                    'file_id' => $workerEvent->id,
                    'file_name' => $fileName,
                    'file_type' => 'Event Attachment',
                    'file_url' => $fileUrl,
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
        }
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        return $this->workerEvent->with(['eventAttachments'])->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_event.*')->find($request['id']);
    }
    /**
     *
     * @param $request
     * @return bool
     */    
    public function deleteAttachment($request): bool
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);
        $data = $this->workerEventAttachments::join('worker_event', 'worker_event.id', 'worker_event_attachments.file_id')
        ->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->find($request['id']);
        if(is_null($data)) {
            return false;
        }
        $data->delete();
        return true;
    }    
}
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
use Carbon\Carbon;

class WorkerEventServices
{
    public const DEFAULT_VALUE = 0;
    public const ATTACHMENT_FILE_TYPE = 'Event Attachment';

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
     *
     * @param Workers $workers Instance of the Workers class
     * @param WorkerEvent $workerEvent Instance of the WorkerEvent class
     * @param WorkerEventAttachments $workerEventAttachments Instance of the WorkerEventAttachments class
     * @param Storage $storage Instance of the Storage class
     * @param WorkerEmployment $workerEmployment Instance of the WorkerEmployment class
     * @param AuthServices $authServices Instance of the AuthServices class
     *
     * @return void
     *
     */
    public function __construct(
        Workers                 $workers,
        WorkerEvent             $workerEvent,
        WorkerEventAttachments  $workerEventAttachments,
        Storage                 $storage,
        WorkerEmployment        $workerEmployment,
        AuthServices            $authServices
    )
    {
        $this->workers                = $workers;
        $this->workerEvent            = $workerEvent;
        $this->workerEventAttachments = $workerEventAttachments;
        $this->storage                = $storage;
        $this->workerEmployment = $workerEmployment;
        $this->authServices = $authServices;
    }
    /**
     * validate the create request data
     *
     * @return array The validation rules for the input data.
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
     * validate the update request data
     *
     * @return array The validation rules for the input data.
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
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return mixed Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
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
     * @param array $request The request data to be validated.
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
     * List the worker event
     *
     * @param $request
     *        worker_id (int) Id of the worker
     *        filter (string) filter type
     *
     * @return mixed Returns the paginated list of worker event.
     */
    public function list($request): mixed
    {
        return $this->workerEvent
            ->where('worker_id', $request['worker_id'])
            ->where(function ($query) use ($request) {
            if(!empty($request['filter'])) {
                $query->where('event_type', $request['filter']);
            }
        })
        ->select('id', 'worker_id', 'event_type', 'created_at', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Create the worker event
     *
     * @param $request  The request data
     *
     * @return array|bool An array of validation errors or boolean based on the processing result
     */
    public function create($request): array|bool
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $workerEvent = $this->createWorkerEvent($request);

        if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[2]){
            $this->updateWorkerEcontractStatus($request);
        } else if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]){
            $this->updateWorkerTotalManagementStatus($request);
        }

        if(isset($request['service_type']) && isset($request['project_id']) && isset($request['last_working_day']) && in_array($request['event_type'], Config::get('services.OTHERS_EVENT_TYPE'))){
            $this->updateWorkerEmployment($workerEvent, $request);
        }

        $this->uploadAttachment($request, $workerEvent);
        return true;
    }

    /**
     * create worker event.
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              event_date (date) event data
     *              event_type (string) event type
     *              flight_number (string) flight number
     *              departure_date (date) departure date
     *              last_working_day (date) last working date
     *              remarks (string) remarks
     *              created_by The ID of the user who created the event.
     *
     * @return mixed Returns the created worker event record.
     */
    private function createWorkerEvent($request): mixed
    {
        return $this->workerEvent->create([
            'worker_id' => $request['worker_id'] ?? self::DEFAULT_VALUE,
            'event_date' => $request['event_date'] ?? '',
            'event_type' => $request['event_type'] ?? '',
            'flight_number' => (isset($request['flight_number']) && !empty($request['flight_number'])) ? $request['flight_number'] : NULL,
            'departure_date' => (isset($request['departure_date']) && !empty($request['departure_date'])) ? $request['departure_date'] : NULL,
            'last_working_date' => (isset($request['last_working_day']) && !empty($request['last_working_day'])) ? $request['last_working_day'] : NULL,
            'remarks' => $request['remarks'] ?? '',
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by']
        ]);
    }

    /**
     * update e-Contract status.
     *
     * @param array $request
     *              worker_id (in) ID of the worker
     *              event_type (string) event type
     *              created_by The ID of the user who created the event.
     *
     * @return void.
     */
    private function updateWorkerEcontractStatus($request)
    {
        $this->workers->where('id', $request['worker_id'])
            ->update([
                "econtract_status" => $request['event_type'],
                "modified_by" => $request['created_by']
            ]);
    }

    /**
     * update total management status.
     *
     * @param array $request
     *              worker_id (in) ID of the worker
     *              event_type (string) event type
     *              created_by The ID of the user who created the event.
     *
     * @return void.
     */
    private function updateWorkerTotalManagementStatus($request)
    {
        $this->workers->where('id', $request['worker_id'])
            ->update([
                "total_management_status" => $request['event_type'],
                "modified_by" => $request['created_by']
            ]);
    }

    /**
     * update worker employment.
     *
     * @param object $workerEvent
     * @param array $request
     *              project_id (int) ID of the project
     *              worker_id (int) ID of the worker
     *              service_type (string) service type
     *              last_working_day (date) last working date
     *              event_type (string) event type
     *              created_by The ID of the user who modified the record.
     *
     * @return void
     *
     */
    private function updateWorkerEmployment($workerEvent, $request)
    {
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

    /**
     * Upload attachment of event.
     *
     * @param array $request
     *              attachment (file)
     * @param object $workerEvent
     *
     * @return void
     */
    private function uploadAttachment($request, $workerEvent): void
    {
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
                    'file_type' => self::ATTACHMENT_FILE_TYPE,
                    'file_url' => $fileUrl,
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);
            }
        }
    }

    /**
     * Retrieve the worker event record based on requested data.
     *
     *
     * @param array $request
     *              company_id (array) ID of the user company
     *              id (int) ID of the event
     *
     * @return mixed Returns the event data

     */
    private function getWorkerEvent($request)
    {
        return $this->workerEvent->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_event.id', 'worker_event.worker_id', 'worker_event.event_date', 'worker_event.event_type', 'worker_event.flight_number', 'worker_event.departure_date', 'worker_event.remarks', 'worker_event.created_by', 'worker_event.modified_by', 'worker_event.last_working_date', 'worker_event.created_at', 'worker_event.updated_at', 'worker_event.deleted_at')
        ->find($request['id']);
    }

    /**
     * Update the worker event
     *
     * @param $request The request data
     *
     * @return array|bool  An array of validation errors or boolean based on the processing result
     */
    public function update($request): array|bool
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $maxId = $this->workerEvent->where('worker_id', $request['worker_id'])->max('id');
        if($maxId != $request['id']) {
            return [
                'maxIdError' => true
            ];
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $workerEvent = $this->getWorkerEvent($request);
        if(is_null($workerEvent)){
            return [
                'unauthorizedError' => true
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
            $this->updateWorkerEcontractStatus($request);
        } else if(isset($request['service_type']) && $request['service_type'] == Config::get('services.WORKER_MODULE_TYPE')[1]){
            $this->updateWorkerTotalManagementStatus($request);
        }

        if(isset($request['service_type']) && isset($request['project_id']) && isset($request['last_working_day']) && in_array($request['event_type'], Config::get('services.OTHERS_EVENT_TYPE'))){
            $this->updateWorkerEmployment($workerEvent, $request);
        }
        $this->uploadAttachment($request, $workerEvent);
        return true;
    }
    /**
     * Show the worker event
     *
     * @param $request The request data containing the company_id and id.
     *
     * @return mixed Returns the worker event detail with related attachments
     */
    public function show($request): mixed
    {
        $request = $this->enrichRequestWithUserDetails($request);
        return $this->workerEvent->with(['eventAttachments'])->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_event.id', 'worker_event.worker_id', 'worker_event.event_date', 'worker_event.event_type', 'worker_event.flight_number', 'worker_event.departure_date', 'worker_event.remarks', 'worker_event.created_by', 'worker_event.modified_by', 'worker_event.last_working_date', 'worker_event.created_at', 'worker_event.updated_at', 'worker_event.deleted_at')
        ->find($request['id']);
    }
    /**
     * Delete the attachment of worker event.
     *
     * @param $request  The request data containing the company_id and id.
     *
     * @return bool Returns true if the deletion is successfully, otherwise false.
     */
    public function deleteAttachment($request): bool
    {
        $request = $this->enrichRequestWithUserDetails($request);
        $data = $this->workerEventAttachments::join('worker_event', 'worker_event.id', 'worker_event_attachments.file_id')
        ->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_event.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_event_attachments.id')->find($request['id']);
        if(is_null($data)) {
            return false;
        }
        $data->delete();
        return true;
    }
}

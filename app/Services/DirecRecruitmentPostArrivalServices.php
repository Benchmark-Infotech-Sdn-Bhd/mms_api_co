<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerVisa;
use App\Models\WorkerPostArrival;
use App\Models\WorkerPostArrivalAttachments;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirecRecruitmentPostArrivalServices
{
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerPostArrival
     */
    private WorkerPostArrival $workerPostArrival;
    /**
     * @var WorkerPostArrivalAttachments
     */
    private WorkerPostArrivalAttachments $workerPostArrivalAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * DirecRecruitmentPostArrivalServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerVisa $workerVisa
     * @param WorkerPostArrival $workerPostArrival
     * @param WorkerPostArrivalAttachments $workerPostArrivalAttachments
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, WorkerVisa $workerVisa, WorkerPostArrival $workerPostArrival, WorkerPostArrivalAttachments $workerPostArrivalAttachments, Storage $storage)
    {
        $this->directRecruitmentCallingVisaStatus   = $directRecruitmentCallingVisaStatus;
        $this->workerVisa                           = $workerVisa;
        $this->workerPostArrival                    = $workerPostArrival;
        $this->workerPostArrivalAttachments         = $workerPostArrivalAttachments;
        $this->storage                              = $storage;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function updatePostArrivalStatus($request): mixed
    {
        if(isset($request['workers']) && !empty($request['workers']) && !empty($request['calling_visa_reference_number'])) {
            foreach ($request['workers'] as $workerId) {
                $workerArrival = $this->workerPostArrival->updateOrCreate(
                    ['worker_id' => $workerId],
                    [
                        'arrival_id' => $request['arrival_id'] ?? '',
                        'status' => $request['status'] ?? '', 
                        'arrived_date' => $request['arrived_date'] ?? '',
                        'entry_visa_valid_until' => $request['entry_visa_valid_until'] ?? '',
                        'jtk_submitted_on' => $request['jtk_submitted_on'] ?? '',
                        'new_arrival_date' => $request['new_arrival_date'] ?? '',
                        'flight_number' => $request['flight_number'] ?? '',
                        'arrival_time' => $request['arrival_time'] ?? '',
                        'remarks' => $request['remarks'] ?? '',
                        'modified_by' => $request['modified_by']
                    ]);
                if($workerArrival->wasRecentlyCreated) {
                    $this->workerPostArrival->where('worker_id', $workerId)->update(['created_by' => $request['modified_by']]);
                }
            }
            // $this->workerPostArrival->whereIn($request['workers'])
            //     ->update(['status' => $request['status'], 'arrived_date' => $request['arrival_date'], 'entry_visa_valid_until' => $request['entry_visa_valid_until']]);
            // $this->workerVisa->whereIn('worker_id', $request['workers'])
            //     ->update(['entry_visa_valid_until' => $request['entry_visa_valid_until'], 'modified_by' => $request['modified_by']]);
        }
    }
}
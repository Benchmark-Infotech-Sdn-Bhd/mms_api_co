<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerVisa;
use App\Models\WorkerArrival;
use App\Models\DirectRecruitmentArrival;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirecRecruitmentPostArrivalServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;
    /**
     * @var DirectRecruitmentArrival
     */
    private DirectRecruitmentArrival $directRecruitmentArrival;
    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * DirecRecruitmentPostArrivalServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerVisa $workerVisa
     * @param WorkerArrival $workerArrival
     * @param DirectRecruitmentArrival $directRecruitmentArrival
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus, WorkerVisa $workerVisa, WorkerArrival $workerArrival, DirectRecruitmentArrival $directRecruitmentArrival, Storage $storage)
    {
        $this->directRecruitmentPostArrivalStatus   = $directRecruitmentPostArrivalStatus;
        $this->workerVisa                           = $workerVisa;
        $this->workerArrival                        = $workerArrival;
        $this->directRecruitmentArrival             = $directRecruitmentArrival;
        $this->storage                              = $storage;
    }
    /**
     * @param $applicationId, $onboardingCountryId, $modifiedBy
     * @return void
     */
    public function updatePostArrivalStatus($applicationId, $onboardingCountryId, $modifiedBy): void
    {
        $this->directRecruitmentPostArrivalStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' -> $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function updatePostArrival($request): mixed
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerArrival->whereIn($request['workers'])
                ->update(['arrival_status' => 'Arrived', 'arrived_date' => $request['arrival_date'], 'entry_visa_valid_until' => $request['entry_visa_valid_until'], 'modified_by' => $request['modified_by']]);
            $this->workerVisa->whereIn('worker_id', $request['workers'])
                    ->update(['entry_visa_valid_until' => $request['entry_visa_valid_until'], 'modified_by' => $request['modified_by']]);
        }
        $this->updatePostArrival($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function UpdateJTKSubmission($request): mixed
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerArrival->whereIn($request['workers'])
                ->update(['jtk_submitted_on' => $request['jtk_submitted_on'], 'modified_by' => $request['modified_by']]);
        }
        $this->updatePostArrival($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function updateCancellation($request): mixed
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerArrival->whereIn($request['workers'])
                ->update(['arrival_status' => 'Cancelled', 'modified_by' => $request['modified_by']]);
        }        
        if(request()->hasFile('attachment')) {
            foreach ($request['workers'] as $workerId) {
                foreach($request->file('attachment') as $file) {
                    $fileName = $file->getClientOriginalName();
                    $filePath = 'directRecruitment/workers/cancellation/' . $workerId. '/'. $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);    
                    $this->cancellationAttachment->create([
                        'file_id' => $workerId,
                        'file_name' => $fileName,
                        'file_type' => 'Post Arrival Cancellation Letter',
                        'file_url' => $fileUrl,
                        'created_by' => $request['modified_by'],
                        'modified_by' => $request['modified_by']
                    ]);
                }
            }
        }
        $this->updatePostArrival($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function updatePostponed($request): mixed
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerArrival->whereIn($request['workers'])
                ->update(['arrival_status' => 'Postponed', 'remarks' => $request['remarks'] ?? '', 'modified_by' => $request['modified_by']]);
            $arrivalDetails = $this->DirectRecruitmentArrival->create([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
                'item_name' => 'Arrival',
                'flight_date' => $request['new_arrival_date'],
                'arrival_time' => $request['arrival_time'],
                'flight_number' => $request['flight_number'],
                'status' => 'Not Arrived',
            ]);
            foreach ($request['workers'] as $workerId) {
                $this->workerArrival->create([
                    'arrival_id' => $arrivalDetails->id,
                    'worker_id' => $workerId,
                    'arrival_status' => 'Not Arrived',
                    'created_by' => $request['modified_by'],
                    'modified_by' => $request['modified_by']
                ]);
            }
        }
        $this->updatePostArrival($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
    }
}
<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\DirectRecruitmentCallingVisa;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectRecruitmentCallingVisaServices
{
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var DirectRecruitmentCallingVisa
     */
    private DirectRecruitmentCallingVisa $directRecruitmentCallingVisa;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;

    /**
     * DirectRecruitmentCallingVisaServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param DirectRecruitmentCallingVisa $directRecruitmentCallingVisa
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, DirectRecruitmentCallingVisa $directRecruitmentCallingVisa, Workers $workers, WorkerVisa $workerVisa)
    {
        $this->directRecruitmentCallingVisaStatus   = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentCallingVisa         = $directRecruitmentCallingVisa;
        $this->workers                              = $workers;
        $this->workerVisa                           = $workerVisa;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function callingVisaStatusList($request): mixed
    {
        return $this->directRecruitmentCallingVisaStatus
            ->select('id', 'item', 'updated_on')
            ->where([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
                'agent_id' => $request['agent_id']
            ])
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function submitCallingVisa($request): bool|array
    {
        $validator = Validator::make($request, $this->directRecruitmentCallingVisa->rules);
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            if(count($request['workers']) > Config::get('services.CALLING_VISA_WORKER_COUNT')) {
                return [
                    'workerCountError' => true
                ];
            }
        }
        $callingVisaStatus = $this->directRecruitmentCallingVisaStatus->create([
            'application_id' => $request['application_id'] ?? 0,
            'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
            'agent_id' => $request['agent_id'] ?? 0,
            'item' => 'Calling Visa Status',
            'updated_on' => Carbon::now(),
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);

        if(isset($request['workers']) && !empty($request['workers'])) {
            foreach ($request['workers'] as $workerId) {
                $this->directRecruitmentCallingVisa->create([
                    'application_id' => $request['application_id'] ?? 0,
                    'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                    'agent_id' => $request['agent_id'] ?? 0,
                    'worker_id' => $workerId,
                    'calling_visa_status_id' => $callingVisaStatus->id ?? 0,
                    'calling_visa_reference_number' => $request['calling_visa_reference_number'] ?? 0,
                    'submitted_on' => $request['submitted_on'] ?? 0,
                    'status' => 'Processed',
                    'created_by' => $request['created_by'] ?? 0,
                    'modified_by' => $request['created_by'] ?? 0
                ]);
                $this->workerVisa->where('worker_id', $workerId)->update(['calling_visa_reference_number' => $request['calling_visa_reference_number']]);
            } 
        }
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function updateCallingVisa($request): bool|array
    {
        $validator = Validator::make($request, $this->directRecruitmentCallingVisa->rulesForUpdation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            if(count($request['workers']) > Config::get('services.CALLING_VISA_WORKER_COUNT')) {
                return [
                    'workerCountError' => true
                ];
            }
        }
        $callingVisaStatus = $this->directRecruitmentCallingVisaStatus->findOrFail($request['calling_visa_status_id']);
        $callingVisaStatus->updated_on = Carbon::now();
        $callingVisaStatus->modified_by = $request['modified_by'];
        $callingVisaStatus->save();

        if(isset($request['workers']) && !empty($request['workers'])) {
            $callingVisaStatus->callingVisa()->delete();
            foreach ($request['workers'] as $workerId) {
                $this->directRecruitmentCallingVisa->create([
                    'application_id' => $request['application_id'] ?? 0,
                    'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                    'agent_id' => $request['agent_id'] ?? 0,
                    'worker_id' => $workerId,
                    'calling_visa_status_id' => $callingVisaStatus->id ?? 0,
                    'calling_visa_reference_number' => $request['calling_visa_reference_number'] ?? 0,
                    'submitted_on' => $request['submitted_on'] ?? 0,
                    'status' => 'Processed',
                    'created_by' => $request['modified_by'] ?? 0,
                    'modified_by' => $request['modified_by'] ?? 0
                ]);
                $this->workerVisa->where('worker_id', $workerId)->update(['calling_visa_reference_number' => $request['calling_visa_reference_number']]);
            }
        } else if(empty($request['workers']) && (!empty($request['calling_visa_reference_number']) || !empty($request['submitted_on']))) {
            $this->directRecruitmentCallingVisa->where('calling_visa_status_id', $request['calling_visa_status_id'])->update(['calling_visa_reference_number' => $request['calling_visa_reference_number'], 'submitted_on' => $request['submitted_on']]);
        }
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersList($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->directRecruitmentCallingVisa->rulesForSearch());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('direct_recruitment_calling_visa', 'direct_recruitment_calling_visa.worker_id', 'workers.id')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.agent_id' => $request['agent_id']
            ])
            ->where('direct_recruitment_calling_visa.deleted_at', NULL)
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['filter']) && !empty($request['filter'])) {
                    $query->where('direct_recruitment_calling_visa.status', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'direct_recruitment_calling_visa.calling_visa_status_id', 'direct_recruitment_calling_visa.calling_visa_reference_number', 'direct_recruitment_calling_visa.submitted_on', 'direct_recruitment_calling_visa.status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
}
<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
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
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, Workers $workers, WorkerVisa $workerVisa)
    {
        $this->directRecruitmentCallingVisaStatus   = $directRecruitmentCallingVisaStatus;
        $this->workers                              = $workers;
        $this->workerVisa                           = $workerVisa;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/',
            'submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }
    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function callingVisaStatusList($request): mixed
    {
        return $this->directRecruitmentCallingVisaStatus
            ->select('id', 'item', 'updated_on', 'status')
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
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers']) && !empty($request['calling_visa_reference_number'])) {
            $workerCount = $this->workerVisa->where('calling_visa_reference_number', $request['calling_visa_reference_number'])->count('worker_id');
            $workerCount +=count($request['workers']);
            if($workerCount > Config::get('services.CALLING_VISA_WORKER_COUNT')) {
                return [
                    'workerCountError' => true
                ];
            } else {
                $this->workerVisa->whereIn('worker_id', $request['workers'])->update(['calling_visa_reference_number' => $request['calling_visa_reference_number'], 'submitted_on' => $request['submitted_on'], 'status' => 'Processed', 'modified_by' => $request['modified_by']]);
            }
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id'],
            'agent_id' => $request['agent_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workersList($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.agent_id' => $request['agent_id'],
                'worker_visa.status' => 'Pending'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->where('workers.id', $request['worker_id'])
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status')
            ->get();
    }
}
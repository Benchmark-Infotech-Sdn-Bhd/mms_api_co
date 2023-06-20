<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DirectRecruitmentCallingVisaDispatchServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;

    /**
     * DirectRecruitmentCallingVisaDispatchServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     */
    public function __construct(Workers $workers, WorkerVisa $workerVisa, DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus)
    {
        $this->workers                                = $workers;
        $this->workerVisa                             = $workerVisa;
        $this->directRecruitmentCallingVisaStatus     = $directRecruitmentCallingVisaStatus;
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'agent_id' => 'required',
                'dispatch_method' => 'required'
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
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerVisa->whereIn('worker_id', $request['workers'])
            ->update(
                ['dispatch_method' => $request['dispatch_method'], 
                'dispatch_consignment_number' => $request['dispatch_consignment_number'] ?? '',
                'dispatch_acknowledgement_number' => $request['dispatch_acknowledgement_number'] ?? '', 
                'dispatch_submitted_on' => Carbon::now(),
                'dispatch_status' => 'Processed',
                'modified_by' => $request['modified_by']]);
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
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function listBasedOnCallingVisa($request): mixed
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
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->where('worker_visa.generated_status', 'Generated')
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.agent_id' => $request['agent_id'],
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_consignment_number', 'worker_visa.dispatch_acknowledgement_number', 'worker_visa.dispatch_submitted_on',  'worker_visa.dispatch_status', DB::raw('COUNT(workers.id) as workers', 'worker_immigration.immigration_status'))
            ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_consignment_number', 'worker_visa.dispatch_acknowledgement_number', 'worker_visa.dispatch_submitted_on',  'worker_visa.dispatch_status')
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
}
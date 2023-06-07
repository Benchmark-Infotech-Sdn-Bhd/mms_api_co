<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\DirectRecruitmentCallingVisa;
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
     * DirectRecruitmentCallingVisaServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param DirectRecruitmentCallingVisa $directRecruitmentCallingVisa
     */
    public function __construct(DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, DirectRecruitmentCallingVisa $directRecruitmentCallingVisa)
    {
        $this->directRecruitmentCallingVisaStatus   = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentCallingVisa         = $directRecruitmentCallingVisa;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function callingVisaStatusList($request): mixed
    {
        return $this->directRecruitmentCallingVisaStatus
            ->select('item', 'updated_on')
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
            }
        } else if(empty($request['workers']) && (!empty($request['calling_visa_reference_number']) || !empty($request['submitted_on']))) {
            $this->directRecruitmentCallingVisa->where('calling_visa_status_id', $request['calling_visa_status_id'])->update(['calling_visa_reference_number' => $request['calling_visa_reference_number'], 'submitted_on' => $request['submitted_on']]);
        }
        return true;
    }
    // /**
    //  * @param $request
    //  * @return mixed
    //  */
    // public function workersList($request): mixed
    // {
    //     return $this->directRecruitmentCallingVisaStatus
    //         ->select('item', 'updated_on')
    //         ->where('onboarding_country_id', $request['onboarding_country_id'])
    //         ->where('agent_id', $request['agent_id'])
    //         ->orderBy('id', 'desc')
    //         ->paginate(Config::get('services.paginate_row'));
    // }
}
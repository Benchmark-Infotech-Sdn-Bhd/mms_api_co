<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaApproval;
use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectRecruitmentCallingVisaApprovalServices
{
    /**
     * @var DirectRecruitmentCallingVisaApproval
     */
    private DirectRecruitmentCallingVisaApproval $directRecruitmentCallingVisaApproval;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;

    /**
     * DirectRecruitmentCallingVisaApprovalServices constructor.
     * @param DirectRecruitmentCallingVisaApproval $directRecruitmentCallingVisaApproval
     * @param Workers $workers
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     */
    public function __construct(DirectRecruitmentCallingVisaApproval $directRecruitmentCallingVisaApproval, Workers $workers, DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus)
    {
        $this->directRecruitmentCallingVisaApproval   = $directRecruitmentCallingVisaApproval;
        $this->workers                                = $workers;
        $this->directRecruitmentCallingVisaStatus                             = $directRecruitmentCallingVisaStatus;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function callingVisaStatusUpdate($request): bool|array
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            foreach ($request['workers'] as $workerId) {
                $this->directRecruitmentCallingVisaApproval->create([
                    'worker_id' => $workerId,
                    'status' => $request['status'] ?? 'Pending',
                    'calling_visa_generated' => $request['calling_visa_generated'] ?? '',
                    'calling_visa_valid_until' => $request['calling_visa_valid_until'] ?? '',
                    'remarks' => $request['remarks'] ?? '',
                    'created_by' => $request['created_by'] ?? 0,
                    'modified_by' => $request['created_by'] ?? 0
                ]);
            }
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id'],
            'agent_id' => $request['agent_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['created_by']]);
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
            ->leftJoin('insurance_purchase', 'insurance_purchase.worker_id', 'workers.id')
            ->leftJoin('direct_recruitment_calling_visa_approval', 'direct_recruitment_calling_visa_approval.worker_id', 'workers.id')
            ->where([
                'workers.application_id' => $request['application_id'],
                'workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.agent_id' => $request['agent_id']
            ])
            ->where('insurance_purchase.status', 'Purchased')
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['filter']) && !empty($request['filter'])) {
                    $query->where('direct_recruitment_calling_visa_approval.status', $request['filter']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'workers.application_id', 'workers.onboarding_country_id', 'workers.agent_id', 'worker_visa.calling_visa_reference_number', 'direct_recruitment_calling_visa_approval.status', 'direct_recruitment_calling_visa_approval.calling_visa_generated', 'direct_recruitment_calling_visa_approval.calling_visa_valid_until', 'direct_recruitment_calling_visa_approval.remarks')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
}
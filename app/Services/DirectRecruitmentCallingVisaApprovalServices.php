<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerInsuranceDetails;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectRecruitmentCallingVisaApprovalServices
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
     * @var workerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;

    /**
     * DirectRecruitmentCallingVisaApprovalServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     */
    public function __construct(Workers $workers, WorkerVisa $workerVisa, WorkerInsuranceDetails $workerInsuranceDetails, DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus)
    {
        $this->workers                                = $workers;
        $this->workerVisa                             = $workerVisa;
        $this->workerInsuranceDetails                 = $workerInsuranceDetails;
        $this->directRecruitmentCallingVisaStatus     = $directRecruitmentCallingVisaStatus;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_generated' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'calling_visa_valid_until' => 'required|date|date_format:Y-m-d|after:today',
            'status' => 'required'
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
    public function approvalStatusUpdate($request): bool|array
    {
        $validator = Validator::make($request, $this->createValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $workerVisaProcessed = $this->workerInsuranceDetails
                            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
                            ->whereIn('worker_insurance_details.worker_id', $request['workers'])
                            ->where('worker_insurance_details.insurance_status', 'Purchased')
                            ->select('worker_visa.calling_visa_reference_number')
                            ->groupBy('worker_visa.calling_visa_reference_number')
                            ->get()->toArray();
            if(count($workerVisaProcessed) != 1) {
                return [
                    'visaReferenceNumberCountError' => true
                ];
            }
            $this->workerVisa->whereIn('worker_id', $request['workers'])->update(['calling_visa_generated' => $request['calling_visa_generated'], 'calling_visa_valid_until' => $request['calling_visa_valid_until'], 'remarks' => $request['remarks'], 'approval_status' => $request['status'], 'modified_by' => $request['modified_by']]);
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
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
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->where('worker_visa.approval_status', '!=', 'Approved')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0,
                'worker_insurance_details.insurance_status' => 'Purchased'
            ])
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
                    $query->where('worker_visa.approval_status', $request['filter']);
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['agent_id']) && !empty($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.approval_status', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_visa.remarks')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->workers->with(['workerBioMedical' => function ($query) { 
                $query->select(['id', 'worker_id', 'bio_medical_valid_until']);
            }])->with(['workerVisa' => function ($query) {
                $query->select(['id', 'worker_id', 'ksm_reference_number', 'calling_visa_reference_number', 'approval_status', 'calling_visa_generated', 'calling_visa_valid_until', 'remarks']);
            }])->where('workers.id', $request['worker_id'])
            ->select('id', 'name', 'passport_number', 'application_id', 'onboarding_country_id', 'agent_id')
            ->get();
    }
}
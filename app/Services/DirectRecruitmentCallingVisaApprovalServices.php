<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerInsuranceDetails;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

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
        $this->workers                                          = $workers;
        $this->workerVisa                                       = $workerVisa;
        $this->workerInsuranceDetails                           = $workerInsuranceDetails;
        $this->directRecruitmentCallingVisaStatus               = $directRecruitmentCallingVisaStatus;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_generated' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'calling_visa_valid_until' => 'required|date|date_format:Y-m-d|after:today'
        ];
    }
    /**
     * @return array
     */
    public function statusValidation(): array
    {
        return [
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
        $validator = Validator::make($request, $this->statusValidation());
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
        }
        if ($request['status'] == 'Approved') {
            $validator = Validator::make($request, $this->createValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }

            $this->workerVisa->whereIn('worker_id', $request['workers'])->update(['calling_visa_generated' => $request['calling_visa_generated'], 'calling_visa_valid_until' => $request['calling_visa_valid_until'], 'remarks' => $request['remarks'], 'approval_status' => $request['status'], 'modified_by' => $request['modified_by']]);

            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'Accepted', 
                    'modified_by' => $request['modified_by']
                ]);

            $workerDetails = [];
            $ksmCount = [];
            
            // update utilised quota based on ksm reference number
            foreach($request['workers'] as $worker) {
                $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
                $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
            }
            $ksmCount = array_count_values($workerDetails);
            foreach($ksmCount as $key => $value) {
                event(new KSMQuotaUpdated($request['onboarding_country_id'], $key, $value, 'increment'));
            }

            // update utilised quota in onboarding country
            event(new WorkerQuotaUpdated($request['onboarding_country_id'], count($request['workers']), 'increment'));
            
        } else {
            $this->workerVisa->whereIn('worker_id', $request['workers'])
            ->update([
                'approval_status' => $request['status'], 
                'modified_by' => $request['modified_by']
            ]);

            $this->workers->whereIn('id', $request['workers'])
            ->update([
                'directrecruitment_status' => $request['status'], 
                'modified_by' => $request['modified_by']
            ]);
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
        $data = $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
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
            });
            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'worker_visa.calling_visa_reference_number', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_visa.approval_status')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->get();
            }else{
                $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.approval_status', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_visa.remarks', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->paginate(Config::get('services.paginate_worker_row'));
            }
            return $data;
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
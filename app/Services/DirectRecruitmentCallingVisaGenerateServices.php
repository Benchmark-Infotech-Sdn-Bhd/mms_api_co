<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerImmigration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DirectRecruitmentCallingVisaGenerateServices
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
     * @var WorkerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var WorkerImmigration
     */
    private WorkerImmigration $workerImmigration;

    /**
     * DirectRecruitmentCallingVisaGenerateServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerImmigration $workerImmigration
     */
    public function __construct(Workers $workers, WorkerVisa $workerVisa, DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, WorkerInsuranceDetails $workerInsuranceDetails, WorkerImmigration $workerImmigration)
    {
        $this->workers                                = $workers;
        $this->workerVisa                             = $workerVisa;
        $this->directRecruitmentCallingVisaStatus     = $directRecruitmentCallingVisaStatus;
        $this->workerInsuranceDetails                 = $workerInsuranceDetails;
        $this->workerImmigration                      = $workerImmigration;
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
    public function generatedStatusUpdate($request): bool|array
    {
        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->workerVisa->whereIn('worker_id', $request['workers'])->update(['generated_status' => 'Generated', 'modified_by' => $request['modified_by']]);
        }
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id'],
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
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
           ->get();
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
        $data = $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_visa.generated_status', '!=', 'Generated')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0,
                'worker_immigration.immigration_status' => 'Paid'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->where(function ($query) use ($request) {
                if(isset($request['agent_id']) && !empty($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }
            });
            
            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number',  'worker_visa.calling_visa_valid_until', 'worker_visa.calling_visa_generated', DB::raw('COUNT(workers.id) as workers'), 'worker_immigration.immigration_reference_number', 'worker_visa.generated_status')
                ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_valid_until', 'worker_visa.calling_visa_generated', 'worker_immigration.immigration_reference_number', 'worker_visa.generated_status')
                ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
                ->get();
            }else{
                $data = $data->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_valid_until', 'worker_visa.calling_visa_generated', 'worker_visa.generated_status', DB::raw('COUNT(workers.id) as workers'), 'worker_immigration.immigration_reference_number', DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
                ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_valid_until', 'worker_visa.calling_visa_generated', 'worker_immigration.immigration_reference_number', 'worker_visa.generated_status')
                ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
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
        $processCallingVisa = $this->workerVisa
                            ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['calling_visa_reference_number', 'submitted_on']);

        $insurancePurchase = $this->workerInsuranceDetails
                        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
                        ->leftJoin('vendors', 'vendors.id', 'worker_insurance_details.insurance_provider_id')
                        ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date']);
                        
        $callingVisaApproval = $this->workerVisa
                        ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['calling_visa_generated', 'calling_visa_valid_until']);

        $callingVisaImmigration = $this->workerImmigration
                        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_immigration.worker_id')
                        ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date']);
                        
        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval,
            'immigration' => $callingVisaImmigration
        ];
    }
}
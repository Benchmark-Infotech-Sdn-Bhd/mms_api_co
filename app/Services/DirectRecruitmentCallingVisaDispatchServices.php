<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerImmigration;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\DirectRecruitmentOnboardingCountry;

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
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
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
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentCallingVisaDispatchServices constructor.
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerImmigration $workerImmigration
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(Workers $workers, WorkerVisa $workerVisa, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus, WorkerInsuranceDetails $workerInsuranceDetails, WorkerImmigration $workerImmigration, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->workers                                      = $workers;
        $this->workerVisa                                   = $workerVisa;
        $this->directRecruitmentOnboardingCountryServices   = $directRecruitmentOnboardingCountryServices;
        $this->directRecruitmentCallingVisaStatus           = $directRecruitmentCallingVisaStatus;
        $this->workerInsuranceDetails                       = $workerInsuranceDetails;
        $this->workerImmigration                            = $workerImmigration;
        $this->directRecruitmentOnboardingCountry           = $directRecruitmentOnboardingCountry;
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
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
            
            $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
                                ->where('company_id', $request['company_id'])
                                ->count();
                                
            if($workerCompanyCount != count($request['workers'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);

            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return [
                    'InvalidUser' => true
                ];
            }
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
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);

        $onBoardingStatus['application_id'] = $request['application_id'];
        $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
        $onBoardingStatus['onboarding_status'] = 5; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

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
            ->where('worker_visa.generated_status', 'Generated')
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
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
            ->where('worker_visa.generated_status', 'Generated')
            ->where('worker_immigration.immigration_status', 'Paid')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%'.$request['search'].'%');
                }
            });
            
            if(isset($request['export']) && !empty($request['export']) ){
                $data = $data->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', DB::raw('COUNT(workers.id) as workers'))
                ->selectRaw("(CASE WHEN (worker_visa.dispatch_method = 'Courier') THEN worker_visa.dispatch_consignment_number WHEN (worker_visa.dispatch_method = 'ByHand') THEN worker_visa.dispatch_acknowledgement_number  ELSE '' END) as dispatch_reference_number")
                ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', 'worker_visa.dispatch_consignment_number', 'worker_visa.dispatch_acknowledgement_number')
                ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
                ->get();
            }else{
                if(\DB::getDriverName() !== 'sqlite'){
                    $data = $data->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', DB::raw('COUNT(workers.id) as workers', 'worker_immigration.immigration_status'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
                    ->selectRaw("(CASE WHEN (worker_visa.dispatch_method = 'Courier') THEN worker_visa.dispatch_consignment_number WHEN (worker_visa.dispatch_method = 'ByHand') THEN worker_visa.dispatch_acknowledgement_number  ELSE '' END) as dispatch_reference_number")
                    ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', 'worker_visa.dispatch_consignment_number', 'worker_visa.dispatch_acknowledgement_number')
                    ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
                    ->paginate(Config::get('services.paginate_worker_row'));
                }else{
                    $data = $data->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', DB::raw('COUNT(workers.id) as workers', 'worker_immigration.immigration_status'), DB::raw('GROUP_CONCAT(workers.id) AS workers_id'))
                    ->selectRaw("(CASE WHEN (worker_visa.dispatch_method = 'Courier') THEN worker_visa.dispatch_consignment_number WHEN (worker_visa.dispatch_method = 'ByHand') THEN worker_visa.dispatch_acknowledgement_number  ELSE '' END) as dispatch_reference_number")
                    ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'worker_visa.dispatch_method', 'worker_visa.dispatch_status', 'worker_visa.dispatch_consignment_number', 'worker_visa.dispatch_acknowledgement_number')
                    ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
                    ->paginate(Config::get('services.paginate_worker_row'));
                }
            }
            return $data;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        $workerCheck = $this->workerVisa->leftJoin('workers', 'workers.id', 'worker_visa.worker_id')
                            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['workers.company_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on']);
        if(!in_array($workerCheck->company_id, $request['company_id'])) {
            return [
                'InvalidUser' => true
            ];
        }
        $processCallingVisa = $this->workerVisa
                            ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['calling_visa_reference_number', 'submitted_on']);

        $insurancePurchase = $this->workerInsuranceDetails
                        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
                        ->leftJoin('vendors', 'vendors.id', 'worker_insurance_details.insurance_provider_id')
                        ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'vendors.name as insurance_provider_name']);
                        
        $callingVisaApproval = $this->workerVisa
                        ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                        ->first(['calling_visa_generated', 'calling_visa_valid_until']);

        $callingVisaImmigration = $this->workerImmigration
                            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_immigration.worker_id')
                            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date']);

        $callingVisaDispatch = $this->workerVisa
                            ->where('calling_visa_reference_number', $request['calling_visa_reference_number'])
                            ->first(['calling_visa_valid_until']);
                        
        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval,
            'immigration' => $callingVisaImmigration,
            'dispatch' => $callingVisaDispatch
        ];
    }
    /**
     * checks the onbording country exists for the particular company
     * 
     * @param int $companyId
     * @param int $onboardingCoyntryId
     * @return mixed The onboarding country details
     */
    private function checkForApplication(int $companyId, int $onboardingCoyntryId): mixed
    {
        return $this->directRecruitmentOnboardingCountry
                    ->join('directrecruitment_applications', function ($join) use($companyId) {
                        $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                            ->where('directrecruitment_applications.company_id', $companyId);
                    })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
                    ->find($onboardingCoyntryId);
    }
}
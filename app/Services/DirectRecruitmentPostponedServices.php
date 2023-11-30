<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentWorkers;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\WorkerVisa;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\CallingVisaExpiryCronDetails;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DirectRecruitmentPostponedServices
{
    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;
    /**
     * @var DirectrecruitmentWorkers
     */
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var OnboardingCountriesKSMReferenceNumber
     */
    private OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber;
    /**
     * @var CallingVisaExpiryCronDetails
     */
    private CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails;

    /**
     * DirectRecruitmentPostponedServices constructor.
     * @param Workers $workers
     * @param WorkerArrival $workerArrival
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     * @param WorkerVisa $workerVisa
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber
     * @param CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails
     */
    public function __construct(Workers $workers, WorkerArrival $workerArrival, DirectrecruitmentWorkers $directrecruitmentWorkers, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, WorkerVisa $workerVisa, OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber, CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails)
    {
        $this->workers = $workers;
        $this->workerArrival = $workerArrival;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->workerVisa = $workerVisa;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
        $this->callingVisaExpiryCronDetails = $callingVisaExpiryCronDetails;
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
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->leftjoin('worker_arrival', 'worker_arrival.worker_id', '=', 'workers.id')
            ->leftjoin('directrecruitment_arrival', 'directrecruitment_arrival.id', '=', 'worker_arrival.arrival_id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Postponed'
            ])
            ->where(function ($query) use ($request) {
                if(isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_arrival.flight_number', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time')
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }
    /**
     * @param $request
     * @return bool
     */
    public function updateCallingVisaExpiry(): bool
    {
        $workerDetails = [];
        $workerKSM = [];
        $postponedWorkerIds = $this->workerArrival
                                ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_arrival.worker_id')
                                ->where('worker_arrival.arrival_status', 'Postponed')
                                ->where('worker_visa.calling_visa_valid_until', '<', Carbon::now()->format('Y-m-d'))
                                ->where('worker_visa.status', '<>', 'Expired')
                                ->select('worker_arrival.worker_id')
                                ->distinct('worker_arrival.worker_id')
                                ->get()->toArray();
        $postponedWorkerIds = array_column($postponedWorkerIds, 'worker_id');
        if(!empty($postponedWorkerIds)) {
            $workerIds = $this->workerArrival
                    ->whereIn('worker_id', $postponedWorkerIds)
                    ->where('arrival_status', 'Not Arrived')
                    ->select('worker_id')
                    ->distinct('worker_id')
                    ->get()->toArray();
            $workerIds = array_column($workerIds, 'worker_id');

            foreach($workerIds as $worker) {
                $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
                $onboardingDetails = $this->directrecruitmentWorkers->where('worker_id', $worker)
                                        ->first(['application_id', 'onboarding_country_id']);
                
                $workerDetails[$worker]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
                $workerKSM[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
                $workerKSM[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number]['application_id'] = $onboardingDetails->application_id;
            }
            
            //update cron table for initial utilised quota
            foreach($workerKSM as $key => $ksmValues) {
                foreach($ksmValues as $ksmKey => $ksmValue) {
                    $quotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmValue['application_id'])
                                        ->where('onboarding_country_id', $ksmValue['onboarding_country_id'])
                                        ->where('ksm_reference_number', $ksmKey)
                                        ->first(['quota', 'utilised_quota']);
                    // temporary fix
                    if(!is_null($quotaDetails)) {
                        $this->callingVisaExpiryCronDetails->create([
                            'application_id' => $ksmValue['application_id'],
                            'onboarding_country_id' => $ksmValue['onboarding_country_id'],
                            'ksm_reference_number' => $ksmKey,
                            'approved_quota' => $quotaDetails->quota,
                            'initial_utilised_quota' => $quotaDetails->utilised_quota,
                            'current_utilised_quota' => 0,
                        ]);
                    }
                } 
            }
            
            if(isset($workerIds) && !empty($workerIds)) {
                $this->workers->whereIn('id', $workerIds)
                    ->update([
                        'directrecruitment_status' => 'Expired'
                    ]);
                    $this->workerVisa->whereIn('worker_id', $workerIds)
                    ->update([
                        'status' => 'Expired'
                    ]);
                foreach ($workerIds as $workerId) {
                    // updating quota in onboarding country 
                    $utilisedQuota = 0;
                    $countryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($workerDetails[$workerId]['onboarding_country_id']);
                    $utilisedQuota = (($countryDetails->utilised_quota - 1) < 0) ? 0 : $countryDetails->utilised_quota - 1;
                    $countryDetails->utilised_quota = $utilisedQuota;
                    $countryDetails->save();

                    // updating quota based on ksm reference number
                    $WorkerKSMDetails = $this->workerVisa->where('worker_id', $workerId)->first(['ksm_reference_number']);
                    $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->where('onboarding_country_id', $countryDetails->id)
                            ->where('ksm_reference_number', $WorkerKSMDetails->ksm_reference_number)
                            ->first(['id', 'utilised_quota']);
                    // temporary fix
                    if(!is_null($ksmDetails)) {
                        $ksmUtilisedQuota = (($ksmDetails->utilised_quota - 1) < 0) ? 0 : $ksmDetails->utilised_quota - 1;
                        $this->onboardingCountriesKSMReferenceNumber->where('id', $ksmDetails->id)->update(['utilised_quota' => $ksmUtilisedQuota]);
                    }
                }
            }
            //update cron table for current utilised quota
            foreach($workerKSM as $key => $ksmValues) {
                foreach($ksmValues as $ksmKey => $ksmValue) {
                    $currentQuotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmValue['application_id'])
                                        ->where('onboarding_country_id', $ksmValue['onboarding_country_id'])
                                        ->where('ksm_reference_number', $ksmKey)
                                        ->first(['quota', 'utilised_quota']);
                    // temporary fix
                    if(!is_null($currentQuotaDetails)) {
                        $this->callingVisaExpiryCronDetails->where([
                            ['onboarding_country_id', $ksmValue['onboarding_country_id']],
                            ['application_id', $ksmValue['application_id']],
                            ['ksm_reference_number', $ksmKey]
                        ])->update(['current_utilised_quota' => $currentQuotaDetails->utilised_quota]);
                    }
                }
            }
        }        
        return true;
    }
}
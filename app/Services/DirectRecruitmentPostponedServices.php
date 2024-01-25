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
    
    public const USER_TYPE = 'Customer';
    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';

    public const ARRIVAL_STATUS_POSTPONED = 'Postponed';
    public const ARRIVAL_STATUS_NOT_ARRIVED = 'Not Arrived';
    public const VISA_STATUS = 'Expired';
    public const STATUS_EXPIRED = 'Expired';

    public const DEFAULT_INT_VALUE = 0;

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
     * DirectRecruitmentPostponedServices constructor method.
     * @param Workers $workers The workers instance
     * @param WorkerArrival $workerArrival The workers arrival instance
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers The Direct recruitment instance
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The Direct Recruitment OnBoarding Country instance
     * @param WorkerVisa $workerVisa The Worker Visa Instance
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber The onboarding Countries KSM Reference Number instance
     * @param CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails The calling visa expiry cron details instance
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
     * Creates the validation rules for search .
     *
     * @return array The array containing the validation rules.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    
    /**
     * Returns a paginated list of direct recruitment workers.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id, user_type
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment worker list.
     * 
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
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == self::USER_TYPE) {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request[self::REQUEST_COMPANY_ID],
                'directrecruitment_workers.onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID],
                'worker_arrival.arrival_status' => self::ARRIVAL_STATUS_POSTPONED
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
     * Get the Postponed Worker Ids
     * 
     * @param
     *
     * @return array Postponed Worker Ids
     */
    public function getPostponedWorkerIds(): array
    {
        $postponedWorkerIds = $this->workerArrival
                                ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_arrival.worker_id')
                                ->where('worker_arrival.arrival_status', self::ARRIVAL_STATUS_POSTPONED)
                                ->where('worker_visa.calling_visa_valid_until', '<', Carbon::now()->format('Y-m-d'))
                                ->where('worker_visa.status', '<>', self::VISA_STATUS)
                                ->select('worker_arrival.worker_id')
                                ->distinct('worker_arrival.worker_id')
                                ->get()->toArray();
        return array_column($postponedWorkerIds, 'worker_id');
    }

    /**
     * Get the Worker Ids
     * 
     * @param $postponedWorkerIds - Array of postponed worker id's
     *
     * @return array Worker Ids
     */
    public function getWorkerIds($postponedWorkerIds): array
    {
        $workerIds = $this->workerArrival
                    ->whereIn('worker_id', $postponedWorkerIds)
                    ->where('arrival_status', self::ARRIVAL_STATUS_NOT_ARRIVED)
                    ->select('worker_id')
                    ->distinct('worker_id')
                    ->get()->toArray();
        return array_column($workerIds, 'worker_id');
    }

    /**
     * Update the calling visa expiry information.
     * 
     * This function call from the cron to update the calling visa expiry information.
     * 
     * @return bool Returns bool true if calling visa expiry updated successfully
     */
    public function updateCallingVisaExpiry(): bool
    {
        $workerDetails = [];
        $workerKSM = [];

        $postponedWorkerIds = $this->getPostponedWorkerIds();

        if(!empty($postponedWorkerIds)) {
           
            $workerIds = $this->getWorkerIds($postponedWorkerIds);

            foreach($workerIds as $worker) {
                $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first([self::REQUEST_KSM_REFERENCE_NUMBER]);
                $onboardingDetails = $this->directrecruitmentWorkers->where('worker_id', $worker)
                                        ->first(['application_id', 'onboarding_country_id']);
                
                $workerDetails[$worker]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
                $workerKSM[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
                $workerKSM[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number][self::REQUEST_COMPANY_ID] = $onboardingDetails->application_id;
            }
            
            //update cron table for initial utilised quota
            foreach($workerKSM as $key => $ksmValues) {
                foreach($ksmValues as $ksmKey => $ksmValue) {
                    $quotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmValue[self::REQUEST_COMPANY_ID])
                                        ->where('onboarding_country_id', $ksmValue[self::REQUEST_ONBOARDING_COUNTRY_ID])
                                        ->where('ksm_reference_number', $ksmKey)
                                        ->first(['quota', 'utilised_quota']);
                    $this->callingVisaExpiryCronDetails->create([
                        'application_id' => $ksmValue[self::REQUEST_COMPANY_ID],
                        'onboarding_country_id' => $ksmValue[self::REQUEST_ONBOARDING_COUNTRY_ID],
                        'ksm_reference_number' => $ksmKey,
                        'approved_quota' => $quotaDetails->quota,
                        'initial_utilised_quota' => $quotaDetails->utilised_quota,
                        'current_utilised_quota' => self::DEFAULT_INT_VALUE,
                    ]);
                } 
            }
            
            if(!empty($workerIds)) {
                $this->workers->whereIn('id', $workerIds)
                    ->update([
                        'directrecruitment_status' => self::STATUS_EXPIRED
                    ]);
                    $this->workerVisa->whereIn('worker_id', $workerIds)
                    ->update([
                        'status' => self::STATUS_EXPIRED
                    ]);
                foreach ($workerIds as $workerId) {
                    // updating quota in onboarding country 
                    $utilisedQuota = self::DEFAULT_INT_VALUE;
                    $countryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($workerDetails[$workerId][self::REQUEST_ONBOARDING_COUNTRY_ID]);
                    $utilisedQuota = (($countryDetails->utilised_quota - 1) < self::DEFAULT_INT_VALUE) ? self::DEFAULT_INT_VALUE : $countryDetails->utilised_quota - 1;
                    $countryDetails->utilised_quota = $utilisedQuota;
                    $countryDetails->save();

                    // updating quota based on ksm reference number
                    $WorkerKSMDetails = $this->workerVisa->where('worker_id', $workerId)->first([self::REQUEST_KSM_REFERENCE_NUMBER]);
                    $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->where('onboarding_country_id', $countryDetails->id)
                            ->where('ksm_reference_number', $WorkerKSMDetails->ksm_reference_number)
                            ->first(['id', 'utilised_quota']);
                    $ksmUtilisedQuota = (($ksmDetails->utilised_quota - 1) < self::DEFAULT_INT_VALUE) ? self::DEFAULT_INT_VALUE : $ksmDetails->utilised_quota - 1;
                    $this->onboardingCountriesKSMReferenceNumber->where('id', $ksmDetails->id)->update(['utilised_quota' => $ksmUtilisedQuota]);
                }
            }
            //update cron table for current utilised quota
            foreach($workerKSM as $key => $ksmValues) {
                foreach($ksmValues as $ksmKey => $ksmValue) {
                    $currentQuotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmValue[self::REQUEST_COMPANY_ID])
                                        ->where('onboarding_country_id', $ksmValue[self::REQUEST_ONBOARDING_COUNTRY_ID])
                                        ->where('ksm_reference_number', $ksmKey)
                                        ->first(['quota', 'utilised_quota']);
                    $this->callingVisaExpiryCronDetails->where([
                        ['onboarding_country_id', $ksmValue[self::REQUEST_ONBOARDING_COUNTRY_ID]],
                        ['application_id', $ksmValue[self::REQUEST_COMPANY_ID]],
                        ['ksm_reference_number', $ksmKey]
                    ])->update(['current_utilised_quota' => $currentQuotaDetails->utilised_quota]);
                }
            }
        }        
        return true;
    }
}
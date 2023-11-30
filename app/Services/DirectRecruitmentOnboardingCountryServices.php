<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\DirectRecruitmentApplicationApproval;
use App\Models\ApplicationInterviews;
use App\Models\OnboardingAttestation;
use App\Models\Workers;
use App\Models\Levy;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\DB;

class DirectRecruitmentOnboardingCountryServices
{
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var DirectRecruitmentApplicationApproval
     */
    private DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;
    /**
     * @var ApplicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;
     /**
     * @var OnboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var Levy
     */
    private Levy $levy;
    /**
     * @var OnboardingCountriesKSMReferenceNumber
     */
    private OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber;

    /**
     * DirectRecruitmentOnboardingCountryServices constructor.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;
     * @param ApplicationInterviews $applicationInterviews
     * @param ValidationServices $validationServices;
     * @param OnboardingAttestation $onboardingAttestation;
     * @param Workers $workers
     * @param Levy $levy
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber
     */
    public function __construct(DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval, ApplicationInterviews $applicationInterviews, ValidationServices $validationServices, OnboardingAttestation $onboardingAttestation, Workers $workers, Levy $levy, OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber)
    {
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->applicationInterviews = $applicationInterviews;
        $this->validationServices = $validationServices;
        $this->onboardingAttestation = $onboardingAttestation;
        $this->workers = $workers;
        $this->levy = $levy;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'country_id' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'country_id' => 'required'
            // 'quota' => 'required|regex:/^[0-9]+$/|max:3'
        ];
    }
    /**
     * @return array
     */
    public function ksmUpdateValidation(): array
    {
        return [
            'id' => 'required',
            'onboarding_country_id' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {

        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|regex:/^[a-zA-Z ]*$/|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        
        return $this->directRecruitmentOnboardingCountry->with(['onboardingKSMReferenceNumbers' => function ($query) {
            $query->select('id', 'onboarding_country_id', 'ksm_reference_number', 'quota', 'utilised_quota');
        }])->leftJoin('countries', 'countries.id', 'directrecruitment_onboarding_countries.country_id')
        ->leftJoin('directrecruitment_onboarding_status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.id')
            ->where('directrecruitment_onboarding_countries.application_id', $request['application_id'])
            ->where(function ($query) use ($request) {
                if (isset($request['search_param']) && !empty($request['search_param'])) {
                    $query->where('countries.country_name', 'like', "%{$request['search_param']}%");
                }
            })
            ->select('directrecruitment_onboarding_countries.id', 'countries.country_name as country', 'countries.system_type as system', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.name as onboarding_status_name')
            ->orderBy('directrecruitment_onboarding_countries.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingCountry->with(['onboardingKSMReferenceNumbers' => function ($query) {
            $query->select('id', 'onboarding_country_id', 'ksm_reference_number', 'quota', 'utilised_quota');
        }])->leftJoin('directrecruitment_onboarding_status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.id')->select('directrecruitment_onboarding_countries.*', 'directrecruitment_onboarding_status.name as onboarding_status_name')->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function create($request)
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        // $interviewApproved = $this->applicationInterviews->where('application_id', $request['application_id'])
        //                 ->where('status', 'Approved')->sum('approved_quota');
        // $countriesQuota = $this->directRecruitmentOnboardingCountry->where('application_id', $request['application_id'])
        //                     ->sum('quota');
        // $countriesQuota += $request['quota'];
        // if($countriesQuota > $interviewApproved) {
        //     return [
        //         'quotaError' => true
        //     ];
        // }
        // $countriesQuota = $this->directRecruitmentOnboardingCountry->where('application_id', $request['application_id'])
        //                     ->sum('quota');
        $checkCountry = $this->directRecruitmentOnboardingCountry->where('application_id', $request['application_id'])     
                            ->where('country_id', $request['country_id'])
                            ->first();
        if(!empty($checkCountry)) {
            $checkKSM = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $request['application_id'])
                            ->where('onboarding_country_id', $checkCountry->id)
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->get();
            if(!empty($checkKSM)) {
                return [
                    'ksmNumberError' => true
                ];
            }
        }
        $levyApproved = $this->levy->where('application_id', $request['application_id'])
                        ->where('new_ksm_reference_number', $request['ksm_reference_number'])
                        ->where('status', 'Paid')->sum('approved_quota');
                        
        $ksmQuota = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $request['application_id'])
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->sum('quota');
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }
        // if(!empty($checkCountry)) {
        //     $ksmQuota = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $request['application_id'])
        //                     ->where('onboarding_country_id', $checkCountry->id)
        //                     ->where('ksm_reference_number', $request['ksm_reference_number'])
        //                     ->sum('quota');
        //     if($interviewApproved < ($checkCountry->quota + $request['quota'])) {
        //         return [
        //             'quotaError' => true
        //         ];
        //     }
        // }              
        if(empty($checkCountry)) {
            $onboardingCountry = $this->directRecruitmentOnboardingCountry->create([
                'application_id' => $request['application_id'] ?? 0,
                'country_id' => $request['country_id'] ?? 0,
                'quota' => $request['quota'] ?? 0,
                'utilised_quota' => $request['utilised_quota'] ?? 0,
                'status' => $request['status'] ?? 1,
                'created_by' => $request['created_by'] ?? 0,
                'modified_by' => $request['created_by'] ?? 0
            ]);
        }
        $this->onboardingCountriesKSMReferenceNumber->create([
            'application_id' => $request['application_id'] ?? 0,
            'onboarding_country_id' => $onboardingCountry->id ?? $checkCountry->id,
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'valid_until' => $request['valid_until'] ?? '',
            'quota' => $request['quota'] ?? 0,
            'utilised_quota' => $request['utilised_quota'] ?? 0,
            'status' => $request['status'] ?? 1,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        if (!empty($checkCountry)) {
            $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($checkCountry->id);
            $onboardingCountryDetails->quota = $onboardingCountryDetails->quota + $request['quota'];
            $onboardingCountryDetails->save();
        }
        return true;
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
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->findOrFail($request['id']);
        // $interviewApproved = $this->applicationInterviews->where('application_id', $onboardingCountry->application_id)
        //                 ->where('status', 'Approved')
        //                 ->sum('approved_quota');
        // $countriesQuota = $this->directRecruitmentOnboardingCountry
        //                     ->where('application_id', $onboardingCountry->application_id)
        //                     ->whereNot(function ($query) use ($request){
        //                         $query->where('id', $request['id']);
        //                     })
        //                     ->sum('quota'); 
        // $countriesQuota += $request['quota'];
        // if($countriesQuota > $interviewApproved) {
        //     return [
        //         'quotaError' => true
        //     ];
        // }
        $attestationDetails = $this->onboardingAttestation->where('onboarding_country_id', $request['id'])->first(['status']);
        if(isset($attestationDetails)) {
            if ($attestationDetails->status == 'Collected') {
                return [
                    'editError' => true
                ];
            } 
        }
        $onboardingCountry->application_id =  $request['application_id'] ?? $onboardingCountry->application_id;
        $onboardingCountry->country_id =  $request['country_id'] ?? $onboardingCountry->country_id;
        // $onboardingCountry->quota =  $request['quota'] ?? $onboardingCountry->quota;
        $onboardingCountry->status =  $request['status'] ?? $onboardingCountry->status;
        $onboardingCountry->utilised_quota =  $request['utilised_quota'] ?? $onboardingCountry->utilised_quota;
        $onboardingCountry->modified_by =  $request['modified_by'] ?? $onboardingCountry->modified_by;
        $onboardingCountry->save();
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function ksmReferenceNumberList($request): mixed
    {
        $ksmReferenceNumbers = $this->directRecruitmentApplicationApproval
                                    ->leftJoin('levy', 'levy.new_ksm_reference_number', 'directrecruitment_application_approval.ksm_reference_number')
                                    ->where('directrecruitment_application_approval.application_id', $request['application_id'])
                                    ->whereIn('levy.status', Config::get('services.APPLICATION_LEVY_KSM_REFERENCE_STATUS'))
                                    ->select('directrecruitment_application_approval.id','directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota', 'directrecruitment_application_approval.valid_until')
                                    ->orderBy('directrecruitment_application_approval.created_at','DESC')
                                    ->get()->toArray();
                        
        $utilisedQuota = $this->workers
        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->whereNotIn('workers.directrecruitment_status', Config::get('services.NOT_UTILISED_STATUS_TYPE'))
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->select('worker_visa.ksm_reference_number', DB::raw('COUNT(workers.id) as utilised_quota')/*, DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id')*/)
        ->groupBy('worker_visa.ksm_reference_number')
        ->orderBy('worker_visa.ksm_reference_number','DESC')
        ->get()->toArray();

        foreach($ksmReferenceNumbers as $key => $value) {
            $ksmReferenceNumbers[$key]['utilised_quota'] = 0;
            foreach($utilisedQuota as $utilisedKey => $utilisedValue) {
                if($utilisedQuota[$utilisedKey]['ksm_reference_number'] == $ksmReferenceNumbers[$key]['ksm_reference_number']) {
                    $ksmReferenceNumbers[$key]['utilised_quota'] = $utilisedQuota[$utilisedKey]['utilised_quota'];
                    // $ksmReferenceNumbers[$key]['worker_id'] = $utilisedQuota[$utilisedKey]['workers_id'];
                }
            }
        }
        return $ksmReferenceNumbers;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function onboarding_status_update($request): bool|array
    {
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->findOrFail($request['country_id']);

        //if($request['onboarding_status'] > $onboardingCountry->onboarding_status){

            $onboardingCountry->onboarding_status =  $request['onboarding_status'];
            $onboardingCountry->save();

        //}        
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function ksmDropDownForOnboarding($request): mixed
    {
        return $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])
        ->select('id', 'ksm_reference_number', 'valid_until')
        ->orderBy('created_at','DESC')
        ->get(); 
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function ksmQuotaUpdate($request): bool|array
    {
        $validator = Validator::make($request, $this->ksmUpdateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->findOrFail($request['id']);
        $levyApproved = $this->levy->where('application_id', $ksmDetails->application_id)
                        ->where('new_ksm_reference_number', $ksmDetails->ksm_reference_number)
                        ->where('status', 'Paid')->sum('approved_quota');
                
        $ksmQuota = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetails->application_id)
                            ->where('ksm_reference_number', $ksmDetails->ksm_reference_number)
                            ->where('id', '<>', $request['id'])
                            ->sum('quota');
        
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }
        
        $attestationDetails = $this->onboardingAttestation->where('onboarding_country_id', $request['onboarding_country_id'])->first(['status']);
        if(isset($attestationDetails)) {
            if ($attestationDetails->status == 'Collected') {
                return [
                    'editError' => true
                ];
            } 
        }
        $currentQuota = 0;
        $oldKSMQuota = $ksmDetails->quota;
        $ksmDetails->ksm_reference_number =  $request['ksm_reference_number'] ?? $ksmDetails->ksm_reference_number;
        $ksmDetails->quota =  $request['quota'] ?? $ksmDetails->quota;
        $ksmDetails->modified_by =  $request['modified_by'] ?? $ksmDetails->modified_by;
        $ksmDetails->save();
        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($request['onboarding_country_id']);
        $currentQuota = $onboardingCountryDetails->quota - $oldKSMQuota;
        $onboardingCountryDetails->quota = $currentQuota + $request['quota'];
        $onboardingCountryDetails->save();
        return true;
    }
}
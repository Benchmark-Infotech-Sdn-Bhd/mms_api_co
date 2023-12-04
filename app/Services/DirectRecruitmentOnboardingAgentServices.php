<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\OnboardingAttestation;

class DirectRecruitmentOnboardingAgentServices
{
    /**
     * @var DirectRecruitmentOnboardingAgent
     */
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;

    /**
     * @var DirectRecruitmentOnboardingAttestationServices
     */
    private DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var OnboardingCountriesKSMReferenceNumber
     */
    private OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber;
    /**
     * @var OnboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;

    /**
     * DirectRecruitmentOnboardingAgentServices constructor.
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
     * @param DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber
     * @param OnboardingAttestation $onboardingAttestation
     */
    public function __construct(DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent, DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices, DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber, OnboardingAttestation $onboardingAttestation)
    {
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->directRecruitmentOnboardingAttestationServices = $directRecruitmentOnboardingAttestationServices;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
        $this->onboardingAttestation = $onboardingAttestation;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'onboarding_country_id' => 'required',
            'agent_id' => 'required',
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
            'agent_id' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->directRecruitmentOnboardingAgent->leftJoin('agent', 'agent.id', 'directrecruitment_onboarding_agent.agent_id')
            ->where([
                ['directrecruitment_onboarding_agent.application_id', $request['application_id']],
                ['directrecruitment_onboarding_agent.onboarding_country_id', $request['onboarding_country_id']],
            ])
            ->select('directrecruitment_onboarding_agent.id', 'agent.agent_name', 'agent.person_in_charge', 'agent.pic_contact_number', 'directrecruitment_onboarding_agent.quota', 'directrecruitment_onboarding_agent.updated_at', 'directrecruitment_onboarding_agent.ksm_reference_number')
            ->orderBy('directrecruitment_onboarding_agent.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingAgent->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function create($request): bool|array
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $checkAgent = $this->directRecruitmentOnboardingAgent
                        ->where('agent_id', $request['agent_id'])
                        ->where('application_id', $request['application_id'])
                        ->where('onboarding_country_id', $request['onboarding_country_id'])
                        ->where('ksm_reference_number', $request['ksm_reference_number'])
                        ->first();
        if(!empty($checkAgent)) {
            return [
                'agentError' => true
            ];
        }
        
        $countriesQuota = $this->onboardingCountriesKSMReferenceNumber
                                ->where('application_id', $request['application_id'])
                                ->where('onboarding_country_id', $request['onboarding_country_id'])
                                ->where('ksm_reference_number', $request['ksm_reference_number'])
                                ->sum('quota');                        
        $agentQuota = $this->directRecruitmentOnboardingAgent
                        ->where('application_id', $request['application_id'])
                        ->where('onboarding_country_id', $request['onboarding_country_id'])
                        ->where('ksm_reference_number', $request['ksm_reference_number'])
                        ->sum('quota');

        $agentQuota += $request['quota'];

        if($agentQuota > $countriesQuota) {
            return [
                'quotaError' => true
            ];
        }

        $onboardingDetails = $this->directRecruitmentOnboardingAgent->create([
            'application_id' => $request['application_id'] ?? 0,
            'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
            'agent_id' => $request['agent_id'] ?? 0,
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'quota' => $request['quota'] ?? 0,
            'status' => $request['status'] ?? 1,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        $request['onboarding_agent_id'] = $onboardingDetails['id'];
        $this->directRecruitmentOnboardingAttestationServices->create($request);

        $onBoardingStatus['application_id'] = $request['application_id'];
        $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
        $onBoardingStatus['onboarding_status'] = 2; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
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
        $onboardingAgent = $this->directRecruitmentOnboardingAgent->findOrFail($request['id']);
        $request['old_ksm_reference_number'] = $onboardingAgent->ksm_reference_number;
        
        $attestationDetails = $this->onboardingAttestation
                                ->where('onboarding_country_id', $request['onboarding_country_id'])
                                ->where('onboarding_agent_id', $request['id'])
                                ->where('ksm_reference_number', $onboardingAgent->ksm_reference_number)
                                ->first(['status']);
                                
        if(isset($attestationDetails) && !empty($attestationDetails)) {
            if ($attestationDetails->status == 'Collected') {
                return [
                    'editError' => true
                ];
            } 
        }

        $checkAgent = $this->directRecruitmentOnboardingAgent
                        ->where('agent_id', $request['agent_id'])
                        ->where('application_id', $request['application_id'])
                        ->where('onboarding_country_id', $request['onboarding_country_id'])
                        ->where('ksm_reference_number', $request['ksm_reference_number'])
                        ->where('id', '<>', $request['id'])
                        ->first();
        if(!empty($checkAgent)) {
            return [
                'agentError' => true
            ];
        }
    
        $countriesQuota = $this->onboardingCountriesKSMReferenceNumber
                                ->where('application_id', $request['application_id'])
                                ->where('onboarding_country_id', $request['onboarding_country_id'])
                                ->where('ksm_reference_number', $request['ksm_reference_number'])
                                ->sum('quota');
        $agentQuota = $this->directRecruitmentOnboardingAgent
                        ->where('application_id', $request['application_id'])
                        ->where('onboarding_country_id', $request['onboarding_country_id'])
                        ->where('ksm_reference_number', $request['ksm_reference_number'])
                        ->where('id', '<>', $request['id'])
                        ->sum('quota');

        $agentQuota += $request['quota'];

        if($agentQuota > $countriesQuota) {
            return [
                'quotaError' => true
            ];
        }
        $onboardingAgent->agent_id =  $request['agent_id'] ?? $onboardingAgent->agent_id;
        $onboardingAgent->ksm_reference_number = $request['ksm_reference_number'] ?? $onboardingAgent->ksm_reference_number;
        $onboardingAgent->quota =  $request['quota'] ?? $onboardingAgent->quota;
        $onboardingAgent->status =  $request['status'] ?? $onboardingAgent->status;
        $onboardingAgent->modified_by =  $request['modified_by'] ?? $onboardingAgent->modified_by;
        $onboardingAgent->save();

        $this->directRecruitmentOnboardingAttestationServices->updateKSMReferenceNumber($request);

        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function ksmDropDownBasedOnOnboarding($request): mixed
    {
        return $this->onboardingCountriesKSMReferenceNumber->where('onboarding_country_id', $request['onboarding_country_id'])
        ->select('id', 'ksm_reference_number')
        ->get(); 
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\OnboardingAttestation;
use App\Models\DirectrecruitmentApplications;
use App\Services\DirectRecruitmentOnboardingCountryServices;

class DirectRecruitmentOnboardingAgentServices
{

    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_AGENT_ID = 'agent_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';

    public const ONBOARDING_STATUS = 2;
    public const DEFAULT_INT_VALUE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;
    
    /**
     * @var DirectRecruitmentOnboardingAgent
     */
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;

    /**
     * @var DirectRecruitmentOnboardingAttestationServices
     */
    private DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices;

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
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * DirectRecruitmentOnboardingAgentServices Constructor method.
     * 
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent The Direct Recruitment onboarding agent instance
     * @param DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices The Direct Recruitment onboarding attestation instance
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The Direct Recruitment onboarding country instance
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber The Direct Recruitment onboarding countries KSM Reference number instance
     * @param OnboardingAttestation $onboardingAttestation The Direct Recruitment onboarding attestation instance
     * @param DirectrecruitmentApplications $directrecruitmentApplications The Direct Recruitment application instance
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The Direct Recruitment onboarding country services
     */
    public function __construct(
        DirectRecruitmentOnboardingAgent                $directRecruitmentOnboardingAgent, 
        DirectRecruitmentOnboardingAttestationServices  $directRecruitmentOnboardingAttestationServices, 
        DirectRecruitmentOnboardingCountry              $directRecruitmentOnboardingCountry, 
        OnboardingCountriesKSMReferenceNumber           $onboardingCountriesKSMReferenceNumber, 
        OnboardingAttestation                           $onboardingAttestation, 
        DirectrecruitmentApplications                   $directrecruitmentApplications, 
        DirectRecruitmentOnboardingCountryServices      $directRecruitmentOnboardingCountryServices
    )
    {
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->directRecruitmentOnboardingAttestationServices = $directRecruitmentOnboardingAttestationServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
        $this->onboardingAttestation = $onboardingAttestation;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
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
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
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
     * Returns a paginated list of direct recruitment on boarding agent with their direct recruitment application details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment onboarding agent with direct recruitment application details.
     */ 
    public function list($request): mixed
    {
        return $this->directRecruitmentOnboardingAgent->leftJoin('agent', 'agent.id', 'directrecruitment_onboarding_agent.agent_id')
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where([
                ['directrecruitment_onboarding_agent.application_id', $request[self::REQUEST_APPLICATION_ID]],
                ['directrecruitment_onboarding_agent.onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID]],
            ])
            ->select('directrecruitment_onboarding_agent.id', 'agent.agent_name', 'agent.person_in_charge', 'agent.pic_contact_number', 'directrecruitment_onboarding_agent.quota', 'directrecruitment_onboarding_agent.updated_at', 'directrecruitment_onboarding_agent.ksm_reference_number')
            ->orderBy('directrecruitment_onboarding_agent.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Show the direct recruitment on boarding agent details.
     *
     * @param array $request The request data containing company id,  id of onboarding agent
     * @return mixed Returns details of  direct recruitment on boarding agent.
     */
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingAgent->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })->where('directrecruitment_onboarding_agent.id', $request['id'])->select('directrecruitment_onboarding_agent.id', 'directrecruitment_onboarding_agent.application_id', 'directrecruitment_onboarding_agent.onboarding_country_id', 'directrecruitment_onboarding_agent.agent_id', 'directrecruitment_onboarding_agent.ksm_reference_number', 'directrecruitment_onboarding_agent.quota', 'directrecruitment_onboarding_agent.status', 'directrecruitment_onboarding_agent.created_by', 'directrecruitment_onboarding_agent.modified_by', 'directrecruitment_onboarding_agent.created_at', 'directrecruitment_onboarding_agent.updated_at', 'directrecruitment_onboarding_agent.deleted_at')->first();
    }

    /**
     * Get the agent details for the given input details
     * 
     * - agent_id: Agent Id value
     * - application_id: Direct Recruitment Application Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     *
     * @param array $request The data used to get the agent details.
     * Return mixed the agent object
     */
    public function checkAgentData(mixed $request): mixed
    {
        $agentId = $request['id'] ?? '';
        return $this->directRecruitmentOnboardingAgent
            ->where('agent_id', $request[self::REQUEST_AGENT_ID])
            ->where('application_id', $request[self::REQUEST_APPLICATION_ID])
            ->where('onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
            ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
            ->where(function ($query) use ($agentId) {
                $query->where('id', '<>', $agentId);
            })
            ->first();
    }

    /**
     * Get the country quota for the given input details
     * 
     * - application_id: Direct Recruitment Application Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - ksm_reference_number: KSM Reference number value
     *
     * @param array $request The data used to get the country quota.
     * Return int country quota
     */
    public function getCountriesQuota(mixed $request): int
    {
        return $this->onboardingCountriesKSMReferenceNumber
                ->where('application_id', $request[self::REQUEST_APPLICATION_ID])
                ->where('onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
                ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
                ->sum('quota'); 
    }

    /**
     * Get the agent quota for the given input details
     * 
     * - application_id: Direct Recruitment Application Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - ksm_reference_number: KSM Reference number value
     *
     * @param array $request The data used to get the agent quota.
     * Return int agent quota
     */
    public function getAgentQuota(mixed $request): int
    {
        $agentId = $request['id'] ?? '';
        return $this->directRecruitmentOnboardingAgent
                ->where('application_id', $request[self::REQUEST_APPLICATION_ID])
                ->where('onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
                ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
                ->where(function ($query) use ($agentId) {
                    $query->where('id', '<>', $agentId);
                })
                ->sum('quota'); 
    }

    /**
     * Create a new Direct Recruitment Onboarding Agent .
     *
     * @param array $inputData The data used to create the Direct Recruitment Onboarding Agent.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - agent_id: Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - quota: Total quota assigned for the agent.
     * - status: Direct Recruitment Onboarding Agent status .
     * - created_by: The user who created the Direct Recruitment Onboarding Agent.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Agent.
     *
     * @param array $request to create a new Direct Recruitment Onboarding Agent .
     * @return mixed The newly created Direct Recruitment Onboarding Agent.
     * 
     */
    public function createDirectRecruitmentOnboardingAgent(array $request):mixed
    {
        return $onboardingDetails = $this->directRecruitmentOnboardingAgent->create([
            'application_id' => $request[self::REQUEST_APPLICATION_ID] ?? self::DEFAULT_INT_VALUE,
            'onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID] ?? self::DEFAULT_INT_VALUE,
            'agent_id' => $request[self::REQUEST_AGENT_ID] ?? self::DEFAULT_INT_VALUE,
            'ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? '',
            'quota' => $request['quota'] ?? self::DEFAULT_INT_VALUE,
            'status' => $request['status'] ?? self::STATUS_ACTIVE,
            'created_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE
        ]);
    }

    /**
     * Get the agent quota for the given input $requestApplicationId, $requestOnboardingCountryId
     * 
     * - requestApplicationId: Direct Recruitment Application Id
     * - requestOnboardingCountryId: Direct Recruitment Onboarding Country Id.
     *
     * @param int $requestApplicationId, int $requestOnboardingCountryId
     * Return void
     */
    public function onboardingStatusUpdate(int $requestApplicationId, int $requestOnboardingCountryId): void
    {
        $onBoardingStatus['application_id'] = $requestApplicationId;
        $onBoardingStatus['country_id'] = $requestOnboardingCountryId;
        $onBoardingStatus['onboarding_status'] = self::ONBOARDING_STATUS; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
    }
 
    /**
     * Create a new direct recruitment on boarding agent.
     *
     * @param array $request The data used to create the direct recruitment on boarding agent.
     * The array should contain the following keys:
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - application_id: Direct Recruitment Application Id
     * - company_id: Company Id
     * - quota: Total no of Quota 
     * - agent_id: Agent Id value
     * - ksm_reference_number: KSM Reference Number value
     * - created_by: The user who created the Direct Recruitment Arrival.
     * - modified_by: The user who modified the Direct Recruitment Arrival.
     *
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "isInvalidUser": A boolean returns true if user is invalid.
     * - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     * - "agentError": A boolean returns true if agent is not mapped with application, onBoardingAgent and KSM reference number.
     * - "quotaError": A boolean returns true if the given quota exceeds the country quota.
     * 
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $onboardingCheck = $this->directRecruitmentOnboardingCountry->find($request[self::REQUEST_ONBOARDING_COUNTRY_ID]);
        $applicationCheck = $this->directrecruitmentApplications->find($request[self::REQUEST_APPLICATION_ID]);

        if($onboardingCheck->application_id != $request[self::REQUEST_APPLICATION_ID]) {
            return [
                'InvalidUser' => true
            ];
        } else if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' => true
            ];
        }

        $checkAgent = $this->checkAgentData($request);
        if(!empty($checkAgent)) {
            return [
                'agentError' => true
            ];
        } 
        
        $countriesQuota = $this->getCountriesQuota($request);

        $agentQuota = $this->getAgentQuota($request);
        $agentQuota += $request['quota'];

        if($agentQuota > $countriesQuota) {
            return [
                'quotaError' => true
            ];
        }

        $onboardingDetails = $this->createDirectRecruitmentOnboardingAgent($request);
        
        $request['onboarding_agent_id'] = $onboardingDetails['id'];
        $this->directRecruitmentOnboardingAttestationServices->create($request);

        $this->onboardingStatusUpdate($request[self::REQUEST_APPLICATION_ID], $request[self::REQUEST_ONBOARDING_COUNTRY_ID]);        

        return true;
    }

    /**
     * Get the onboarding agent details for the given input
     * 
     * - id: Direct Recruitment onBoarding Agent Id
     * - company_id: Direct Recruitment application Company Id.
     *
     * @param array $request The data used to get the onboarding agent details.
     * Return mixed onboarding agent details
     */
    public function getOnboardingAgent(array $request): mixed
    {
        return $this->directRecruitmentOnboardingAgent
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })->select('directrecruitment_onboarding_agent.id', 'directrecruitment_onboarding_agent.application_id', 'directrecruitment_onboarding_agent.onboarding_country_id', 'directrecruitment_onboarding_agent.agent_id', 'directrecruitment_onboarding_agent.ksm_reference_number', 'directrecruitment_onboarding_agent.quota', 'directrecruitment_onboarding_agent.status', 'directrecruitment_onboarding_agent.created_by', 'directrecruitment_onboarding_agent.modified_by', 'directrecruitment_onboarding_agent.created_at', 'directrecruitment_onboarding_agent.updated_at', 'directrecruitment_onboarding_agent.deleted_at')
        ->find($request['id']);
    }

    /**
     * Get the onboarding attestation status for the given input
     * 
     * - id: Direct Recruitment onBoarding Agent Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - ksm_reference_number: KSM Reference number field result from onBoarding Agent table.
     *
     * @param array $request The data used to get the onboarding attestation status.
     * Return mixed onboarding attestation status
     */
    public function getOnboardingAttestationStatus(array $request): mixed
    {
        return $this->onboardingAttestation
            ->where('onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
            ->where('onboarding_agent_id', $request['id'])
            ->where('ksm_reference_number', $request['old_ksm_reference_number'])
            ->first(['status']);
    }

    /**
     * Update Direct Recruitment Onboarding Agent .
     *
     * @param array $request, array $onboardingAgent The data used to update the Direct Recruitment Onboarding Agent.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - agent_id: Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - quota: Total quota assigned for the agent.
     * - status: Direct Recruitment Onboarding Agent status .
     * - created_by: The user who created the Direct Recruitment Onboarding Agent.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Agent.
     *
     * @return void
     */
    public function updateOnboardingAgent(array $request, object $onboardingAgent):void
    {
        $onboardingAgent->agent_id =  $request[self::REQUEST_AGENT_ID] ?? $onboardingAgent->agent_id;
        $onboardingAgent->ksm_reference_number = $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? $onboardingAgent->ksm_reference_number;
        $onboardingAgent->quota =  $request['quota'] ?? $onboardingAgent->quota;
        $onboardingAgent->status =  $request['status'] ?? $onboardingAgent->status;
        $onboardingAgent->modified_by =  $request['modified_by'] ?? $onboardingAgent->modified_by;
        $onboardingAgent->save();
    }

     /**
     * Update the direct recruitment on boarding agent.
     *
     * @param array $request The data used to update the direct recruitment on boarding agent.
     * The array should contain the following keys:
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - application_id: Direct Recruitment Application Id
     * - company_id: Company Id
     * - quota: Total no of Quota 
     * - agent_id: Agent Id value
     * - ksm_reference_number: KSM Reference Number value
     * - created_by: The user who created the Direct Recruitment Arrival.
     * - modified_by: The user who modified the Direct Recruitment Arrival.
     *
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "isInvalidUser": A boolean returns true if user is invalid.
     * - "isInvalidUser": A boolean returns true if user access invalid application which is not not in his beloning company
     * - "editError": A boolean returns true if requested attestation status in 'Collected'
     * - "agentError": A boolean returns true if agent is not mapped with application, onBoardingAgent and KSM reference number.
     * - "quotaError": A boolean returns true if the given quota exceeds the country quota.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingAgent = $this->getOnboardingAgent($request);

        if(is_null($onboardingAgent)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['application_id'] = $onboardingAgent->application_id;
        $request['onboarding_country_id'] = $onboardingAgent->onboarding_country_id;
        $request['old_ksm_reference_number'] = $onboardingAgent->ksm_reference_number;
        
        $attestationDetails = $this->getOnboardingAttestationStatus($request);
                                
        if(isset($attestationDetails) && !empty($attestationDetails)) {
            if ($attestationDetails->status == 'Collected') {
                return [
                    'editError' => true
                ];
            } 
        }

        $checkAgent = $this->checkAgentData($request);
                        
        if(!empty($checkAgent)) {
            return [
                'agentError' => true
            ];
        }
    
        $countriesQuota = $this->getCountriesQuota($request);
        
        $agentQuota = $this->getAgentQuota($request);
        $agentQuota += $request['quota'];

        if($agentQuota > $countriesQuota) {
            return [
                'quotaError' => true
            ];
        }

        $this->updateOnboardingAgent($request, $onboardingAgent);

        $this->directRecruitmentOnboardingAttestationServices->updateKSMReferenceNumber($request);

        return true;
    }    

    /**
     * List the Dropdown values of KSM reference dropdown from onBoarding Country.
     *
     * @param array $request The request data containing company id,  onboarding country id
     * @return mixed Returns Dropdown values of KSM reference dropdown from onBoarding Country.
     */
    public function ksmDropDownBasedOnOnboarding($request): mixed
    {
        return $this->onboardingCountriesKSMReferenceNumber
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_countries_ksm_reference_number.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
        ->where('onboarding_countries_ksm_reference_number.onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
        ->select('onboarding_countries_ksm_reference_number.id', 'onboarding_countries_ksm_reference_number.ksm_reference_number')
        ->get(); 
    }
}
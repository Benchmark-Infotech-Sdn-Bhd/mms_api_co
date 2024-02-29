<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\OnboardingAttestation;
use App\Models\DirectrecruitmentApplications;

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
        DirectRecruitmentOnboardingAgent               $directRecruitmentOnboardingAgent,
        DirectRecruitmentOnboardingAttestationServices $directRecruitmentOnboardingAttestationServices,
        DirectRecruitmentOnboardingCountry             $directRecruitmentOnboardingCountry,
        OnboardingCountriesKSMReferenceNumber          $onboardingCountriesKSMReferenceNumber,
        OnboardingAttestation                          $onboardingAttestation,
        DirectrecruitmentApplications                  $directrecruitmentApplications,
        DirectRecruitmentOnboardingCountryServices     $directRecruitmentOnboardingCountryServices
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
     * Retrieves a list of onboarding agents based on the given request parameters
     *
     * @param array $request The request parameters
     *     - int $request['company_id']: The company ID to filter on
     *     - int $request['application_id']: The application ID to filter on
     *     - int $request['onboarding_country_id']: The onboarding country ID to filter on
     * @return LengthAwarePaginator The paginated list of onboarding agents
     */
    public function list($request): LengthAwarePaginator
    {
        return $this->directRecruitmentOnboardingAgent->leftJoin('agent', 'agent.id', 'directrecruitment_onboarding_agent.agent_id')
            ->join('directrecruitment_applications', function ($join) use ($request) {
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
     * Retrieves the details of an onboarding agent
     *
     * @param array $request The request parameters
     *     - int $request['id'] The ID of the onboarding agent
     *     - int|array $request[self::REQUEST_COMPANY_ID] The ID(s) of the company(s) associated with the onboarding agent
     * @return mixed The onboarding agent details
     */
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingAgent->join('directrecruitment_applications', function ($join) use ($request) {
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
     * Creates a direct recruitment onboarding agent
     *
     * @param array $request The request data
     * @return mixed The created onboarding agent
     */
    public function createDirectRecruitmentOnboardingAgent(array $request)
    {
        return $this->directRecruitmentOnboardingAgent->create([
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
     * Updates the onboarding status for an application in a specific country
     *
     * @param int $applicationId The ID of the application
     * @param int $onboardingCountryId The ID of the onboarding country
     * @return void
     */
    public function updateOnboardingStatus(int $applicationId, int $onboardingCountryId): void
    {
        $onBoardingStatus = [
            'applicationId' => $applicationId,
            'countryId' => $onboardingCountryId,
            'onboardingStatus' => self::ONBOARDING_STATUS //Agent Added
        ];
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
    }

    /**
     * Create a new direct recruitment onboarding agent.
     *
     * @param array $request The data used to create the direct recruitment onboarding agent.
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
     * - "isInvalidUser": A boolean returns true if user access invalid application which is not in his belonging company
     * - "agentError": A boolean returns true if agent is not mapped with application, onBoardingAgent and KSM reference number.
     * - "quotaError": A boolean returns true if the given quota exceeds the country quota.
     *
     */
    public function create($request): bool|array
    {
        if (!$this->isValidRequest($request)) {
            return ['error' => 'Invalid Request'];
        }

        if (!$this->hasValidUser($request)) {
            return ['InvalidUser' => true];
        }

        if ($this->hasAgentError($request)) {
            return ['agentError' => true];
        }

        if (!$this->hasValidAgentQuota($request)) {
            return ['quotaError' => true];
        }

        $onboardingDetails = $this->createDirectRecruitmentOnboardingAgent($request);
        $request['onboarding_agent_id'] = $onboardingDetails['id'];
        $this->directRecruitmentOnboardingAttestationServices->create($request);
        $this->onboardingStatusUpdate($request[self::REQUEST_ONBOARDING_COUNTRY_ID], self::ONBOARDING_STATUS);
        return true;
    }

    /**
     * Check if the given request is valid
     *
     * @param array $request The request data to validate
     *
     * @return bool Returns true if the request is valid, false otherwise
     */
    private function isValidRequest($request): bool
    {
        $validator = Validator::make($request, $this->createValidation());
        return !$validator->fails();
    }

    /**
     * Check if the user is valid for onboarding
     *
     * - REQUEST_ONBOARDING_COUNTRY_ID: Direct Recruitment onBoarding Country Id
     * - REQUEST_APPLICATION_ID: Direct Recruitment application Id
     * - REQUEST_COMPANY_ID: Direct Recruitment application Company Id.
     *
     * @param array $request The data used to check the validity of the user.
     * @return bool True if the user is valid for onboarding, False otherwise.
     */
    private function hasValidUser($request): bool
    {
        $onboardingCheck = $this->directRecruitmentOnboardingCountry->find($request[self::REQUEST_ONBOARDING_COUNTRY_ID]);
        $applicationCheck = $this->directrecruitmentApplications->find($request[self::REQUEST_APPLICATION_ID]);
        return $onboardingCheck->application_id === $request[self::REQUEST_APPLICATION_ID] &&
            $applicationCheck->company_id === $request[self::REQUEST_COMPANY_ID];
    }

    /**
     * Check if there is an error in the agent data for the given input.
     *
     * @param mixed $request The data used to check the agent data.
     * @return bool True if there is an error, false otherwise.
     */
    private function hasAgentError($request): bool
    {
        $checkAgent = $this->checkAgentData($request);
        return !empty($checkAgent);
    }

    /**
     * Check if the agent has a valid quota for the given request
     *
     * This method calculates the agent's quota based on the countries quota and the requested quota.
     * It returns true if the agent has a valid quota, false otherwise.
     *
     * @param array $request The data used to check the agent's quota.
     *     - id: Direct Recruitment onBoarding Agent Id
     *     - company_id: Direct Recruitment application Company Id.
     *     - quota: The quota requested for the agent.
     *
     * @return bool True if the agent has a valid quota, false otherwise.
     */
    private function hasValidAgentQuota($request): bool
    {
        $countriesQuota = $this->getCountriesQuota($request);
        $agentQuota = $this->getAgentQuota($request);
        $agentQuota += $request['quota'];
        return $agentQuota <= $countriesQuota;
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
    public function getOnboardingAgent(array $request)
    {
        return $this->directRecruitmentOnboardingAgent
            ->join('directrecruitment_applications', function ($join) use ($request) {
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
     * @param array $request , array $onboardingAgent The data used to update the Direct Recruitment Onboarding Agent.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - agent_id: Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - quota: Total quota assigned for the agent.
     * - status: Direct Recruitment Onboarding Agent status .
     * - created_by: The user who created the Direct Recruitment Onboarding Agent.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Agent.
     * @param object $onboardingAgent
     * @return void
     */
    public function updateOnboardingAgent(array $request, object $onboardingAgent): void
    {
        $onboardingAgent->agent_id = $request[self::REQUEST_AGENT_ID] ?? $onboardingAgent->agent_id;
        $onboardingAgent->ksm_reference_number = $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? $onboardingAgent->ksm_reference_number;
        $onboardingAgent->quota = $request['quota'] ?? $onboardingAgent->quota;
        $onboardingAgent->status = $request['status'] ?? $onboardingAgent->status;
        $onboardingAgent->modified_by = $request['modified_by'] ?? $onboardingAgent->modified_by;
        $onboardingAgent->save();
    }

    /**
     * Update the direct recruitment onboarding agent.
     *
     * @param array $request The data used to update the direct recruitment onboarding agent.
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
     * - "isInvalidUser": A boolean returns true if user access invalid application which is not in his belonging company
     * - "editError": A boolean returns true if requested attestation status in 'Collected'
     * - "agentError": A boolean returns true if agent is not mapped with application, onBoardingAgent and KSM reference number.
     * - "quotaError": A boolean returns true if the given quota exceeds the country quota.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingAgent = $this->checkForOnboardingAgent($request['company_id'], $request['id']);

        if (is_null($onboardingAgent)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['application_id'] = $onboardingAgent->application_id;
        $request['onboarding_country_id'] = $onboardingAgent->onboarding_country_id;
        $request['old_ksm_reference_number'] = $onboardingAgent->ksm_reference_number;

        $attestationDetails = $this->getOnboardingAttestationStatus($request);

        if (!empty($attestationDetails)) {
            if ($attestationDetails->status == 'Collected') {
                return [
                    'editError' => true
                ];
            }
        }

        $checkAgent = $this->checkAgentData($request);

        if (!empty($checkAgent)) {
            return [
                'agentError' => true
            ];
        }

        $countriesQuota = $this->getCountriesQuota($request);

        $agentQuota = $this->getAgentQuota($request);
        $agentQuota += $request['quota'];

        if ($agentQuota > $countriesQuota) {
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
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('onboarding_countries_ksm_reference_number.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_countries_ksm_reference_number.onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
            ->select('onboarding_countries_ksm_reference_number.id', 'onboarding_countries_ksm_reference_number.ksm_reference_number')
            ->get();
    }

    /**
     * checks the boarding agent exist for the particular company
     *
     * @param int $companyId
     * @param int $onboardingAgentId
     * @return mixed The onboarding agent details
     */
    private function checkForOnboardingAgent(int $companyId, int $onboardingAgentId): mixed
    {
        return $this->directRecruitmentOnboardingAgent
            ->join('directrecruitment_applications', function ($join) use ($companyId) {
                $join->on('directrecruitment_onboarding_agent.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $companyId);
            })->select('directrecruitment_onboarding_agent.id', 'directrecruitment_onboarding_agent.application_id', 'directrecruitment_onboarding_agent.onboarding_country_id', 'directrecruitment_onboarding_agent.agent_id', 'directrecruitment_onboarding_agent.ksm_reference_number', 'directrecruitment_onboarding_agent.quota', 'directrecruitment_onboarding_agent.status', 'directrecruitment_onboarding_agent.created_by', 'directrecruitment_onboarding_agent.modified_by', 'directrecruitment_onboarding_agent.created_at', 'directrecruitment_onboarding_agent.updated_at', 'directrecruitment_onboarding_agent.deleted_at')
            ->find($onboardingAgentId);
    }

    /**
     * updates the onboarding status 
     * 
     * @param int $onboardingCountryId - refers the onboarding country id
     * @param int $onboardingStatus - onboarding status of the particular onboarding country
     * 
     * @return bool
     */
    public function onboardingStatusUpdate(int $onboardingCountryId, int $onboardingStatus): bool
    {
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->find($onboardingCountryId);
        $onboardingCountry->onboarding_status =  $onboardingStatus;
        $onboardingCountry->save();
        return true;
    }
}

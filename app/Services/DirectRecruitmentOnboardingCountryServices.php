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
use App\Models\DirectrecruitmentApplications;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\DB;
use App\Models\DirectRecruitmentOnboardingAgent;

class DirectRecruitmentOnboardingCountryServices
{

    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_ONBOARDING_AGENT_ID = 'onboarding_agent_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';

    public const LEVY_APPROVED_STATUS = 'Paid';
    public const DEFAULT_INT_VALUE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;

    /**
     * @var ValidationServices $validationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval
     */
    private DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;
    /**
     * @var ApplicationInterviews $applicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;
     /**
     * @var OnboardingAttestation $onboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;
    /**
     * @var Workers $workers
     */
    private Workers $workers;
    /**
     * @var Levy $levy
     */
    private Levy $levy;
    /**
     * @var OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber
     */
    private OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber;
    /**
     * @var DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent
     */
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    /**
     * @var DirectrecruitmentApplications $directrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * DirectRecruitmentOnboardingCountryServices constructor method.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The Direct Recruitment OnBoarding Country instance
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval The Direct Recruitment application approval instance
     * @param ApplicationInterviews $applicationInterviews The application interviews instance
     * @param ValidationServices $validationServices The validation services
     * @param OnboardingAttestation $onboardingAttestation The onboarding attestation instance
     * @param Workers $workers The workers instance
     * @param Levy $levy The Levy instance
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber The onboarding Countries KSM Reference Number instance
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent The Direct Recruitment onboarding agent instance 
     * @param DirectrecruitmentApplications $directrecruitmentApplications The Direct Recruitment applications instance
     */
    public function __construct(
        DirectRecruitmentOnboardingCountry      $directRecruitmentOnboardingCountry,
        DirectRecruitmentApplicationApproval    $directRecruitmentApplicationApproval,
        ApplicationInterviews                   $applicationInterviews,
        ValidationServices                      $validationServices,
        OnboardingAttestation                   $onboardingAttestation,
        Workers                                 $workers,
        Levy                                    $levy,
        OnboardingCountriesKSMReferenceNumber   $onboardingCountriesKSMReferenceNumber,
        DirectRecruitmentOnboardingAgent        $directRecruitmentOnboardingAgent,
        DirectrecruitmentApplications           $directrecruitmentApplications
    )
    {
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->applicationInterviews = $applicationInterviews;
        $this->validationServices = $validationServices;
        $this->onboardingAttestation = $onboardingAttestation;
        $this->workers = $workers;
        $this->levy = $levy;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
    }
    /**
     * Creates the validation rules for creating a new entity.
     *
     * @return array The array containing the validation rules.
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
     * Returns the validation rules for the update action.
     *
     * @return array The validation rules for the update action.
     *
     * The returned array has the following structure:
     * [
     *     'id' => 'required',
     *     'country_id' => 'required'
     * ]
     *
     * The 'id' field is required, meaning it must be present in the request data.
     * The 'country_id' field is also required, meaning it must be present in the request data.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'country_id' => 'required'
        ];
    }
    /**
     * Returns the validation rules for the update action.
     *
     * @return array The validation rules for the update action.
     *
     * The returned array has the following structure:
     * [
     *     'id' => 'required',
     *     'ksm_reference_number' => 'required',
     *     'quota' => 'required',
     * ]
     *
     * The 'id' field is required, meaning it must be present in the request data.
     * The 'ksm_reference_number' field is required, meaning it must be present in the request data.
     * The 'quota' fields is required, meaning it must be present in the request data. max limit 3 and should be integer values
     */
    public function ksmUpdateValidation(): array
    {
        return [
            'id' => 'required',
            'ksm_reference_number' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3'
        ];
    }
    /**
     * Returns the validation rules for the add action.
     *
     * @return array The validation rules for the add action.
     *
     * The returned array has the following structure:
     * [
     *     'onboarding_country_id' => 'required',
     *     'ksm_reference_number' => 'required',
     *     'quota' => 'required',
     * ]
     *
     * The 'onboarding_country_id' field is required, meaning it must be present in the request data.
     * The 'ksm_reference_number' field is required, meaning it must be present in the request data.
     * The 'quota' fields is required, meaning it must be present in the request data. max limit 3 and should be integer values
     */
    public function addKSMValidation(): array
    {
        return [
            'onboarding_country_id' => 'required',
            'ksm_reference_number' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3'
        ];
    }
    /**
     * Returns a paginated list of direct recruitment on boarding country with their direct recruitment application details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment onboarding agent with direct recruitment application details.
     */    
    public function list($request): mixed
    {

        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|regex:/^[a-zA-Z ]*$/|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        
        return $this->directRecruitmentOnboardingCountry
            ->with(['onboardingKSMReferenceNumbers' => function ($query) {
                $query->select('id', 'onboarding_country_id', 'ksm_reference_number', 'quota', 'utilised_quota');
            }])
            ->leftJoin('countries', 'countries.id', 'directrecruitment_onboarding_countries.country_id')
            ->leftJoin('directrecruitment_onboarding_status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.id')
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                     ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('directrecruitment_onboarding_countries.application_id', $request[self::REQUEST_APPLICATION_ID])
            ->where(function ($query) use ($request) {
                $this->applySearchParam($query, $request);
            })
            ->select('directrecruitment_onboarding_countries.id', 'countries.country_name as country', 'countries.system_type as system', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.name as onboarding_status_name')
            ->orderBy('directrecruitment_onboarding_countries.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Applies the search query to the given query builder.
     *
     * @param Builder $query The query builder to apply the search query to.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchParam($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('countries.country_name', 'like', "%{$request['search_param']}%");
        }
    }
    /**
     * Returns a direct recruitment on boarding country with their on baordig KSM reference number details.
     *
     * @param array $request The request data containing id, company id
     * @return mixed direct recruitment on boarding country with their on baordig KSM reference number details.
     */   
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingCountry
            ->with(['onboardingKSMReferenceNumbers' => function ($query) {
                $query->leftJoin('directrecruitment_onboarding_agent', function($join) {
                $join->on('directrecruitment_onboarding_agent.ksm_reference_number', 'onboarding_countries_ksm_reference_number.ksm_reference_number')
                ->on('directrecruitment_onboarding_agent.onboarding_country_id', 'onboarding_countries_ksm_reference_number.onboarding_country_id');
            })
            ->select('onboarding_countries_ksm_reference_number.id', 'onboarding_countries_ksm_reference_number.onboarding_country_id', 'onboarding_countries_ksm_reference_number.ksm_reference_number', 'onboarding_countries_ksm_reference_number.quota', 'onboarding_countries_ksm_reference_number.utilised_quota', 'directrecruitment_onboarding_agent.id as agent_id', \DB::raw("(CASE WHEN directrecruitment_onboarding_agent.id is not null THEN '0' ELSE '1' END) AS edit_flag"));
            }])
            ->leftJoin('directrecruitment_onboarding_status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.id')
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at', 'directrecruitment_onboarding_status.name as onboarding_status_name')->find($request['id']);
    }
    /**
     * Get the sum of Levy Approved count for the given input data
     * 
     * @param array $request which has the following keys
     * - applicationId (Int): application id
     * - ksm_reference_number: KSM Reference number
     *
     * @return int sum of Levy Approved count
     */
    public function getSumLevyApproved(array $request, $applicationId): int
    {
        return $this->levy->where('application_id', $applicationId)
        ->where('new_ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
        ->where('status', self::LEVY_APPROVED_STATUS)->sum('approved_quota');
    }
    /**
     * Get the sum of quota for the given input data
     * 
     * @param array $request which has the following keys
     * - applicationId: application id
     * - ksm_reference_number: KSM Reference number
     *
     * @return int sum of quota
     */
    public function getSumQuota(array $request, $applicationId, $id = ''): int
    {
        return $this->onboardingCountriesKSMReferenceNumber->where('application_id', $applicationId)
        ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
        ->where(function ($query) use ($id) {
            $query->where('id', '<>', $id);
        })
        ->sum('quota');
    }
     /**
     * Create a new Direct Recruitment on-boardig country.
     *
     * @param array $request The data used to create the Direct Recruitment On-boardig Country.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - country_id: Country Id.
     * - quota: Quota added for this application and country.
     * - utilised_quota: Total quota utilised.
     * - status: status.
     * - created_by: The user who created the Direct Recruitment on-boardig country.
     * - modified_by: The user who modified the Direct Recruitment on-boardig country.
     *
     * @return mixed The newly created Direct Recruitment on-boardig country.
     */
    public function createDirectRecruitmentOnboardingCountry(array $request)
    {
        return $this->directRecruitmentOnboardingCountry->create([
            'application_id' => $request[self::REQUEST_APPLICATION_ID] ?? self::DEFAULT_INT_VALUE,
            'country_id' => $request['country_id'] ?? self::DEFAULT_INT_VALUE,
            'quota' => $request['quota'] ?? self::DEFAULT_INT_VALUE,
            'utilised_quota' => $request['utilised_quota'] ?? self::DEFAULT_INT_VALUE,
            'status' => $request['status'] ?? self::STATUS_ACTIVE,
            'created_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE
        ]);
    }
    /**
     * Create a new Direct Recruitment on-boardig KSMReferenceNumber.
     *
     * @param array $request The data used to create the Direct Recruitment On-boardig KSMReferenceNumber.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: onboarding_country_id.
     * - ksm_reference_number: KSM Reference Number
     * - quota: Quota added for this application, onboarding_country_id and on-boardig KSMReferenceNumber.
     * - utilised_quota: Total quota utilised.
     * - status: status.
     * - created_by: The user who created the Direct Recruitment on-boardig KSMReferenceNumber.
     * - modified_by: The user who modified the Direct Recruitment on-boardig KSMReferenceNumber.
     *
     * @return void The newly created Direct Recruitment on-boardig KSMReferenceNumber.
     */
    public function createOnboardingCountriesKSMReferenceNumber(array $request, int $onboardingCountryId): void
    {
        $this->onboardingCountriesKSMReferenceNumber->create([
            'application_id' => $request[self::REQUEST_APPLICATION_ID] ?? self::DEFAULT_INT_VALUE,
            'onboarding_country_id' => $onboardingCountryId,
            'ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? '',
            'quota' => $request['quota'] ?? self::DEFAULT_INT_VALUE,
            'utilised_quota' => $request['utilised_quota'] ?? self::DEFAULT_INT_VALUE,
            'status' => $request['status'] ?? self::STATUS_ACTIVE,
            'created_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_INT_VALUE
        ]);
    }

    /**
     * Create the on Boarding Country on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - application_id : Direct recruitment application id
     * - country_id : country id from master
     * - quota : quota for the created country
     * - ksm_reference_number : KSM reference number
     * 
     * @return array|bool Returns array | bool with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "ksmQuotaError": A boolean returns true if the ksm reference number is not in his beloning appliation
     *  - A boolean indicating if the post arrival details was successfully updated.
     */
    public function create($request)
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($request[self::REQUEST_APPLICATION_ID]);

        if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' =>true
            ];
        }

        $levyApproved = $this->getSumLevyApproved($request, $request[self::REQUEST_APPLICATION_ID]);

        $ksmQuota = $this->getSumQuota($request,$request[self::REQUEST_APPLICATION_ID], '');
        
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }    
        $onboardingCountry = $this->createDirectRecruitmentOnboardingCountry($request);

        $this->createOnboardingCountriesKSMReferenceNumber($request, $onboardingCountry->id);
       
        return true;
    }

    /**
     * Update the on-boarding Country on the given input request.
     *
     * @param array $request The request data containing application_id, country_id, status
     * @param $onboardingCountry The request data containing application_id, country_id, status
     * @return void Updated the on-boarding Country    .
     */
    public function updateOnboardingCountry(array $request, $onboardingCountry): void
    {
        $onboardingCountry->application_id =  $request[self::REQUEST_APPLICATION_ID] ?? $onboardingCountry->application_id;
        $onboardingCountry->country_id =  $request['country_id'] ?? $onboardingCountry->country_id;
        $onboardingCountry->status =  $request['status'] ?? $onboardingCountry->status;
        $onboardingCountry->modified_by =  $request['modified_by'] ?? $onboardingCountry->modified_by;
        $onboardingCountry->save();
    }
    /**
     * Update the on-boarding Country on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - application_id : Direct recruitment application id
     * - country_id : country id from master
     * - quota : quota for the created country
     * - ksm_reference_number : KSM reference number
     * 
     * @return array|bool Returns array | bool with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "ksmQuotaError": A boolean returns true if the ksm reference number is not in his beloning appliation
     *  - A boolean indicating if the post arrival details was successfully updated.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingCountry = $this->checkForApplication($request['company_id'], $request['id']);

        if(is_null($onboardingCountry)) {
            return [
                'InvalidUser' =>true
            ];
        }

        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('onboarding_country_id', $onboardingCountry->id)
                                ->first();
        if(!empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }

        $this->updateOnboardingCountry($request, $onboardingCountry);
        
        return true;
    }
     /**
     * List the on Boarding Country on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - application_id : Direct recruitment application id
     * - country_id : country id from master
     * - quota : quota for the created country
     * - ksm_reference_number : KSM reference number
     * 
     * @return array|bool Returns array | bool with the following keys:
     *  - "InvalidUser": A boolean returns true if user is invalid.
     *  - "ksmQuotaError": A boolean returns true if the ksm reference number is not in his beloning appliation
     *  - A boolean indicating true if the on Boarding Country was successfully updated.
     */  
    public function ksmReferenceNumberList($request): mixed
    {
        $applicationCheck = $this->directrecruitmentApplications->find($request[self::REQUEST_APPLICATION_ID]);

        if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' =>true
            ];
        }
        $ksmReferenceNumbers = $this->directRecruitmentApplicationApproval
                                    ->leftJoin('levy', 'levy.new_ksm_reference_number', 'directrecruitment_application_approval.ksm_reference_number')
                                    ->where('directrecruitment_application_approval.application_id', $request[self::REQUEST_APPLICATION_ID])
                                    ->whereIn('levy.status', Config::get('services.APPLICATION_LEVY_KSM_REFERENCE_STATUS'))
                                    ->select('directrecruitment_application_approval.id','directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota', 'directrecruitment_application_approval.valid_until')
                                    ->orderBy('directrecruitment_application_approval.created_at','DESC')
                                    ->get()->toArray();
                        
        $utilisedQuota = $this->workers
        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        ->whereNotIn('workers.directrecruitment_status', Config::get('services.NOT_UTILISED_STATUS_TYPE'))
        ->where('directrecruitment_workers.application_id', $request[self::REQUEST_APPLICATION_ID])
        ->select('worker_visa.ksm_reference_number', DB::raw('COUNT(workers.id) as utilised_quota')/*, DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id')*/)
        ->groupBy('worker_visa.ksm_reference_number')
        ->orderBy('worker_visa.ksm_reference_number','DESC')
        ->get()->toArray();

        foreach($ksmReferenceNumbers as $key => $value) {
            $ksmReferenceNumbers[$key]['utilised_quota'] = self::DEFAULT_INT_VALUE;
            foreach($utilisedQuota as $utilisedKey => $utilisedValue) {
                if($utilisedQuota[$utilisedKey][self::REQUEST_KSM_REFERENCE_NUMBER] == $ksmReferenceNumbers[$key][self::REQUEST_KSM_REFERENCE_NUMBER]) {
                    $ksmReferenceNumbers[$key]['utilised_quota'] = $utilisedQuota[$utilisedKey]['utilised_quota'];
                    // $ksmReferenceNumbers[$key]['worker_id'] = $utilisedQuota[$utilisedKey]['workers_id'];
                }
            }
        }
        return $ksmReferenceNumbers;
    }

    /**
     * Update the on-boarding Country status
     *
     * @param array $request The request data to create the on Boarding Country.
     * - country_id : country id from master
     * - onboarding_status : on boarding status for the created country
     * 
     * @return bool Returns array | bool with the following keys:
     *  - A boolean indicating true if the on Boarding Country status was successfully updated.
     */
    public function onboarding_status_update($request): bool
    {
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->find($request['country_id']);
        $onboardingCountry->onboarding_status =  $request['onboarding_status'];
        $onboardingCountry->save();
        return true;
    }
    /**
     * List the Dropdown values of KSM reference dropdown from onBoarding Application.
     *
     * @param array $request The request data containing company id,  company id
     * @return mixed Returns Dropdown values of KSM reference dropdown from onBoarding Country.
     * - "InvalidUser": A boolean returns true if user is invalid.
     */
    public function ksmDropDownForOnboarding($request): mixed
    {
        $applicationCheck = $this->directrecruitmentApplications->find($request[self::REQUEST_APPLICATION_ID]);
        if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' =>true
            ];
        }
        return $this->directRecruitmentApplicationApproval->where('application_id', $request[self::REQUEST_APPLICATION_ID])
        ->select('id', 'ksm_reference_number')
        ->orderBy('created_at','DESC')
        ->get(); 
    }
    /**
     * Update the quota for the ksm reference number on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - id : Direct recruitment onBoarding Countries KSM Reference number id
     * - quota : quota for the created country
     * - ksm_reference_number : KSM reference number
     * 
     * @return bool|array Returns array | bool with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "editError": A boolean returns true if user is invalid to edis the record.
     *  - "ksmNumberError": A boolean returns true if KSM Reference number alreaddy added for the country
     *  - "ksmQuotaError": A boolean returns true if the ksm reference number is not in his beloning appliation
     *  - A boolean returns true indicating, if quota for the ksm reference number was successfully updated.
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
        if(empty($ksmDetails)) {
            return [
                'dataError' => true
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($ksmDetails->application_id);
        if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' =>true
            ];
        }
        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                                ->where('ksm_reference_number', $ksmDetails->ksm_reference_number)
                                ->first();
        if(!empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }

        $checkKSM = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetails->application_id)
                            ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                            ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
                            ->where('id', '<>', $request['id'])
                            ->first();
                            
        if(!empty($checkKSM)) {
            return [
                'ksmNumberError' => true
            ];
        }

        $levyApproved = $this->getSumLevyApproved($request, $ksmDetails->application_id);
                
        $ksmQuota = $this->getSumQuota($request,$ksmDetails->application_id, $request['id']);
        
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }
        
        $ksmDetails->ksm_reference_number =  $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? $ksmDetails->ksm_reference_number;
        $ksmDetails->quota =  $request['quota'] ?? $ksmDetails->quota;
        $ksmDetails->modified_by =  $request['modified_by'] ?? $ksmDetails->modified_by;
        $ksmDetails->save();

        $this->saveOnboardingCountryDetails($ksmDetails, $request['quota']);
        
        return true;
    }
    /**
     * Update the quota in onboarding Country 
     *
     * @param object $ksmDetails The request data to update the quota in onboarding Country .
     * - quota : updated quota value
     * - onboarding_country_id : onboarding country id from master
     * 
     * @return void Returns void - Update the quota in onboarding Country 
     * 
     */
    public function saveOnboardingCountryDetails(object $ksmDetails, $quota = 0): void
    {
        $currentQuota = self::DEFAULT_INT_VALUE;
        $oldKSMQuota = $ksmDetails->quota;
        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($ksmDetails->onboarding_country_id);
        $currentQuota = $onboardingCountryDetails->quota - $oldKSMQuota;
        $onboardingCountryDetails->quota = $currentQuota + $quota;
        $onboardingCountryDetails->save();
    }

    /**
     * Dalete the ksm reference number on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - id : Direct recruitment onBoarding Countries KSM Reference number id
     * - company_id
     * 
     * @return bool|array Returns array | bool with the following keys:
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "dataError" : A boolean returns true if data not found.
     *  - "editError": A boolean returns true if user is invalid to edis the record.
     *  - A boolean returns true indicating, if the KSM Reference number deleted successfully .
     */
    public function deleteKSM($request): bool|array
    {
        $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->find($request['id']);
        if(empty($ksmDetails)) {
            return [
                'dataError' => true
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($ksmDetails->application_id);
        if($applicationCheck->company_id != $request[self::REQUEST_COMPANY_ID]) {
            return [
                'InvalidUser' =>true
            ];
        }
        
        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('application_id', $ksmDetails->application_id)
                                ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                                ->where('ksm_reference_number', $ksmDetails->ksm_reference_number)
                                ->first(['status']);
        if(!empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }
        $this->saveOnboardingCountryDetails($ksmDetails, 0);
        $ksmDetails->delete();
        return true;
    }
    /**
     * Create a new onboarding Countries KSM Reference Number .
     *
     * @param array $request The data used to create the onboarding Countries KSM Reference Number.
     * The array should contain the following keys:
     * 
     * - application_id: on boarding attestation id
     * - onboarding_country_id: Dispath date
     * - ksm_reference_number: Dispath time
     * - quota: employee id
     * - status: from user details
     * - created_by: The user who created the Dispatch.
     * - modified_by: The user who modified the Dispatch.
     *
     * @param array $checkCountry consists of id, application_id.
     * 
     * @return void onboarding Countries KSM Reference Number .
     * 
     */
    public function addOnboardingCountriesKSMReferenceNumber(array $request, object $checkCountry):void
    {
        $this->onboardingCountriesKSMReferenceNumber->create([
            'application_id' => $checkCountry->application_id ?? self::DEFAULT_INT_VALUE,
            'onboarding_country_id' => $checkCountry->id,
            'ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? '',
            'quota' => $request['quota'] ?? self::DEFAULT_INT_VALUE,
            'utilised_quota' => $request['utilised_quota'] ?? self::DEFAULT_INT_VALUE,
            'status' => $request['status'] ?? self::STATUS_ACTIVE,
            'created_by' => $request['modified_by'] ?? self::DEFAULT_INT_VALUE,
            'modified_by' => $request['modified_by'] ?? self::DEFAULT_INT_VALUE
        ]);
    }
    /**
     * Create the ksm reference number on the given input request.
     *
     * @param array $request The request data to create the on Boarding Country.
     * - onboarding_country_id: Direct recruitment onBoarding Country id
     * - ksm_reference_number: Direct recruitment onBoarding Countries KSM Reference number
     * - quota: Quota added
     * 
     * @return bool|array Returns array | bool with the following keys:
     *  - "error": A boolean returns true if validation fails.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "ksmNumberError" : A boolean returns true if data not found.
     *  - "ksmQuotaError": A boolean returns true if user is invalid to edis the record.
     *  - A boolean returns true indicating, if the KSM Reference number added successfully .
     */
    public function addKSM($request): bool|array
    {
        $validator = Validator::make($request, $this->addKSMValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $checkCountry = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);

        if(is_null($checkCountry)) {
            return [
                'InvalidUser' => true
            ];
        } else if(!is_null($checkCountry)) {
            $checkKSM = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $checkCountry->application_id)
                            ->where('onboarding_country_id', $checkCountry->id)
                            ->where('ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER])
                            ->first();
            if(!empty($checkKSM)) {
                return [
                    'ksmNumberError' => true
                ];
            }
        }

        $levyApproved = $this->getSumLevyApproved($request, $checkCountry->application_id);

        $ksmQuota = $this->getSumQuota($request,$checkCountry->application_id, '');

        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }  
        
        $this->addOnboardingCountriesKSMReferenceNumber($request, $checkCountry);

        $checkCountry->quota = $checkCountry->quota + $request['quota'];
        $checkCountry->save();
        return true;
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
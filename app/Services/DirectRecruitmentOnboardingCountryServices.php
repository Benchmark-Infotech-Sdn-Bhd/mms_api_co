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
    public const REQUEST_ONBOARDING_ATTESTATION_ID = 'onboarding_attestation_id';

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

        if(isset($request['search_param']) && !empty($request['search_param'])){
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
                if (isset($request['search_param']) && !empty($request['search_param'])) {
                    $query->where('countries.country_name', 'like', "%{$request['search_param']}%");
                }
            })
            ->select('directrecruitment_onboarding_countries.id', 'countries.country_name as country', 'countries.system_type as system', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.name as onboarding_status_name')
            ->orderBy('directrecruitment_onboarding_countries.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
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
            ->select('directrecruitment_onboarding_countries.*', 'directrecruitment_onboarding_status.name as onboarding_status_name')->find($request['id']);
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

        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' =>true
            ];
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
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->create([
            'application_id' => $request['application_id'] ?? 0,
            'country_id' => $request['country_id'] ?? 0,
            'quota' => $request['quota'] ?? 0,
            'utilised_quota' => $request['utilised_quota'] ?? 0,
            'status' => $request['status'] ?? 1,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        $this->onboardingCountriesKSMReferenceNumber->create([
            'application_id' => $request['application_id'] ?? 0,
            'onboarding_country_id' => $onboardingCountry->id,
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'quota' => $request['quota'] ?? 0,
            'utilised_quota' => $request['utilised_quota'] ?? 0,
            'status' => $request['status'] ?? 1,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
       
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
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->join('directrecruitment_applications', function ($join) use($request) {
                            $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                                ->where('directrecruitment_applications.company_id', $request['company_id']);
                            })->find($request['id']);
        if(is_null($onboardingCountry)) {
            return [
                'InvalidUser' =>true
            ];
        }
        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('onboarding_country_id', $onboardingCountry->id)
                                ->first();
        if(isset($agentDetails) && !empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }
        $onboardingCountry->application_id =  $request['application_id'] ?? $onboardingCountry->application_id;
        $onboardingCountry->country_id =  $request['country_id'] ?? $onboardingCountry->country_id;
        $onboardingCountry->status =  $request['status'] ?? $onboardingCountry->status;
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
        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' =>true
            ];
        }
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
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->find($request['country_id']);
        $onboardingCountry->onboarding_status =  $request['onboarding_status'];
        $onboardingCountry->save();
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function ksmDropDownForOnboarding($request): mixed
    {
        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' =>true
            ];
        }
        return $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])
        ->select('id', 'ksm_reference_number')
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
        $applicationCheck = $this->directrecruitmentApplications->find($ksmDetails->application_id);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' =>true
            ];
        }
        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                                ->where('ksm_reference_number', $ksmDetails->ksm_reference_number)
                                ->first();
        if(isset($agentDetails) && !empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }

        $checkKSM = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetails->application_id)
                            ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->where('id', '<>', $request['id'])
                            ->first();
        if(!empty($checkKSM)) {
            return [
                'ksmNumberError' => true
            ];
        }

        $levyApproved = $this->levy->where('application_id', $ksmDetails->application_id)
                        ->where('new_ksm_reference_number', $request['ksm_reference_number'])
                        ->where('status', 'Paid')->sum('approved_quota');
                
        $ksmQuota = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetails->application_id)
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->where('id', '<>', $request['id'])
                            ->sum('quota');
        
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }
        
        $currentQuota = 0;
        $oldKSMQuota = $ksmDetails->quota;
        $ksmDetails->ksm_reference_number =  $request['ksm_reference_number'] ?? $ksmDetails->ksm_reference_number;
        $ksmDetails->quota =  $request['quota'] ?? $ksmDetails->quota;
        $ksmDetails->modified_by =  $request['modified_by'] ?? $ksmDetails->modified_by;
        $ksmDetails->save();
        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($ksmDetails->onboarding_country_id);
        $currentQuota = $onboardingCountryDetails->quota - $oldKSMQuota;
        $onboardingCountryDetails->quota = $currentQuota + $request['quota'];
        $onboardingCountryDetails->save();
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function deleteKSM($request): bool|array
    {
        $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->find($request['id']);
        $applicationCheck = $this->directrecruitmentApplications->find($ksmDetails->application_id);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' =>true
            ];
        }
        if(empty($ksmDetails)) {
            return [
                'dataError' => true
            ];
        }
        $agentDetails = $this->directRecruitmentOnboardingAgent
                                ->where('application_id', $ksmDetails->application_id)
                                ->where('onboarding_country_id', $ksmDetails->onboarding_country_id)
                                ->where('ksm_reference_number', $ksmDetails->ksm_reference_number)
                                ->first(['status']);
        if(isset($agentDetails) && !empty($agentDetails)) {
            return [
                'editError' => true
            ];
        }
        $currentQuota = 0;
        $oldKSMQuota = $ksmDetails->quota;
        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($ksmDetails->onboarding_country_id);
        $currentQuota = $onboardingCountryDetails->quota - $oldKSMQuota;
        $onboardingCountryDetails->quota = $currentQuota;
        $onboardingCountryDetails->save();
        $ksmDetails->delete();
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function addKSM($request): bool|array
    {
        $validator = Validator::make($request, $this->addKSMValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $checkCountry = $this->directRecruitmentOnboardingCountry->join('directrecruitment_applications', function ($join) use($request) {
                            $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                                ->where('directrecruitment_applications.company_id', $request['company_id']);
                            })->find($request['onboarding_country_id']);
        if(is_null($checkCountry)) {
            return [
                'InvalidUser' => true
            ];
        } else if(!is_null($checkCountry)) {
            $checkKSM = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $checkCountry->application_id)
                            ->where('onboarding_country_id', $checkCountry->id)
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->first();
            if(!empty($checkKSM)) {
                return [
                    'ksmNumberError' => true
                ];
            }
        }
        $levyApproved = $this->levy->where('application_id', $checkCountry->application_id)
                        ->where('new_ksm_reference_number', $request['ksm_reference_number'])
                        ->where('status', 'Paid')->sum('approved_quota');
                        
        $ksmQuota = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $checkCountry->application_id)
                            ->where('ksm_reference_number', $request['ksm_reference_number'])
                            ->sum('quota');
        if($levyApproved < ($ksmQuota + $request['quota'])) {
            return [
                'ksmQuotaError' => true
            ];
        }  
        $this->onboardingCountriesKSMReferenceNumber->create([
            'application_id' => $checkCountry->application_id ?? 0,
            'onboarding_country_id' => $checkCountry->id,
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'quota' => $request['quota'] ?? 0,
            'utilised_quota' => $request['utilised_quota'] ?? 0,
            'status' => $request['status'] ?? 1,
            'created_by' => $request['modified_by'] ?? 0,
            'modified_by' => $request['modified_by'] ?? 0
        ]);
        $checkCountry->quota = $checkCountry->quota + $request['quota'];
        $checkCountry->save();
        return true;
    }
}
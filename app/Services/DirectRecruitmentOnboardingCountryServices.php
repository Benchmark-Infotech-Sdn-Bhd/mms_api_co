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
     * DirectRecruitmentOnboardingCountryServices constructor.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;
     * @param ApplicationInterviews $applicationInterviews
     * @param ValidationServices $validationServices;
     * @param OnboardingAttestation $onboardingAttestation;
     * @param Workers $workers
     * @param Levy $levy
     */
    public function __construct(DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval, ApplicationInterviews $applicationInterviews, ValidationServices $validationServices, OnboardingAttestation $onboardingAttestation, Workers $workers, Levy $levy)
    {
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->applicationInterviews = $applicationInterviews;
        $this->validationServices = $validationServices;
        $this->onboardingAttestation = $onboardingAttestation;
        $this->workers = $workers;
        $this->levy = $levy;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'country_id' => 'required',
            'quota' => 'required|regex:/^[0-9]+$/|max:3'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'country_id' => 'required',
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
        
        return $this->directRecruitmentOnboardingCountry->leftJoin('countries', 'countries.id', 'directrecruitment_onboarding_countries.country_id')
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
        return $this->directRecruitmentOnboardingCountry->leftJoin('directrecruitment_onboarding_status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_status.id')->select('directrecruitment_onboarding_countries.*', 'directrecruitment_onboarding_status.name as onboarding_status_name')->find($request['id']);
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
        $interviewApproved = $this->applicationInterviews->where('application_id', $request['application_id'])
                        ->where('status', 'Approved')->sum('approved_quota');
        $countriesQuota = $this->directRecruitmentOnboardingCountry->where('application_id', $request['application_id'])
                            ->sum('quota');
        $countriesQuota += $request['quota'];
        if($countriesQuota > $interviewApproved) {
            return [
                'quotaError' => true
            ];
        }
        $this->directRecruitmentOnboardingCountry->create([
            'application_id' => $request['application_id'] ?? 0,
            'country_id' => $request['country_id'] ?? 0,
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
        $onboardingCountry = $this->directRecruitmentOnboardingCountry->findOrFail($request['id']);
        $interviewApproved = $this->applicationInterviews->where('application_id', $onboardingCountry->application_id)
                        ->where('status', 'Approved')
                        ->sum('approved_quota');
        $countriesQuota = $this->directRecruitmentOnboardingCountry
                            ->where('application_id', $onboardingCountry->application_id)
                            ->whereNot(function ($query) use ($request){
                                $query->where('id', $request['id']);
                            })
                            ->sum('quota'); 
        $countriesQuota += $request['quota'];
        if($countriesQuota > $interviewApproved) {
            return [
                'quotaError' => true
            ];
        }
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
        $onboardingCountry->quota =  $request['quota'] ?? $onboardingCountry->quota;
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
        // return $this->directRecruitmentApplicationApproval
        // ->leftJoin('levy', function($join) use ($request){
        //     $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
        //     ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
        //     })
        // ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_application_approval.application_id')
        // ->where('directrecruitment_application_approval.application_id', $request['application_id'])
        // ->select('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
        // ->selectRaw('sum(directrecruitment_onboarding_countries.utilised_quota) as utilised_quota')
        // ->groupBy('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
        // ->distinct()
        // ->get();

        // return $this->directRecruitmentApplicationApproval
        // ->leftJoin('levy', function($join) use ($request){
        //     $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
        //     ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
        //     })
        // ->leftJoin('directrecruitment_workers', 'directrecruitment_workers.application_id', 'directrecruitment_application_approval.application_id')
        // ->leftJoin('workers', function ($join) {
        //     $join->on('workers.id', '=', 'directrecruitment_workers.worker_id')
        //          ->where('workers.directrecruitment_status', 'Processed');
        // })
        // // ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
        // ->where('directrecruitment_application_approval.application_id', $request['application_id'])
        // ->select('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota', DB::raw('COUNT(workers.id) as utilised_quota'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
        // // ->selectRaw('count(workers.id) as utilised_quota')
        // ->groupBy('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
        // ->distinct()
        // ->get();

        // return $this->workers->leftJoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', 'workers.id')
        //     ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_workers.application_id')
        //     ->leftJoin('levy', function($join) use ($request) {
        //             $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
        //             ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
        //             })
        //     ->where('workers.directrecruitment_status', 'Processed')
        //     ->where('directrecruitment_application_approval.application_id', $request['application_id'])
        //     ->select('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota', DB::raw('COUNT(workers.id) as utilised_quota'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
        //     ->groupBy('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
        //     ->distinct()
        //     ->get();

        $ksmReferenceNumbers = $this->levy->where('application_id', $request['application_id'])
                            ->whereIn('status', Config::get('services.APPLICATION_LEVY_KSM_REFERENCE_STATUS'))
                            ->select('id','new_ksm_reference_number as ksm_reference_number', 'approved_quota')
                            ->orderBy('created_at','DESC')
                            ->get();
// return $ksmReferenceNumbers;
        return $this->workers
        ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
        ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
        // ->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.ksm_reference_number', 'worker_visa.ksm_reference_number')
        // ->leftJoin('levy', 'levy.application_id', 'directrecruitment_application_approval.application_id')
        // ->leftJoin('levy', function($join) use ($request) {
        //                 $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
        //                 ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
        //                 })
        ->where('workers.directrecruitment_status', 'Processed')
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->select('worker_visa.ksm_reference_number', DB::raw('COUNT(workers.id) as utilised_quota'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
        ->groupBy('worker_visa.ksm_reference_number')
        ->orderBy('worker_visa.ksm_reference_number','DESC')
        ->get();
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
}
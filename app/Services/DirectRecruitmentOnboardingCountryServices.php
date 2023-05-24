<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentOnboardingCountryServices
{
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * DirectRecruitmentOnboardingCountryServices constructor.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
     */
    public function __construct(DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
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
        return $this->directRecruitmentOnboardingCountry->leftJoin('countries', 'countries.id', 'directrecruitment_onboarding_countries.country_id')
            ->where('directrecruitment_onboarding_countries.application_id', $request['application_id'])
            ->select('directrecruitment_onboarding_countries.id', 'countries.country_name as country', 'countries.system_type as system', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota')
            ->orderBy('directrecruitment_onboarding_countries.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->directRecruitmentOnboardingCountry->find($request['id']);
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
        return $this->directRecruitmentOnboardingCountry->leftJoin('directrecruitment_application_approval', 'directrecruitment_application_approval.application_id', 'directrecruitment_onboarding_countries.application_id')
            ->where('directrecruitment_onboarding_countries.application_id', $request['application_id'])
            ->select('directrecruitment_onboarding_countries.id', 'directrecruitment_application_approval.ksm_reference_number', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota')
            ->get();
    }
}
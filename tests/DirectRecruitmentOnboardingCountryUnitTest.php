<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentOnboardingCountryUnitTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }
    /**
     * Functional test for Onboarding Application Id mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingApplicationIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Country Id mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country_id' => ['The country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Quota size validation 
     * 
     * @return void
     */
    public function testForOnboardingQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding country creation 
     * 
     * @return void
     */
    public function testForOnboardingCountryCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Country Added Successfully']
        ]);
    }
    /**
     * Functional test for Update Id mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', array_merge($this->UpdationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Country Id mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', array_merge($this->UpdationData(), ['country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'country_id' => ['The country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', array_merge($this->UpdationData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Quota size validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', array_merge($this->UpdationData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Country
     * 
     * @return void
     */
    public function testForUpdateOnboardingCountry(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Country Updated Successfully']
        ]);
    }
    /**
     * Functional test for View Onboarding Country
     * 
     * @return void
     */
    public function testForViewOnboardingCountry(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'id',
                    'application_id',
                    'country_id',
                    'quota',
                    'utilised_quota',
                    'status',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
    /**
     * Functional test for List Onboarding Countries
     * 
     * @return void
     */
    public function testForListOnboardingCountries(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/list', ['application_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
        ]);
    }
    /**
     * Functional test for List KSM Reference Number with Quota
     * 
     * @return void
     */
    public function testForViewKSMReferenecNumberWithQuota(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmReferenceNumberList', ['application_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * @return void
     */
    public function creationSeeder(): void
    {
        $this->artisan("db:seed --class=ServiceSeeder");
        $this->artisan("db:seed --class=SystemTypeSeeder");
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
        ];   
        $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());

        $payload =  [
            'name' => 'HR'
        ];
        $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
       
        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'), 
            'ic_number' => 222223434, 
            'passport_number' => 'ADI', 
            'email' => 'test@gmail.com', 
            'contact_number' => 238467,
            'address' => 'Addres', 
            'postcode' => 2344, 
            'position' => 'Position', 
            'branch_id' => 1,
            'role_id' => 1, 
            'salary' => 67.00, 
            'status' => 1, 
            'city' => 'ABC', 
            'state' => 'Malaysia'
        ];
        $this->json('POST', 'api/v1/employee/create', $payload, $this->getHeader(false));

        $payload =  [
            'sector_name' => 'Agriculture',
            'sub_sector_name' => 'Agriculture'
        ];  
        $this->json('POST', 'api/v1/sector/create', $payload, $this->getHeader(false));

        $payload = [
            'company_name' => 'ABC Firm', 
            'contract_type' => 'Zero Cost', 
            'roc_number' => 'APS6376', 
            'director_or_owner' => 'Test', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'address' => 'Coimbatore', 
            'pic_name' => 'PICTest', 
            'pic_contact_number' => '764859694', 
            'pic_designation' => 'Manager', 
            'registered_by' => 1, 
            'sector_type' => 1, 
            'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])
        ];
        $res = $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'crm_prospect_id' => 1, 
            'quota_applied' => 100, 
            'person_incharge' => 'test', 
            'cost_quoted' => 10.22, 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecrutment/submitProposal', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'application_id' => 1, 
            'item_name' => 'Document Checklist', 
            'application_checklist_status' => 'Completed', 
            'remarks' => 'test', 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'submission_date' => Carbon::now()->format('Y-m-d'), 
            'applied_quota' => 50, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 50, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => Carbon::now()->format('Y-m-d'), 
            'payment_amount' => 10.87, 
            'approved_quota' => 50, 
            'ksm_reference_number' => 'My/643/7684548', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648000', 
            'remarks' => 'test create'
        ];
        $this->json('POST', 'api/v1/levy/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'country_id' => 1, 'quota' => 15];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return ['id' => 1, 'country_id' => 1, 'quota' => 20];
    }
}

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
     * Functional test for ksm reference number mandatory field validation 
     * 
     * @return void
     */
    public function testForKSMReferenceNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
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
     * Functional test for quota validation 
     * 
     * @return void
     */
    public function testForQuotaValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', array_merge($this->creationData(), ['quota' => 52]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The number of quota cannot exceed the Approved KSM Quota']
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
     * Functional test for Update Onboarding Country edit validation
     * 
     * @return void
     */
    public function testForUpdateOnboardingCountryEditValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->agentData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'An Agent has been assigned to this record; users cannot edit the records']
        ]);
    }
    /**
     * Functional test for add KSM onboarding country id mandatory field validation 
     * 
     * @return void
     */
    public function testForAddKSMOnboardingCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['onboarding_country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_country_id' => ['The onboarding country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for add KSM Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForAddKSMQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for add KSM Quota size validation 
     * 
     * @return void
     */
    public function testForAddKSMQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for add KSM ksm_reference_number mandatory field validation 
     * 
     * @return void
     */
    public function testForAddKSMReferenceNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for add ksm quota validation 
     * 
     * @return void
     */
    public function testForAddKSMQuotaValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['quota' => 100]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The number of quota cannot exceed the Approved KSM Quota']
        ]);
    }
    /**
     * Functional test for Add KSM already Exists validation
     * 
     * @return void
     */
    public function testForAddKSMKSMNumberValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', array_merge($this->addKSMData(), ['ksm_reference_number' => 'My/992/095648000']), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The KSM Reference Number for this Country Has Been Added Already']
        ]);
    }
    /**
     * Functional test for Add KSM
     * 
     * @return void
     */
    public function testForAddKSM(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', $this->addKSMData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'KSM Refrence Number Added Successfully']
        ]);
    }
    /**
     * Functional test for KSM Quota Update Id mandatory field validation 
     * 
     * @return void
     */
    public function testForKSMQuotaUpdateIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', array_merge($this->ksmQuotaUpdationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for KSM Quota Update Onboarding Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForKSMQuotaUpdateOnboardingQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', array_merge($this->ksmQuotaUpdationData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for  KSM Quota Update Onboarding Quota size validation 
     * 
     * @return void
     */
    public function testForKSMQuotaUpdateOnboardingQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', array_merge($this->ksmQuotaUpdationData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for KSM Quota Update KSM reference number mandatory field validation 
     * 
     * @return void
     */
    public function testForKSMQuotaUpdateKSMReferenceNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', array_merge($this->ksmQuotaUpdationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for ksm quota validation 
     * 
     * @return void
     */
    public function testForKSMQuotaValidation(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', array_merge($this->ksmQuotaUpdationData(), ['quota' => 520]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The number of quota cannot exceed the Approved KSM Quota']
        ]);
    }
    /**
     * Functional test for KSM already Exists validation
     * 
     * @return void
     */
    public function testForUpdateKSMNumberValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/addKSM', $this->addKSMData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', ['id' => 2, 'ksm_reference_number' => 'My/992/095648000', 'quota' => 20], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The KSM Reference Number for this Country Has Been Added Already']
        ]);
    }
    /**
     * Functional test for KSM Quota Update
     * 
     * @return void
     */
    public function testForKSMQuotaUpdate(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', $this->ksmQuotaUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Quota Updated Successfully']
        ]);
    }
    /**
     * Functional test for Update KSM Quota edit validation
     * 
     * @return void
     */
    public function testForUpdateKSMQuotaEditValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->agentData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmQuotaUpdate', $this->ksmQuotaUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'An Agent has been assigned to this record; users cannot edit the records']
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
     * Functional test for List KSM Reference Number dropdown for onboarding
     * 
     * @return void
     */
    public function testForViewKSMReferenecNumberDropdownForOnboarding(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/ksmDropDownForOnboarding', ['application_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test for delete KSM
     * 
     * @return void
     */
    public function testForDeleteKSM(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/deleteKSM', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $response->seeJson([
            'data' => ['message' => 'Record Deleted Successfully']
        ]);
    }
    /**
     * Functional test for delete KSm delete validation
     * 
     * @return void
     */
    public function testForDeleteValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/deleteKSM', ['id' => 5], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $response->seeJson([
            'data' => ['message' => 'Data Not Found']
        ]);
    }
    /**
     * Functional test for KSM delete Restriction
     * 
     * @return void
     */
    public function testForDeleteRestrictionValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->agentData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/deleteKSM', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'An Agent has been assigned to this record; users cannot edit the records']
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
            'name' => 'HR',
            'special_permission' => 0
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
            'state' => 'Malaysia',
            'subsidiary_companies' => []
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
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

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
            "agent_name" => 'ABC', 
            "country_id" => 1, 
            "city" => 'CBE', 
            "person_in_charge" => 'ABC',
            "pic_contact_number" => '9823477867', 
            "email_address" => 'test@gmail.com', 
            "company_address" => 'Test'
        ];
        $this->json('POST', 'api/v1/agent/create', $payload, $this->getHeader(false));

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
            'submission_date' => Carbon::now()->format('Y-m-d'), 
            'applied_quota' => 50, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684549', 
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
            'ksm_reference_number' => 'My/643/7684549', 
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
            'payment_date' => Carbon::now()->format('Y-m-d'), 
            'payment_amount' => 10.87, 
            'approved_quota' => 50, 
            'ksm_reference_number' => 'My/643/7684549', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648001', 
            'remarks' => 'test create'
        ];
        $this->json('POST', 'api/v1/levy/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/992/095648000', 
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/992/095648001', 
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
        return ['application_id' => 1, 'country_id' => 1, 'ksm_reference_number' => 'My/992/095648000', 'quota' => 25];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return ['id' => 1, 'country_id' => 1];
    }
    /**
     * @return array
     */
    public function ksmQuotaUpdationData(): array
    {
        return ['id' => 1, 'onboarding_country_id' => 1, 'ksm_reference_number' => 'My/992/095648000', 'quota' => 50];
    }
    /**
     * @return array
     */
    public function agentData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1, 'quota' => 10, 'ksm_reference_number' => 'My/992/095648000'];
    }
    /**
     * @return array
     */
    public function addKSMData(): array
    {
        return ['onboarding_country_id' => 1, 'ksm_reference_number' => 'My/992/095648001', 'quota' => 10];
    }
}

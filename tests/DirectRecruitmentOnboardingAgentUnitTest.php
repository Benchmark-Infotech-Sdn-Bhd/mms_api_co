<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentOnboardingAgentUnitTest extends TestCase
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
     * Functional test for Onboarding Agent Application Id mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentApplicationIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Agent Country Id mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['onboarding_country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_country_id' => ['The onboarding country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Agent Id mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['agent_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'agent_id' => ['The agent id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Agent ksm mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentKSMRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Agent Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Agent Quota size validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Onboarding Agent quota validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentQuotaValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', array_merge($this->creationData(), ['quota' => 50]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The number of quota cannot exceed the Country Quota']
        ]);
    }
    /**
     * Functional test for Onboarding Agent validation 
     * 
     * @return void
     */
    public function testForOnboardingAgentValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false)); 
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The Agent already added for this Country and KSM Reference Number']
        ]);
    }
    /**
     * Functional test for Onboarding Agent creation 
     * 
     * @return void
     */
    public function testForOnboardingAgentCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Agent Added Successfully']
        ]);
    }
    /**
     * Functional test for Update Id mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent Agent Id mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentAgentIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['agent_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'agent_id' => ['The agent id field is required.']
            ]
        ]);
    }
     /**
     * Functional test for Update Onboarding Agent ksm mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentKSMRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent Quota size validation 
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota' => ['The quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent Quota Validation
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentQuotaValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', array_merge($this->UpdationData(), ['quota' => 100]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'The number of quota cannot exceed the Country Quota']
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent Edit validation
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgentEditValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/update', $this->attestationUpdateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Attestation submission has been processed for this record, users are not allowed to modify the records.']
        ]);
    }
    /**
     * Functional test for Update Onboarding Agent
     * 
     * @return void
     */
    public function testForUpdateOnboardingAgent(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Agent Updated Successfully']
        ]);
    }
    /**
     * Functional test for View Onboarding Agent
     * 
     * @return void
     */
    public function testForViewOnboardingAgent(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'id',
                    'application_id',
                    'onboarding_country_id',
                    'agent_id',
                    'quota',
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
     * Functional test for List Onboarding Agent
     * 
     * @return void
     */
    public function testForListOnboardingAgent(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/list', ['application_id' => 1, 'onboarding_country_id' => 1], $this->getHeader(false));
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
     * Functional test for ksm refernce number dropdown based on onboarding country
     * 
     * @return void
     */
    public function testForDropDownKSM(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/ksmDropDownBasedOnOnboarding', ['onboarding_country_id' => 1], $this->getHeader(false));
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
            'applied_quota' => 25, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 25, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => Carbon::now()->format('Y-m-d'), 
            'payment_amount' => 10.87, 
            'approved_quota' => 25, 
            'ksm_reference_number' => 'My/643/7684548', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648000', 
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
            'country_id' => 1, 
            'ksm_reference_number' => 'My/992/095648000', 
            'valid_until' => Carbon::now()->format('Y-m-d'), 
            'quota' => 25
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1, 'quota' => 10, 'ksm_reference_number' => 'My/992/095648000'];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1, 'quota' => 15, 'ksm_reference_number' => 'My/992/095648000'];
    }
    /**
     * @return array
     */
    public function attestationUpdateData(): array
    {
        return [
            "id" => 1,
            "ksm_reference_number" => "My/992/095648000",
            "submission_date" => Carbon::now()->format('Y-m-d'),
            "collection_date" => Carbon::now()->format('Y-m-d'),
            "file_url" => "google.com",
            "remarks" => "remarks testing"
        ];
    }
}

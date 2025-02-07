<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class CallingVisaApprovalUnitTest extends TestCase
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
     * Functional test for approval calling visa, visa generated date mandatory field validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalVisaGeneratedDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', array_merge($this->creationData(), ['calling_visa_generated' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'calling_visa_generated' => ['The calling visa generated field is required.']
            ]
        ]);
    }
    /**
     * Functional test for approval calling visa, valid until date mandatory field validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalCallingVisaValidUntilDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', array_merge($this->creationData(), ['calling_visa_valid_until' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'calling_visa_valid_until' => ['The calling visa valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for approval calling visa, status mandatory field validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', array_merge($this->creationData(), ['status' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }
    /**
     * Functional test for approval calling visa, visa generated date type validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalVisaGeneratedDateTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', array_merge($this->creationData(), ['calling_visa_generated' => '2025-05-05']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'calling_visa_generated' => ['The calling visa generated must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for approval calling visa, valid until date type validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalCallingVisaValidUntilDateTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', array_merge($this->creationData(), ['calling_visa_valid_until' => '2023-05-05']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'calling_visa_valid_until' => ['The calling visa valid until must be a date after today.']
            ]
        ]);
    }
     /**
     * Functional test for approval calling visa, serach field validation 
     * 
     * @return void
     */
    public function testForCallingVisaApprovalSearchValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/workersList', array_merge($this->creationData(), ['search' => 'wo']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for approval workers list 
     * 
     * @return void
     */
    public function testForWorkersList(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1], $this->getHeader(false));
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
     * Functional test for worker list with search
     * 
     * @return void
     */
    public function testForWorkersListWithSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1, 'search' => 'Work'], $this->getHeader(false));
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
     * Functional test for show process calling visa
     * 
     * @return void
     */
    public function testForShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/show', ['worker_id' => 1], $this->getHeader(false));
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
            'date_of_birth' => '1998-11-02', 
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
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'submission_date' => '2023-05-04', 
            'applied_quota' => 100, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 100, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => '2023-05-10', 
            'payment_amount' => 10.87, 
            'approved_quota' => 100, 
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
            'received_date' => '2023-05-13', 
            'valid_until' => '2023-06-13'
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'country_id' => 1,
            'quota' => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $payload, $this->getHeader(false));
        
        $payload = [
            'agent_name' => 'ABC', 
            'country_id' => 1, 
            'city' => 'CBE', 
            'person_in_charge' => 'ABC',
            'pic_contact_number' => '9823477867', 
            'email_address' => 'test@gmail.com', 
            'company_address' => 'Test'
        ];
        $this->json('POST', 'api/v1/agent/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'agent_id' => 1,
            'quota' => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'agent_id' => 1, 'calling_visa_generated' => '2023-06-13', 'calling_visa_valid_until' => '2025-07-25', 'status' => 'Approved', 'workers' => [1], 'remarks' => 'test'];
    }
}

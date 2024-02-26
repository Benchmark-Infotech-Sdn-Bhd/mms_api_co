<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentApplicationApprovalUnitTest extends TestCase
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
     * Functional test for Create Approval Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalCreationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create Approval Received Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalCreationReceivedDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['received_date' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'received_date' => ['The received date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create Approval Valid Until Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalCreationValidUntilDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['valid_until' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'valid_until' => ['The valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create Approval KMS Referenece Number mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalCreationKMSReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create Approval Received Date Format Type validation 
     * 
     * @return void
     */
    public function testForApprovalCreationReceivedDateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['received_date' => '05-05-2023']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'received_date' => ['The received date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Create Approval Valid Until Date Format Type validation 
     * 
     * @return void
     */
    public function testForApprovalCreationValidUntilDateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', array_merge($this->creationData(), ['valid_until' => '05-05-2023']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'valid_until' => ['The valid until does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Approval Create
     * 
     * @return void
     */
    public function testForApprovalCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Approval Details Added Successfully']
        ]);
    }
    /**
     * Functional test for Update Approval ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.'],
                'ksm_reference_number' => ['The ksm reference number has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for Update Approval Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Approval Received Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationReceivedDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['received_date' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'received_date' => ['The received date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Approval Valid Until Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationValidUntilDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['valid_until' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'valid_until' => ['The valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Approval Received Date Format Type validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationReceivedDateFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['received_date' => '05-05-2023']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'received_date' => ['The received date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Update Approval Valid Until Date Format Type validation 
     * 
     * @return void
     */
    public function testForApprovalUpdationValidUntilDateFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', array_merge($this->updationData(), ['valid_until' => '05-05-2023']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'valid_until' => ['The valid until does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Approval Update
     * 
     * @return void
     */
    public function testForApprovalUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Approval Details Updated Successfully']
        ]);
    }
    /**
     * Functional test to Show Approval
     * 
     * @return void
     */
    public function testToDisplayApprovalDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
                'id', 
                'application_id', 
                'item_name', 
                'ksm_reference_number',  
                'received_date',  
                'valid_until', 
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            ]
        ]);
    }
    /**
     * Functional test to List Approval
     * 
     * @return void
     */
    public function testToListApprovalDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/list', ['application_id' => 1], $this->getHeader(false));
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
     * @return array
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
            'special_permission' => '',
            'system_role' => 0,
            'status' => 1,
            'parent_id' => 0,
            'company_id' => 1
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
            'approved_quota' => 10, 
            'ksm_reference_number' => 'My/643/7684548', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648000', 
            'remarks' => 'test create'
        ];
        $this->json('POST', 'api/v1/levy/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'ksm_reference_number' => 'My/992/095648000', 'received_date' => Carbon::now()->format('Y-m-d'), 'valid_until' => Carbon::now()->addYear()->format('Y-m-d')];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'ksm_reference_number' => 'My/992/095648000', 'received_date' => Carbon::now()->format('Y-m-d'), 'valid_until' => Carbon::now()->addYear()->format('Y-m-d')];
    }
}

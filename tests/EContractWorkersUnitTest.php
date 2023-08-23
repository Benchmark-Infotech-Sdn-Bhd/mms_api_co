<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class EContractWorkersUnitTest extends TestCase
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
     * Functional test for assign worker, work start date mandatory field validation 
     * 
     * @return void
     */
    public function testForAssignWorkerWorkStartDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['work_start_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'work_start_date' => ['The work start date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, work start date format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerWorkStartDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['work_start_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'work_start_date' => ['The work start date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, department format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerDepartmentFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['department' => 'department$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'department' => ['The department format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, sub department format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerSubDepartmentFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['sub_department' => 'SubDepartment$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sub_department' => ['The sub department format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker
     * 
     * @return void
     */
    public function testForAssignWorker(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Workers are Assigned Successfully']
        ]);
    }
    /**
     * Functional test for worker list for assign worker search validation
     * 
     * @return void
     */
    public function testForCancellationWorkersListSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/workerListForAssignWorker', ['search' => 'te'], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management, assign worker list with search
     * 
     * @return void
     */
    public function testForTotalManagementWorkerListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/workerAssign/workerListForAssignWorker', ['search' => 'tes'], $this->getHeader(false));
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

        $payload =  [
            'name' => 'VendorOne',
            'type' => 'Accommodation',
            'email_address' => 'vendorone@gmail.com',
            'contact_number' => 1234567890,
            'person_in_charge' => 'test',
            'pic_contact_number' => 1232134234,
            'address' => 'test',
            'state' => 'test',
            'city' => 'test',
            'postcode' => 45353,
            'remarks' => 'test'
        ];  
        $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader(false));

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
            'name' => 'TestWorker',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Female',
            'passport_number' => 12345678954,
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 12345678900,
            'ksm_reference_number' => '',
            'calling_visa_reference_number' => '',
            'calling_visa_valid_until' => '',
            'entry_visa_valid_until' => '',
            'work_permit_valid_until' => '',
            'bio_medical_reference_number' => 'BIO1234567',
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'Test Clinic',
            'doctor_code' => 'Doc123',
            'allocated_xray' => 'Tst1234',
            'xray_code' => 'Xray1234',
            'ig_policy_number' => '',
            'ig_policy_number_valid_until' => '',
            'hospitalization_policy_number' => '',
            'hospitalization_policy_number_valid_until' => '',
            'bank_name' => 'Bank Name',
            'account_number' => 1234556678,
            'socso_number' => 12345678,
            'crm_prospect_id' => 0
        ];
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));

        $payload = [
            'prospect_id' => 1, 
            'company_name' => 'ABC Firm', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'pic_name' => 'PICTest', 
            'sector_id' => 1, 
            'sector_name' => 'Agriculture', 
            'fomnext_quota' => 10, 
            'air_ticket_deposit' => 1.11, 
            'service_id' => 2, 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/eContract/addService', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'crm_prospect_id' => 1, 
            'quota_requested' => 10, 
            'person_incharge' => 'PICTest', 
            'cost_quoted' => 20, 
            'remarks' => 'testRemark', 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/eContract/proposalSubmit', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "test name",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10,
            "attachment" => "test.png",
            "valid_until" => Carbon::now()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/eContract/project/add', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['project_id' => 1, 'department' => 'department', 'sub_department' => 'sub department', 'work_start_date' => Carbon::now()->format('Y-m-d'), 'workers' => [1]];
    }
}

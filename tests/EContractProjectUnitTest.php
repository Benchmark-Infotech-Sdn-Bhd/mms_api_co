<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class EContractProjectUnitTest extends TestCase
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
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'name' => ['The name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationstateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['state' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationcityValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['city' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'city' => ['The city field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationaddressValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationannualleaveValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['annual_leave' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'annual_leave' => ['The annual leave field is required.']
            ]
        ]);
    }
     /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationannualleavemaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['annual_leave' => '100']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'annual_leave' => ['The annual leave must not be greater than 2 characters.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationmedicalleaveValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['medical_leave' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'medical_leave' => ['The medical leave field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationhospitalizationleaveValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['hospitalization_leave' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'hospitalization_leave' => ['The hospitalization leave field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectAddApplicationvaliduntilValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/add', array_merge($this->creationData(), ['valid_until' => '2023/10/10']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'valid_until' => ['The valid until does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for EContract Project add 
     * 
     * @return void
     */
    public function testForTotalManagementProjectAdd(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/project/add', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'E-Contract Project Added Successfully']
        ]);
    }
    /**
     * Functional test for EContract project update  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractProjectUpdateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/project/update', array_merge($this->creationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract project update
     * 
     * @return void
     */
    public function testForEContractProjectUpdate(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/project/add', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/project/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'E-Contract Project Updated Successfully']
        ]);
    }
    /**
     * Functional test for EContract project show Unauthorized validation
     * 
     * @return void
     */
    public function testForEContractProjectShowUnauthorizedValidation(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/project/add', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/project/show', ['id' => 0], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => [
                'message' => "Unauthorized"
            ]
        ]);
    }
    /**
     * Functional test for EContract project show
     * 
     * @return void
     */
    public function testForEContractProjectShow(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/project/add', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/project/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
        ]);
    }
    /**
     * Functional test for EContract project listing
     * 
     * @return void
     */
    public function testForEContractProjectListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/project/list', ['application_id' => 1, 'search' => ''], $this->getHeader(false));
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
     * Functional test for attachment delete
     * 
     * @return void
     */
    public function testForworkerattachmentDelete(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/project/add', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/project/deleteAttachment', ["attachment_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
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
        $res = $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

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
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
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
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return [
            "id" => 1,
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
    }
}

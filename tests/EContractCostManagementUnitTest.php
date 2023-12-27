<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class EContractCostManagementUnitTest extends TestCase
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
     * Functional test for EContract cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreateProjectIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', array_merge($this->creationData(), ['project_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'project_id' => ['The project id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreateTitleValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', array_merge($this->creationData(), ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreatepaymentreferencenumberValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', array_merge($this->creationData(), ['payment_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreatepaymentdateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', array_merge($this->creationData(), ['payment_date' => Carbon::now()->format('Y/m/d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreateAmountValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', array_merge($this->creationData(), ['amount' => '10000.9999']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management create 
     * 
     * @return void
     */
    public function testForEContractCostManagementCreate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'E-Contract Cost Management Created Successfully']
        ]);
    }
    /**
     * Functional test for EContract cost management update  mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractCostManagementUpdateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/costManagement/update', array_merge($this->UpdationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for EContract cost management update
     * 
     * @return void
     */
    public function testForEContractCostManagementUpdate(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/costManagement/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'E-Contract Cost Management Updated Successfully']
        ]);
    }
    /**
     * Functional test for EContract Cost management show
     * 
     * @return void
     */
    public function testForEContractCostManagementShow(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/eContract/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/costManagement/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
        ]);
    }
    /**
     * Functional test for EContract cost manangement listing
     * 
     * @return void
     */
    public function testForEContractCostManagementListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/costManagement/list', ['project_id' => 1, 'search_param' => ''], $this->getHeader(false));
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
    public function testForEContractCostManagementattachmentDelete(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/costManagement/deleteAttachment', ["id" => 1], $this->getHeader(false));
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
            'contact_number' => 1234567890,
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
        return [
            "project_id" => 1,
            "title" => "cost management testing",
            "payment_reference_number" => "EXP001",
            "payment_date" => Carbon::now()->format('Y-m-d'),
            "amount" => 100,
            "quantity" => 1,
            "attachment[]" => "test.png",
            "remarks" => "remarks testing"
        ];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return [
            "id" => 1,
            "title" => "cost management testing",
            "payment_reference_number" => "EXP001",
            "payment_date" => Carbon::now()->format('Y-m-d'),
            "amount" => 100,
            "quantity" => 1,
            "attachment[]" => "test.png",
            "remarks" => "remarks testing"
        ];
    }
}

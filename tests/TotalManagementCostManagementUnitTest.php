<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class TotalManagementCostManagementUnitTest extends TestCase
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
     * Functional test for Total Management cost management create  mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementCostManagementCreateApplicationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total Management cost management create 
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementCreate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for total Management cost management update  mandatory field validation 
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementUpdateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/update', array_merge($this->UpdationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for totalManagement cost management update
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementUpdate(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/totalManagement/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
       /**
     * Functional test for totalManagement cost management delete
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementDelete(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/totalManagement/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['isDeleted' => true, 'message' => 'Deleted Successfully']
        ]);
    }
    /**
     * Functional test for totalManagement Cost management show
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementShow(): void
    {
        $this->creationSeeder();
        $res = $this->json('POST', 'api/v1/totalManagement/costManagement/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
        ]);
    }
    /**
     * Functional test for totalManagement cost manangement listing
     * 
     * @return void
     */
    public function testFortotalManagementCostManagementListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/costManagement/list', ['project_id' => 1, 'search_param' => ''], $this->getHeader(false));
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
            'company_name' => 'ABC Firm', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'pic_name' => 'PICTest', 
            'sector' => 1, 
            'from_existing' => 0, 
            'client_quota' => 10, 
            'fomnext_quota' => 10, 
            'initial_quota' => 1, 
            'service_quota' => 1
        ];
        $this->json('POST', 'api/v1/totalManagement/addService', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "test name",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "employee_id" => 1,
            "transportation_provider_id" => 1,
            "driver_id" => 1,
            "assign_as_supervisor" => 0,
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10
        ];
        $this->json('POST', 'api/v1/totalManagement/project/add', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "project two",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "employee_id" => 1,
            "transportation_provider_id" => 1,
            "driver_id" => 1,
            "assign_as_supervisor" => 0,
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10
        ];
        $this->json('POST', 'api/v1/totalManagement/project/add', $payload, $this->getHeader(false));

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
            "application_id" => 1,
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
            "application_id" => 1,
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

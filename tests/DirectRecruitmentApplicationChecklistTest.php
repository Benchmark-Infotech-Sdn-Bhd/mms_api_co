<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentApplicationChecklistTest extends TestCase
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
     * A test method for validate id
     * 
     * @return void
     */
    public function testIdFieldRequiredValidation(): void
    {
        $payload =  [
            'id' => '',
            'remarks' => 'test',
            'file_url' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }

    /**
     * Functional test for update DR Application checklist
     */
    public function testForUpdateApplicationChecklist(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $this->updationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * A test method for retrieve DR Application checklist.
     *
     * @return void
     */
    public function testRetrieveSpecificDRApplicationChecklist()
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * A test method for validate Application Id required.
     *
     * @return void
     */
    public function testApplicationIdRetrieveValidation()
    {
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/showBasedOnApplication', ['application_id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                "application_id" => [
                    "The application id field is required."
                ]
            ]
        ]);
    }
    /**
     * A test method for retrieve DR Application checklist Based on Application.
     *
     * @return void
     */
    public function testRetrieveSpecificDRApplicationChecklistBasedOnApplication()
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/showBasedOnApplication', ['application_id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data'
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
            'name' => 'Administrator',
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

        $payload =  [
            'sector_name' => 'IT',
            'sub_sector_name' => 'IT'
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
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'item_name' => 'Document Checklist', 'application_checklist_status' => 'Pending', 
        'remarks' => 'test', 'file_url' => 'test'];
    }
}

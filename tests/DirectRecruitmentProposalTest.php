<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentProposalTest extends TestCase
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
    public function testAddProposalIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * A test method for validate quota applied
     * 
     * @return void
     */
    public function testAddProposalQuotaAppliedValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), ['quota_applied' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_applied' => ['The quota applied field is required.']
            ]
        ]);
    }

    /**
     * A test method for validate person incharge
     * 
     * @return void
     */
    public function testAddProposalPersonInchargeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), ['person_incharge' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'person_incharge' => ['The person incharge field is required.']
            ]
        ]);
    }

    /**
     * A test method for validate cost quoted
     * 
     * @return void
     */
    public function testAddProposalCostQuotedValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), ['cost_quoted' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'cost_quoted' => ['The cost quoted field is required.']
            ]
        ]);
    }
    /**
     * A test method for validate create proposal
     * 
     * @return void
     */
    public function testAddProposalRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['id' => '', 'crm_prospect_id' => '', 'quota_applied' => '', 'person_incharge' => '', 'cost_quoted' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "cost_quoted" => [
                    "The cost quoted field is required."
                ],
                "id" => [
                    "The id field is required."
                ],
                "person_incharge" => [
                    "The person incharge field is required."
                ],
                "quota_applied" => [
                    "The quota applied field is required."
                ]
            ]
        ]);
    }

    /**
     * Functional test to validate size of quota applied in proposal creation
     * 
     * @return void
     */
    public function testAddProposalMinMaxQuotaAppliedValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['id' => '1', 'quota_applied' => '123456789', 'person_incharge' => 'Test', 'cost_quoted' => '2']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "quota_applied" => ["The quota applied must not be greater than 3 characters."]
            ]
        ]);
    }
    /**
     * Functional test to validate Format of quota applied in proposal creation
     * 
     * @return void
     */
    public function testAddProposalQuotaAppliedFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['id' => '1', 'quota_applied' => '4$$', 'person_incharge' => 'Test', 'cost_quoted' => '2']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "quota_applied" => ["The quota applied format is invalid."]
            ]
        ]);
    }
    /**
     * Functional test to validate format of cost coated in proposal creation
     * 
     * @return void
     */
    public function testToAddProposalCostQuotedFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['id' => '1', 'quota_applied' => '10', 'person_incharge' => 'test', 'cost_quoted' => '647.56$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "cost_quoted" => ["The cost quoted format is invalid."]
            ]
        ]);
    }
    /**
     * Functional test for create proposal
     */
    public function testForAddProposal(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', $this->addProposalData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => [
            "isUpdated" => true,
            "message" => "Updated Successfully"
            ]
        ]);
    }
    /**
     * Functional test to update proposal
     * 
     * @return void
     */
    public function testAddProposalFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', array_merge($this->addProposalData(), 
        ['id' => '1', 'quota_applied' => 'dsdsddd', 'person_incharge' => 'test', 'cost_quoted' => 'sdsd']), $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJson([
            "data" => [ 
                "quota_applied" => ["The quota applied format is invalid."],
                "cost_quoted" => ["The cost quoted format is invalid."]
            ]
        ]);
    }
    /**
     * A test method for retrieve specific proposal.
     *
     * @return void
     */
    public function testRetrieveSpecificProposalIdValidation()
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', $this->addProposalData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecrutment/showProposal', ['id' => 0], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => [
                'message' => "Unauthorized."
            ]
        ]);
    }
    /**
     * A test method for retrieve specific proposal.
     *
     * @return void
     */
    public function testRetrieveSpecificProposal()
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutment/submitProposal', $this->addProposalData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecrutment/showProposal', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
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
    }
    /**
     * @return array
     */
    public function addProposalData(): array
    {
        return ['id' => 1, 'crm_prospect_id' => 1, 'quota_applied' => 10, 'person_incharge' => 'test', 
        'cost_quoted' => 10.22, 'remarks' => 'test'];
    }
}
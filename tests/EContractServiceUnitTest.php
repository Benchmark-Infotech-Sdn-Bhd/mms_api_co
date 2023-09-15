<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class EContractServiceUnitTest extends TestCase
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
     * Functional test for e-contract, add service prospect id mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['prospect_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'prospect_id' => ['The prospect id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service company name mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service sector id mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceSectorIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['sector_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector_id' => ['The sector id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service sector name mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceSectorNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['sector_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector_name' => ['The sector name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service fomnext quota mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceFomnextQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['fomnext_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service air ticket deposit mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceAirTicketDepositRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['air_ticket_deposit' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'air_ticket_deposit' => ['The air ticket deposit field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service fomnext quota format validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceFomnextQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['fomnext_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service fomnext quota size validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceFomnextQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['fomnext_quota' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, add service air ticket deposit format validation 
     * 
     * @return void
     */
    public function testForEContractAddServiceAirTicketDepositFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/addService', array_merge($this->creationData(), ['air_ticket_deposit' => 1.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'air_ticket_deposit' => ['The air ticket deposit format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract add service
     * 
     * @return void
     */
    public function testForEContractAddService(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/addService',$this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Service Added Successfully']
        ]);
    }
    /**
     * Functional test for e-contract, get quota prospect service ida mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaProspectServiceIDRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['prospect_service_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'prospect_service_id' => ['The prospect service id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, get quota fomnext quota mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaFomnextQuotaRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['fomnext_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, get quota air ticket deposit mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaAirTicketDepositRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['air_ticket_deposit' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'air_ticket_deposit' => ['The air ticket deposit field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, get quota air ticket deposit format validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaAirTicketDepositFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['air_ticket_deposit' => 1.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'air_ticket_deposit' => ['The air ticket deposit format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, get quota fomnext quota format validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaFomnextQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['fomnext_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract,  get quota fomnext quota size validation 
     * 
     * @return void
     */
    public function testForEContractGetQuotaFomnextQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', array_merge($this->getQuotaData(), ['fomnext_quota' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota must not be greater than 3 characters.']
            ]
        ]);
    }
     /**
     * Functional test for e-contract get quota 
     * 
     * @return void
     */
    public function testForEContractGetQuota(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/allocateQuota', $this->getQuotaData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Quota Updated Successfully.']
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal id mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalIDRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal quota requested mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalQuotaRequestedRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['quota_requested' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal person incharge mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalPersonInChargeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['person_incharge' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'person_incharge' => ['The person incharge field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit cost quoted mandatory field validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalCostQuotedRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['cost_quoted' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'cost_quoted' => ['The cost quoted field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract,submit proposal quota requested format validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalQuotaRequestedFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['quota_requested' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal quota requested size validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalQuotaRequestedSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['quota_requested' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal cost quoted format validation 
     * 
     * @return void
     */
    public function testForEContractSubmitProposalCostQuotedFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', array_merge($this->proposalData(), ['cost_quoted' => 1.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'cost_quoted' => ['The cost quoted format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-contract, submit proposal
     * 
     * @return void
     */
    public function testForEContractSubmitProposal(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/proposalSubmit', $this->proposalData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Proposal Submitted Successfully.']
        ]);
    }
    /**
     * Functional test for e-Contract, application listing search validation
     * 
     * @return void
     */
    public function testForEContractApplicationListingSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/applicationListing', ['search' => 'AB'], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract, application listing with search
     * 
     * @return void
     */
    public function testForEContractApplicationListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/applicationListing', ['search' => 'ABC'], $this->getHeader(false));
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
     * Functional test to Display Proposal 
     * 
     * @return void
     */
    public function testToDisplayProposal(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/showProposal', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
            ]
        ]);
    }
    /**
     * Functional test to Display Service 
     * 
     * @return void
     */
    public function testToDisplayService(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/showService', ['prospect_service_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
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
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['prospect_id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector_id' => 1, 'sector_name' => 'Agriculture', 'fomnext_quota' => 10, 'air_ticket_deposit' => 1.11, 'service_id' => 2, 'file_url' => 'test'];
    }
    /**
     * @return array
     */
    public function getQuotaData(): array
    {
        return ['prospect_service_id' => 1, 'fomnext_quota' => 10, 'air_ticket_deposit' => 1.11];
    }
    /**
     * @return array
     */
    public function proposalData(): array
    {
        return ['id' => 1, 'crm_prospect_id' => 1, 'quota_requested' => 10, 'person_incharge' => 'PICTest', 'cost_quoted' => 20, 'remarks' => 'testRemark', 'file_url' => 'test'];
    }
}

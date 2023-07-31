<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class DirectRecruitmentApplicationUnitTest extends TestCase
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
     * Functional test for Add Service
     * 
     * @return void
     */
    public function testForAddService(): void
    {
        $this->crmCreationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->crmProspectCreationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Service Added Successfully']
        ]);
    }
    /**
     * Functional test for List Direct Recruitment Application Without Search
     * 
     * @return void
     */
    public function testForApplicationListingWithoutSearch(): void
    {
        $this->crmCreationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->crmProspectCreationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['sector' => 2]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => '', 'filter' => '', 'contract_type' => ''], $this->getHeader(false));
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
     * Functional test for List Direct Recruitment Application with search
     * 
     * @return void
     */
    public function testForApplicationListingWithSearch(): void
    {
        $this->crmCreationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->crmProspectCreationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['sector' => 2]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => 'AB', 'filter' => '', 'contract_type' => ''], $this->getHeader(false));
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
     * Functional test for List Direct Recruitment Application with Filter
     * 
     * @return void
     */
    public function testForApplicationListingWithFilter(): void
    {
        $this->crmCreationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->crmProspectCreationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['sector' => 2]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => '', 'filter' => 'Pending Proposal', 'contract_type' => ''], $this->getHeader(false));
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
     * Functional test for List Direct Recruitment Application with Contract Type Filter
     * 
     * @return void
     */
    public function testForApplicationListingWithContractTypeFilter(): void
    {
        $this->crmCreationSeeder();
        $this->json('POST', 'api/v1/crm/create', $this->crmProspectCreationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', $this->creationData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['sector' => 2]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => '', 'filter' => '', 'contract_type' => 'Zero Cost'], $this->getHeader(false));
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
    public function crmCreationSeeder(): void
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
            'name' => 'Administrator'
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

        $payload =  [
            'sector_name' => 'IT',
            'sub_sector_name' => 'IT'
        ];  
        $this->json('POST', 'api/v1/sector/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function crmProspectCreationData(): array
    {
        return ['company_name' => 'ABC Firm', 'contract_type' => 'Zero Cost', 'roc_number' => 'APS6376', 'director_or_owner' => 'Test', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'address' => 'Coimbatore', 'pic_name' => 'PICTest', 'pic_contact_number' => '764859694', 'pic_designation' => 'Manager', 'registered_by' => 1, 'sector_type' => 1, 'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])];
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector' => 1, 'contract_type' => 'Zero Cost', 'service_id' => 1];
    }
}

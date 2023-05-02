<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class CRMProspectUnitTest extends TestCase
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
     * Functional test for CRM prospect register validation 
     * 
     * @return void
     */
    public function testForProspectCreationValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', [], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.'], 
                'roc_number' => ['The roc number field is required.'], 
                'contact_number' => ['The contact number field is required.'],
                'email' => ['The email field is required.'],
                'address' => ['The address field is required.'],
                'pic_name' => ['The pic name field is required.'],
                'pic_contact_number' => ['The pic contact number field is required.'],
                'pic_designation' => ['The pic designation field is required.'],
                'registered_by' => ['The registered by field is required.'],
                'sector_type' => ['The sector type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect company name register validation 
     * 
     * @return void
     */
    public function testForProspectCompanyNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'ABC Firm123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect contact number size validation 
     * 
     * @return void
     */
    public function testForProspectContactNumberSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => 647348435879845798]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect contact number type validation 
     * 
     * @return void
     */
    public function testForProspectContactNumberTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => 6473498.67]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC contact number size validation 
     * 
     * @return void
     */
    public function testForProspectPICContactNumberSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => 6473498867588767]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC contact number type validation 
     * 
     * @return void
     */
    public function testForProspectPICContactNumberTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => 6473498.67]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect registration 
     * 
     * @return void
     */
    public function testForProspectCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/crm/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Prospect Created Successfully']
        ]);
    }
    /**
     * Functional test for CRM prospect update validation 
     * 
     * @return void
     */
    public function testForProspectUpdationValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', [], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.'],
                'company_name' => ['The company name field is required.'], 
                'roc_number' => ['The roc number field is required.'], 
                'contact_number' => ['The contact number field is required.'],
                'email' => ['The email field is required.'],
                'address' => ['The address field is required.'],
                'pic_name' => ['The pic name field is required.'],
                'pic_contact_number' => ['The pic contact number field is required.'],
                'pic_designation' => ['The pic designation field is required.'],
                'registered_by' => ['The registered by field is required.'],
                'sector_type' => ['The sector type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect updation 
     * 
     * @return void
     */
    public function testForProspectUpdation(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Prospect Updated Successfully']
        ]);
    }
    /**
     * Functional test to list CRM prospects
     * 
     * @return void
     */
    public function testForProspectList(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => '', 'filter' => ''], $this->getHeader(false));
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
     * Functional test to list CRM prospects with search
     * 
     * @return void
     */
    public function testForProspectListWithSearch(): void
    {
        $this->testForProspectCreation();
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'XYZ Firm']), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => 'XY', 'filter' => ''], $this->getHeader(false));
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
     * Functional test to list CRM prospects with filter
     * 
     * @return void
     */
    public function testForProspectListWithFilter(): void
    {
        $this->testForProspectCreation();
        $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => 'XYZ Firm', 'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"]])]), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/crm/list', ['search' => '', 'filter' => 3], $this->getHeader(false));
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
     * Functional test to show CRM prospect
     * 
     * @return void
     */
    public function testForProspectShow(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name',
                        'roc_number',
                        'director_or_owner',
                        'contact_number',
                        'email',
                        'address',
                        'pic_name',
                        'pic_contact_number',
                        'pic_designation',
                        'registered_by',
                        'prospect_services',
                        'prospect_login_credentials'
                    ]
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
            'name' => 'Admin'
        ];
        $res = $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
        $this->artisan("db:seed --class=unit_testing_employee");
        $payload =  [
            'sector_name' => 'Agriculture',
            'sub_sector_name' => 'Agriculture'
        ];  
        $this->json('POST', 'api/v1/sector/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['company_name' => 'ABC Firm', 'contract_type' => 'Zero Cost', 'roc_number' => 'APS6376', 'director_or_owner' => 'Test', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'address' => 'Coimbatore', 'pic_name' => 'PICTest', 'pic_contact_number' => '764859694', 'pic_designation' => 'Manager', 'registered_by' => 1, 'sector_type' => 1, 'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'company_name' => 'ABC Firm', 'contract_type' => 'Normal', 'roc_number' => 'APS6376', 'director_or_owner' => 'Test', 'contact_number' => '76845697', 'email' => 'test@gmail.com', 'address' => 'Coimbatore', 'pic_name' => 'PICTest', 'pic_contact_number' => '764859694', 'pic_designation' => 'Manager', 'registered_by' => 1, 'sector_type' => 1];
    }
}

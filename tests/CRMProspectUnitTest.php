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
     * Functional test for CRM prospect Comapny name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Director/Owner mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationDirectorOrOwnerRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['director_or_owner' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect ROC number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationROCNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['roc_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Address mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationAddressRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationPICNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Contact Number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationPICContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Registered By mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationRegisteredByRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['registered_by' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'registered_by' => ['The registered by field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Sector Type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationSectorTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['sector_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector_type' => ['The sector type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Service type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectCreationServiceTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['prospect_service' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'prospect_service' => ['The prospect service field is required.']
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
     * Functional test for CRM prospect PIC designation type validation 
     * 
     * @return void
     */
    public function testForProspectPICDesignationTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/create', array_merge($this->creationData(), ['pic_designation' => 'HR1']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_designation' => ['The pic designation format is invalid.']
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
     * Functional test for CRM prospect id mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationIDRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Comapny name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Director/Owner mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationDirectorOrOwnerRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['director_or_owner' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'director_or_owner' => ['The director or owner field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect ROC number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationROCNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['roc_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'roc_number' => ['The roc number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect email mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Address mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationAddressRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Name mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Contact Number mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_contact_number' => ['The pic contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect PIC Designation type validation 
     * 
     * @return void
     */
    public function testForProspectUpdationPICDesignationTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['pic_designation' => 'HR1']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_designation' => ['The pic designation format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Registered By mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationRegisteredByRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['registered_by' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'registered_by' => ['The registered by field is required.']
            ]
        ]);
    }
    /**
     * Functional test for CRM prospect Sector Type mandatory field validation 
     * 
     * @return void
     */
    public function testForProspectUpdationSectorTypeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/crm/update', array_merge($this->updationData(), ['sector_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
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
                        'registered_by_name',
                        'prospect_services',
                        'prospect_login_credentials'
                    ]
                ]
        ]);
    }
    /**
     * Functional test to show Companies Dropdown
     * 
     * @return void
     */
    public function testForCompaniesDropDown(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/dropDownCompanies', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name'
                    ]
                ]
        ]);
    }
    /**
     * Functional test to show Prospect Details
     * 
     * @return void
     */
    public function testForGetProspectDetails(): void
    {
        $this->testForProspectCreation();
        $response = $this->json('POST', 'api/v1/crm/getProspectDetails', ["id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                        'id',
                        'company_name',
                        'contact_number',
                        'email',
                        'pic_name'
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

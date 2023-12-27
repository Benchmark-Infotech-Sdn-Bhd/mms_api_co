<?php

namespace Tests;
use Illuminate\Support\Carbon;

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
     * Functional test for Add Service id required validation
     * 
     * @return void
     */
    public function testForAddServiceIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service company_name required validation
     * 
     * @return void
     */
    public function testForAddServiceCompanyNameRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service contact_number required validation
     * 
     * @return void
     */
    public function testForAddServiceContactNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['contact_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service email required validation
     * 
     * @return void
     */
    public function testForAddServiceEmailrRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['email' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service pic_name required validation
     * 
     * @return void
     */
    public function testForAddServicePICNamerRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['pic_name' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service sector required validation
     * 
     * @return void
     */
    public function testForAddServiceSectorRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['sector' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sector' => ['The sector field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service contract_type required validation
     * 
     * @return void
     */
    public function testForAddServiceContractTypeRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['contract_type' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contract_type' => ['The contract type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service service_id required validation
     * 
     * @return void
     */
    public function testForAddServiceServiceIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/addService', array_merge($this->creationData(), ['service_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'service_id' => ['The service id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Add Service
     * 
     * @return void
     */
    public function testForAddService(): void
    {
        $this->creationSeeder();
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
        $this->creationSeeder();
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
     * Functional test for List Direct Recruitment Application search validation
     * 
     * @return void
     */
    public function testForApplicationListingSearchValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => 'AB', 'filter' => '', 'contract_type' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
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
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/applicationListing', ['search' => 'ABC', 'filter' => '', 'contract_type' => ''], $this->getHeader(false));
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
        $this->creationSeeder();
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
        $this->creationSeeder();
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
     * Functional test for dropdown filter
     * 
     * @return void
     */
    public function testForDropDownFilter(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/dropDownFilter', $this->creationData(), $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for List Total Management Application Without Search
     * 
     * @return void
     */
    public function testForTotalManagementApplicationListingWithoutSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/totalManagementListing', ['search' => ''], $this->getHeader(false));
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
     * Functional test for List Total Management Application search validation
     * 
     * @return void
     */
    public function testForTotalManagementApplicationListingSearchValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/totalManagementListing', ['search' => 'AB'], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for List Total Management Application with search
     * 
     * @return void
     */
    public function testForTotalManagementApplicationListingWithSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/totalManagementListing', ['search' => 'ABC'], $this->getHeader(false));
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
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector' => 1, 'contract_type' => 'Zero Cost', 'service_id' => 1];
    }
}

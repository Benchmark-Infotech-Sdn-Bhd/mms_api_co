<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class InvoiceTest extends TestCase
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
     * Functional test to view xeroGetItems
     */
    public function testForXeroGetItems(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoice/xeroGetItems', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                ]
        ]);
    }
    /**
     * Functional test to view xeroGetTaxRates
     */
    public function testForxeroGetTaxRates(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoice/xeroGetTaxRates', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [

                ]
        ]);
    }
    /**
     * Functional test to view xeroGetAccounts
     */
    public function testForXeroGetAccounts(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoice/xeroGetAccounts', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test to validate Required fields for CrmProspectId
     * 
     * @return void
     */
    public function testForCrmProspectIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/create', array_merge($this->creationData(), 
        ['crm_prospect_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "crm_prospect_id" => [
                    "The crm prospect id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for IssueDate
     * 
     * @return void
     */
    public function testForIssueDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/create', array_merge($this->creationData(), 
        ['issue_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "issue_date" => [
                    "The issue date field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for DueDate
     * 
     * @return void
     */
    public function testForDueDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/create', array_merge($this->creationData(), 
        ['due_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "due_date" => [
                    "The due date field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for ReferenceNumber
     * 
     * @return void
     */
    public function testForReferenceNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/create', array_merge($this->creationData(), 
        ['reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "reference_number" => [
                    "The reference number field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Amount
     * 
     * @return void
     */
    public function testForAmountRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/create', array_merge($this->creationData(), 
        ['amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "amount" => [
                    "The amount field is required."
                ]
            ]
        ]);
    }
    // /**
    //  * Functional test for create Invoice
    //  */
    // public function testForCreateInvoice(): void
    // {
    //     $this->creationSeeder();
    //     $response = $this->json('POST', 'api/v1/invoice/create', $this->creationData(), $this->getHeader(false)); 
    //     $response->seeStatusCode(200);
    //     $this->response->assertJsonStructure([
    //         "data" =>
    //         [
    //         ]
    //     ]);
    // }
    /**
     * Functional test to list Invoice
     */
    public function testForListingInvoiceWithSearch(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/invoice/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/invoice/list', ['search_param' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
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
     * Functional test to view Invoice Required Validation
     */
    public function testForViewInvoiceItemsValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoice/show', ['id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to view Invoice
     */
    public function testForViewInvoice(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/invoice/create', $this->creationData(), $this->getHeader(false)); 
        $response = $this->json('POST', 'api/v1/invoice/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
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
            "subsidiary_companies" => []
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

    }

    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
            'crm_prospect_id' => 1, 
            'issue_date' => '2023-12-12',             
            'due_date' => '2023-12-12', 
            'reference_number' => 'Test123',
            'amount' => 100,
            'invoice_items' => json_encode([["expense_id" => 1, "service_id" => 1, "item" => "test", "description" => "test description", "quantity" => 2, "price" => 100, "account" => "", "tax_rate" => 0, "total_price" => 200]]),
            'tax' => 0,
            'remarks' => 'test remarks',
            'domain_name' => 'hcm.benchmarkit.com.my'
        ];
    }
    
}
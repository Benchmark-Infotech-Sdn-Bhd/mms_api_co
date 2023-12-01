<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class InvoiceItemsTempTest extends TestCase
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
     * Functional test to validate Required fields for CrmProspectId
     * 
     * @return void
     */
    public function testForCrmProspectIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', array_merge($this->creationData(), 
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
     * Functional test to validate Required fields for ServiceId
     * 
     * @return void
     */
    public function testForServiceIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', array_merge($this->creationData(), 
        ['service_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "service_id" => [
                    "The service id field is required."
                ]
            ]
        ]);
    }
    
    /**
     * Functional test to validate Required fields for InvoiceItems
     * 
     * @return void
     */
    public function testForInvoiceItemsRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', array_merge($this->creationData(), 
        ['invoice_items' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "invoice_items" => [
                    "The invoice items field is required."
                ]
            ]
        ]);
    }
    
    /**
     * Functional test for create InvoiceItemsTemp
     */
    public function testForCreateInvoiceItemsTemp(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false)); 
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'crm_prospect_id',
                'service_id',
                'expense_id',
                'invoice_number',
                'item',
                'description',
                'quantity',
                'price',
                'account',
                'tax_rate',
                'total_price',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Id
     * 
     * @return void
     */
    public function testForIdRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/update', array_merge($this->updationData(), 
        ['id' => '']), $this->getHeader(false));
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
     * Functional test to validate Required fields for ServiceId
     * 
     * @return void
     */
    public function testForServiceIdRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/update', array_merge($this->updationData(), 
        ['service_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "service_id" => [
                    "The service id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for CrmProspectId
     * 
     * @return void
     */
    public function testForCrmProspectIdRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/update', array_merge($this->updationData(), 
        ['crm_prospect_id' => '']), $this->getHeader(false));
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
     * Functional test for update InvoiceItemsTemp
     */
    public function testForUpdateInvoiceItemsTemp(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [

            ]
        ]);
    }
    /**
     * Functional test to list InvoiceItemsTemp
     */
    public function testForListingInvoiceItemsTempWithSearch(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/list', ['search_param' => ''], $this->getHeader(false));
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
     * Functional test to view InvoiceItemsTemp Required Validation
     */
    public function testForViewInvoiceItemsTempRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false)); 

        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/show', ['id' => ''], $this->getHeader());
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
     * Functional test to view InvoiceItemsTemp
     */
    public function testForViewInvoiceItemsTemp(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false)); 
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'crm_prospect_id',
                    'service_id',
                    'expense_id',
                    'invoice_number',
                    'item',
                    'description',
                    'quantity',
                    'price',
                    'account',
                    'tax_rate',
                    'total_price',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'crm_prospect'
                ]
        ]);
    }
    /**
     * Functional test to delete InvoiceItemsTemp
     */
    public function testForDeleteInvoiceItemsTemp(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false)); 
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/delete', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'isDeleted',
                    'message'
                ]
        ]);
    }
    /**
     * Functional test to delete all InvoiceItemsTemp
     */
    public function testForDeleteAllInvoiceItemsTemp(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/create', $this->creationData(), $this->getHeader(false)); 
        $response = $this->json('POST', 'api/v1/invoiceItemsTemp/deleteAll', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'isDeleted',
                    'message'
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
            'service_id' => 1, 
            'crm_prospect_id' => 1, 
            'invoice_items' => json_encode([["expense_id" => 1, "invoice_number" => "", "item" => "test", "description" => "test description", "quantity" => 2, "price" => 100, "account" => "", "total_price" => 200]]),
            'domain_name' => 'hcm.benchmarkit.com.my'
        ];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1,  'service_id' => 1, 'crm_prospect_id'=> 1, 'expense_id' => 1, 'invoice_number' => '', 'item' => 'Test', 'description' => 'test description', 'quantity' => 2, 'price' => 100, 'account' => '', 'tax_rate' => 0, 'total_price' => 200,  'domain_name' => 'hcm.benchmarkit.com.my'];
    }
}
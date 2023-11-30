<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentExpensesTest extends TestCase
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
     * Functional test to validate Required fields for ApplicationId
     * 
     * @return void
     */
    public function testForApplicationIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', array_merge($this->creationData(), 
        ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "application_id" => [
                    "The application id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Title
     * 
     * @return void
     */
    public function testForTitleRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', array_merge($this->creationData(), 
        ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "title" => [
                    "The title field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Payment Reference Number
     * 
     * @return void
     */
    public function testForPaymentReferenceNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', array_merge($this->creationData(), 
        ['payment_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "payment_reference_number" => [
                    "The payment reference number field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Payment Date
     * 
     * @return void
     */
    public function testForPaymentDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', array_merge($this->creationData(), 
        ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "payment_date" => [
                    "The payment date field is required."
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
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', array_merge($this->creationData(), 
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
    /**
     * Functional test for create Expenses
     */
    public function testForCreateExpenses(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false)); 
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'application_id',
                'title',
                'payment_reference_number',
                'payment_date',
                'quantity',
                'amount',
                'remarks',
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
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
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
     * Functional test to validate Required fields for Application Id
     * 
     * @return void
     */
    public function testForApplicationIdRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
        ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "application_id" => [
                    "The application id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Title
     * 
     * @return void
     */
    public function testForTitleRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
        ['title' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "title" => [
                    "The title field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Payment Reference Number
     * 
     * @return void
     */
    public function testForPaymentReferenceNumberRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
        ['payment_reference_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "payment_reference_number" => [
                    "The payment reference number field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Payment Date
     * 
     * @return void
     */
    public function testForPaymentDateRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
        ['payment_date' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "payment_date" => [
                    "The payment date field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Amount
     * 
     * @return void
     */
    public function testForAmountRequiredUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', array_merge($this->updationData(), 
        ['amount' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "amount" => [
                    "The amount field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for update Expenses
     */
    public function testForUpdateExpenses(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [

            ]
        ]);
    }
    /**
     * Functional test to list Expenses
     */
    public function testForListingExpensesWithSearch(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/list', ['search_param' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test to view Expenses Required Validation
     */
    public function testForViewExpensesRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false)); 

        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/show', ['id' => ''], $this->getHeader());
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
     * Functional test to view Expense
     */
    public function testForViewExpenses(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false)); 
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'application_id',
                    'title',
                    'payment_reference_number',
                    'payment_date',
                    'quantity',
                    'amount',
                    'remarks',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'invoice_number',
                    'direct_recruitment_expenses_attachments'
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
    public function creationData(): array
    {
        return ['application_id' => 1, 'title' => 'Test', 'payment_reference_number' => 'TestExp123', 'payment_date' => '2023-11-30', 'amount' => '100', 'remarks' => 'test remarks', 'quantity' => 1, 'domain_name' => 'hcm.benchmarkit.com.my'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'title' => 'TestUpdate', 'payment_reference_number' => 'TestExp123', 'payment_date' => '2023-11-30', 'amount' => '100', 'remarks' => 'test remarks', 'quantity' => 1, 'domain_name' => 'hcm.benchmarkit.com.my'];
    }
}
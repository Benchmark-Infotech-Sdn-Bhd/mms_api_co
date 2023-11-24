<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

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
     * Functional test for create Agent
     */
    public function testForCreateExpenses(): void
    {
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());        
        $response->seeStatusCode(200);
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
     * Functional test for update Agent
     */
    public function testForUpdateExpenses(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/update', $this->updationData(), $this->getHeader(false));
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
     * Functional test to list Agents
     */
    public function testForListingExpensesWithSearch(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/list', ['application_id' => 1, 'page' => 1, 'search_param' => '', 'domain_name' => 'hcm.benchmarkit.com.my'], $this->getHeader());
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/directRecrutmentExpenses/list', ['search_param' => ''], $this->getHeader(false));
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
     * Functional test to view Agent Required Validation
     */
    public function testForViewExpensesRequiredValidation(): void
    {
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
     * Functional test to view Agent
     */
    public function testForViewExpenses(): void
    {
        $this->json('POST', 'api/v1/directRecrutmentExpenses/create', $this->creationData(), $this->getHeader(false));
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
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'title' => 'Test', 'payment_reference_number' => 'TestExp123', 'payment_date' => '2023-11-24', 'amount' => '100', 'remarks' => 'test remarks', 'quantity' => 1, 'domain_name' => 'hcm.benchmarkit.com.my'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'title' => 'TestUpdate', 'payment_reference_number' => 'TestExp123', 'payment_date' => '2023-11-24', 'amount' => '100', 'remarks' => 'test remarks', 'quantity' => 1, 'domain_name' => 'hcm.benchmarkit.com.my'];
    }
}
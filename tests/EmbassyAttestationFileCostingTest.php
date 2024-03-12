<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class EmbassyAttestationFileCostingTest extends TestCase
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
     * Functional test to validate Required fields for EmbassyAttestationFileCosting creation
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', array_merge($this->creationData(), 
        ['country_id' => '', 'title' => '', 'amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "country_id" => [
                    "The country id field is required."
                ],
                "title" => [
                    "The title field is required."
                ],
                "amount" => [
                    "The amount field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for EmbassyAttestationFileCosting creation
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingCreationUserValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', array_merge($this->creationData(), 
        ['country_id' => 0]), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            "data" => [ 
                "message" => "Unauthorized."
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Country Id
     * 
     * @return void
     */
    public function testForCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', array_merge($this->creationData(), 
        ['country_id' => '', 'title' => 'test', 'amount' => 78]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "country_id" => [
                    "The country id field is required."
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
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', array_merge($this->creationData(), 
        ['country_id' => 1, 'title' => '', 'amount' => 98]), $this->getHeader());
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
     * Functional test to validate Required fields for Amount
     * 
     * @return void
     */
    public function testForAmountRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', array_merge($this->creationData(), 
        ['country_id' => 1, 'title' => 'Test', 'amount' => '']), $this->getHeader());
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
     * Functional test to validate Required fields for EmbassyAttestationFileCosting Updation
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingUpdationRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/update', array_merge($this->updationData(), 
        ['id' => '','country_id' => '', 'title' => '', 'amount' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "country_id" => [
                    "The country id field is required."
                ],
                "title" => [
                    "The title field is required."
                ],
                "amount" => [
                    "The amount field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create EmbassyAttestationFileCosting
     */
    public function testForCreateEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'id',
                'country_id',
                'title',
                'amount',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            ]
        ]);
    }
    /**
     * Functional test for update EmbassyAttestationFileCosting
     */
    public function testForUpdateEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/update', $this->updationData(), $this->getHeader(false));
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
     * Functional test for delete EmbassyAttestationFileCosting
     */
    public function testForDeleteEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isDeleted',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for EmbassyAttestationFileCosting Listing
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingListingRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/list', array_merge($this->updationData(), 
        ['country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "country_id" => [
                    "The country id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to list EmbassyAttestationFileCosting user validation
     */
    public function testForListingEmbassyAttestationFileCostingUserValidation(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/list', ['country_id' => 0], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => [
                'message' => "Unauthorized."
            ]
        ]);
    }
    /**
     * Functional test to list EmbassyAttestationFileCosting
     */
    public function testForListingEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/list', ['country_id' => 1], $this->getHeader(false));
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
     * Functional test to validate Required fields for EmbassyAttestationFileCosting View
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingViewRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/show', array_merge($this->updationData(), 
        ['id' => '']), $this->getHeader());
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
     * Functional test to view EmbassyAttestationFileCosting
     */
    public function testForViewEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'country_id',
                    'title',
                    'amount',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['country_id' => 1, 'title' => 'Document', 'amount' => 10];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'country_id' => 1, 'title' => 'Document', 'amount' => 10];
    }
}

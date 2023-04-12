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
     * Functional test to validate Required fields for EmbassyAttestationFileCosting Updation
     * 
     * @return void
     */
    public function testForEmbassyAttestationFileCostingUpdationRequiredValidation(): void
    {
        $response = $this->json('PUT', 'api/v1/embassyAttestationFile/update', array_merge($this->updationData(), 
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
     * Functional test for create EmbassyAttestationFileCosting
     */
    public function testForCreateEmbassyAttestationFileCosting(): void
    {
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'message'
            ]
        ]);
    }
    /**
     * Functional test for update EmbassyAttestationFileCosting
     */
    public function testForUpdateEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
        $response = $this->json('PUT', 'api/v1/embassyAttestationFile/update', $this->updationData(), $this->getHeader(false));
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
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
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
     * Functional test to list EmbassyAttestationFileCosting
     */
    public function testForListingEmbassyAttestationFileCosting(): void
    {
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
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
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/embassyAttestationFile/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'message'
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

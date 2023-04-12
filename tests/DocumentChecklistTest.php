<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DocumentChecklistTest extends TestCase
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
     * Functional test to validate Required fields for DocumentChecklist creation
     * 
     * @return void
     */
    public function testForDocumentChecklistCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/documentChecklist/create', array_merge($this->creationData(), 
        ['sector_id' => '', 'document_title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "sector_id" => [
                "The sector id field is required."
            ],
            "document_title" => [
                "The document title field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for DocumentChecklist Updation
     * 
     * @return void
     */
    public function testForDocumentChecklistUpdationRequiredValidation(): void
    {
        $response = $this->json('PUT', 'api/v1/documentChecklist/update', array_merge($this->updationData(), 
        ['sector_id' => '', 'document_title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "sector_id" => [
                    "The sector id field is required."
                ],
                "document_title" => [
                    "The document title field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create DocumentChecklist
     */
    public function testForCreateDocumentChecklist(): void
    {
        $response = $this->json('POST', 'api/v1/documentChecklist/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'message'
            ]
        ]);
    }
    /**
     * Functional test for update DocumentChecklist
     */
    public function testForUpdateDocumentChecklist(): void
    {
        $this->json('POST', 'api/v1/documentChecklist/create', $this->creationData(), $this->getHeader());
        $response = $this->json('PUT', 'api/v1/documentChecklist/update', $this->updationData(), $this->getHeader(false));
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
     * Functional test for delete DocumentChecklist
     */
    public function testForDeleteDocumentChecklist(): void
    {
        $this->json('POST', 'api/v1/documentChecklist/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/documentChecklist/delete', ['id' => 1], $this->getHeader(false));
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
     * Functional test to validate Required fields for DocumentChecklist Listing
     * 
     * @return void
     */
    public function testForDocumentChecklistListingRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/documentChecklist/list', array_merge($this->updationData(), 
        ['sector_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "sector_id" => [
                    "The sector id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to list DocumentChecklist
     */
    public function testForListingDocumentChecklist(): void
    {
        $this->json('POST', 'api/v1/documentChecklist/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/documentChecklist/list', ['sector_id' => 1], $this->getHeader(false));
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
     * Functional test to validate Required fields for DocumentChecklist View
     * 
     * @return void
     */
    public function testForDocumentChecklistViewRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/documentChecklist/show', array_merge($this->updationData(), 
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
     * Functional test to view DocumentChecklist
     */
    public function testForViewDocumentChecklist(): void
    {
        $this->json('POST', 'api/v1/documentChecklist/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/documentChecklist/show', ['id' => 1], $this->getHeader(false));
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
        return ['sector_id' => 1, 'document_title' => 'Document'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'sector_id' => 1, 'document_title' => 'Document'];
    }
}

<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DocumentChecklistAttachmentTest extends TestCase
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
     * Functional test to validate Required fields for DocumentChecklist Attachment creation
     * 
     * @return void
     */
    public function testForDocumentChecklistAttachmentCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => '', 'application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "document_checklist_id" => [
                "The document checklist id field is required."
            ],
            "application_id" => [
                "The application id field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for DocumentChecklistId
     * 
     * @return void
     */
    public function testForDocumentChecklistIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => '', 'application_id' => 2]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "document_checklist_id" => [
                "The document checklist id field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Application Id
     * 
     * @return void
     */
    public function testForApplicationIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => 1, 'application_id' => '']), $this->getHeader());
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
     * Functional test to validate Valid format fields for DocumentChecklist Attachment creation
     * 
     * @return void
     */
    public function testForDocumentChecklistAttachmentCreationFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => 'id', 'application_id' => 'id']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "document_checklist_id" => [
                "The document checklist id format is invalid."
            ],
            "application_id" => [
                "The application id format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Valid format fields for DocumentChecklist Id
     * 
     * @return void
     */
    public function testForDocumentChecklistIdFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => 'id', 'application_id' => 1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "document_checklist_id" => [
                "The document checklist id format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Valid format fields for Application Id
     * 
     * @return void
     */
    public function testForApplicationIdFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', array_merge($this->creationData(), 
        ['document_checklist_id' => 1, 'application_id' => 'id']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "application_id" => [
                "The application id format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test for create DocumentChecklist Attachment
     */
    public function testForCreateDocumentChecklist(): void
    {
        $this->json('POST', 'api/v1/sector/create', ['sector_name' => 'Agriculture', 'sub_sector_name' => 'Agriculture'], $this->getHeader());
        $this->json('POST', 'api/v1/documentChecklist/create', ['sector_id' => 1, 'document_title' => 'Document', 'remarks' => 'test'], $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/checklistAttachment/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUploaded',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Id for delete
     * 
     * @return void
     */
    public function testForDocumentChecklistAttachmentIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/delete',['id' => ''], $this->getHeader());
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
     * Functional test for delete DocumentChecklist Attachment
     */
    public function testForDeleteDocumentChecklistAttachment(): void
    {
        $this->json('POST', 'api/v1/sector/create', ['sector_name' => 'Agriculture', 'sub_sector_name' => 'Agriculture'], $this->getHeader());
        $this->json('POST', 'api/v1/documentChecklist/create', ['sector_id' => 1, 'document_title' => 'Document', 'remarks' => 'test'], $this->getHeader(false));
        $this->json('POST', 'api/v1/checklistAttachment/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/checklistAttachment/delete', ['id' => 1], $this->getHeader(false));
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
     * Functional test to validate Required fields for DocumentChecklist with Attachments Listing
     * 
     * @return void
     */
    public function testForDocumentChecklistAttachmentListingRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/checklistAttachment/list', array_merge($this->updationData(), 
        ['sector_id' => '','application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "sector_id" => [
                    "The sector id field is required."
                ],
                "application_id" => [
                    "The application id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to list DocumentChecklist with Attachments
     */
    public function testForListingDocumentChecklistWithAttachments(): void
    {
        $this->json('POST', 'api/v1/sector/create', ['sector_name' => 'Agriculture', 'sub_sector_name' => 'Agriculture'], $this->getHeader());
        $this->json('POST', 'api/v1/documentChecklist/create', ['sector_id' => 1, 'document_title' => 'Document', 'remarks' => 'test'], $this->getHeader(false));
        $this->json('POST', 'api/v1/checklistAttachment/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/checklistAttachment/list', ['sector_id' => 1,'application_id' => 1], $this->getHeader(false));
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
     * @return array
     */
    public function creationData(): array
    {
        return ['document_checklist_id' => 1, 'application_id' => 1, 'file_type' => 'checklist',
            'file_url' => 'test'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1,'document_checklist_id' => 1, 'application_id' => 1, 'file_type' => 'checklist',
        'file_url' => 'test'];
    }
}

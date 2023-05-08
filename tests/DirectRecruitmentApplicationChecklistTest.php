<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DirectRecruitmentApplicationChecklistTest extends TestCase
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
     * A test method for validate id
     * 
     * @return void
     */
    public function testIdFieldRequiredValidation(): void
    {
        $payload =  [
            'id' => '',
            'remarks' => 'test',
            'file_url' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }

    /**
     * Functional test for update DR Application checklist
     */
    public function testForUpdateApplicationChecklist(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $this->updationData(), $this->getHeader());
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
     * A test method for retrieve DR Application checklist.
     *
     * @return void
     */
    public function testRetrieveSpecificDRApplicationChecklist()
    {
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * A test method for validate Application Id required.
     *
     * @return void
     */
    public function testApplicationIdRetrieveValidation()
    {
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/showBasedOnApplication', ['application_id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                "application_id" => [
                    "The application id field is required."
                ]
            ]
        ]);
    }
    /**
     * A test method for retrieve DR Application checklist Based on Application.
     *
     * @return void
     */
    public function testRetrieveSpecificDRApplicationChecklistBasedOnApplication()
    {
        $response = $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/showBasedOnApplication', ['application_id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'item_name' => 'Document Checklist', 'application_checklist_status' => 'Pending', 
        'remarks' => 'test', 'file_url' => 'test'];
    }
}

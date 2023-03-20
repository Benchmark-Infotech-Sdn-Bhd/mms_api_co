<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class DocumentChecklistTest extends TestCase
{
    protected Generator $faker;
    /**
     * A test method for create new DocumentChecklist.
     *
     * @return void
     */
    public function testNewDocumentChecklist()
    {
        $this->faker = Factory::create();
        $payload =  [
            'sector_id' => 1,
            'document_title' => $this->faker->text()
        ];
        $response = $this->post('/api/v1/documentChecklist/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for update existing DocumentChecklist.
     *
     * @return void
     */
    public function testUpdateDocumentChecklist()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 2,
            'sector_id' => 2,
            'document_title' => $this->faker->text()
        ];
        $response = $this->put('/api/v1/documentChecklist/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for delete existing DocumentChecklist.
     *
     * @return void
     */
    public function testDeleteDocumentChecklist()
    {
        $payload =  [
            'id' => 3
        ];
        $response = $this->post('/api/v1/documentChecklist/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for retrieve DocumentChecklist based on Sector.
     *
     * @return void
     */
    public function testShouldReturnDocumentChecklistBySector()
    {
        $response = $this->post("/api/v1/documentChecklist/retrieveBySector",['sector_id' => 2]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
}

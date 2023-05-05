<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class SectorsTest extends TestCase
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
     * Functional test to validate Required fields for Sector creation
     * 
     * @return void
     */
    public function testForSectorCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        ['sector_name' => '', 'sub_sector_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "sector_name" => [
                "The sector name field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Sector name
     * 
     * @return void
     */
    public function testForSectorNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        ['sector_name' => '', 'sub_sector_name' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "sector_name" => [
                "The sector name field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Sector creation
     * 
     * @return void
     */
    public function testForSectorCreationMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        [
        'sector_name' => 'AAAAAAAAAAAAAAAAAA DFEWF REWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REREEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE ERRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR srrrrrrrrrrrrrrrr jrhtirhiti', 
        'sub_sector_name' => 'AAAAAAAAAAAAAAAAAA DFEWF REWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REREEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE ERRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR srrrrrrrrrrrrrrrr ruhtfiritj'
        ]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sector_name" => [
                "The sector name must not be greater than 255 characters."
            ],
            "sub_sector_name" => [
                "The sub sector name must not be greater than 255 characters."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Sector name
     * 
     * @return void
     */
    public function testForSectorNameMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        [
        'sector_name' => 'AAAAAAAAAAAAAAAAAA DFEWF REWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REREEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE ERRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR srrrrrrrrrrrrrrrr jrhtirhiti', 
        'sub_sector_name' => 'AAAAAAAA'
        ]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sector_name" => [
                "The sector name must not be greater than 255 characters."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Sub sector name
     * 
     * @return void
     */
    public function testForSubSectorNameMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        [
        'sector_name' => 'AAAAAAA', 
        'sub_sector_name' => 'AAAAAAAAAAAAAAAAAA DFEWF REWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWWW REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REREEEEEEEEEEEEEEEEEEEEEEEEEEEEEE REEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE ERRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR srrrrrrrrrrrrrrrr ruhtfiritj'
        ]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sub_sector_name" => [
                "The sub sector name must not be greater than 255 characters."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in sector creation
     * 
     * @return void
     */
    public function testForSectorCreationFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        ['sector_name' => 'Malay123$', 
        'sub_sector_name' => 'Sub123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sector_name" => [
                "The sector name format is invalid."
            ],
            "sub_sector_name" => [
                "The sub sector name format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in sector name
     * 
     * @return void
     */
    public function testForSectorNameFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        ['sector_name' => 'Malay123$', 
        'sub_sector_name' => 'Sector']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sector_name" => [
                "The sector name format is invalid."
            ]
            ]
        ]);
    }
        /**
     * Functional test to validate format for fields in sub sector name
     * 
     * @return void
     */
    public function testForSubSectorNameFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', array_merge($this->creationData(), 
        ['sector_name' => 'Test', 
        'sub_sector_name' => 'Sub123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "sub_sector_name" => [
                "The sub sector name format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate Sector Updation
     * 
     * @return void
     */
    public function testForSectorUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/update', array_merge($this->updationData(), 
        ['id' => '','sector_name' => '', 'sub_sector_name' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "sector_name" => [
                    "The sector name field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create Sector
     */
    public function testForCreateSector(): void
    {
        $response = $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'id',
                'sector_name',
                'sub_sector_name',
                'checklist_status',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            ]
        ]);
    }
    /**
     * Functional test for update Sector
     */
    public function testForUpdateSector(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/update', $this->updationData(), $this->getHeader(false));
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
     * Functional test for delete Sector
     */
    public function testForDeleteSector(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/delete', ['id' => 1], $this->getHeader(false));
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
     * Functional test to list Sectors
     */
    public function testForListingSectorsWithSearch(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/list', ['search_param' => ''], $this->getHeader(false));
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
     * Functional test to view Sector Required Validation
     */
    public function testForViewSectorRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/show', ['id' => ''], $this->getHeader());
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
     * Functional test to view Sector
     */
    public function testForViewSector(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'sector_name',
                    'sub_sector_name',
                    'checklist_status',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
    /**
     * Functional test for Sector dropdown
     */
    public function testForSectorDropdown(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/dropDown', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data"
        ]);
    }
        /**
     * Functional test to update status for sector Required Validation
     */
    public function testForUpdateSectorStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/updateStatus', ['id' => '','status' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to update status for sector Format/MinMax Validation
     */
    public function testForUpdateSectorStatusFormatAndMinMaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/sector/updateStatus', ['id' => 1,'status' => 12], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "status" => [
                    "The status format is invalid.",
                    "The status must not be greater than 1 characters."
                ],
            ]
        ]);
    }
    /**
     * Functional test for update sector Status
     */
    public function testForUpdateSectorStatus(): void
    {
        $this->json('POST', 'api/v1/sector/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/sector/updateStatus', ['id' => 1, 'status' => 1], $this->getHeader(false));
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
     * @return array
     */
    public function creationData(): array
    {
        return ['sector_name' => 'Agriculture', 'sub_sector_name' => 'Agriculture'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'sector_name' => 'Agriculture', 'sub_sector_name' => 'Agriculture'];
    }
}

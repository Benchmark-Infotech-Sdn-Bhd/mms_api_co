<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class RolesUnitTest extends TestCase
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
     * Functional test to validate name in Role creation
     * 
     * @return void
     */
    public function testForRoleCreationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', array_merge($this->creationData(), ['name' => 'Admin123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * Functional test to validate Role Updation
     * 
     * @return void
     */
    public function testForRoleUpdationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/update', array_merge($this->updationData(), ['name' => 'Admin$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * Functional test to validate Role Updation
     * 
     * @return void
     */
    public function testForRoleUpdationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['id' => ['The id field is required.']]
        ]);
    }
    /**
     * Functional test for create Role
     */
    public function testForCreateRole(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Created Successfully']
        ]);
    }
    /**
     * Functional test for update Role
     */
    public function testForUpdateRole(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Updated Successfully']
        ]);
    }
    /**
     * Functional test to list Roles
     */
    public function testForListingRolesWithSearch(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $this->json('POST', 'api/v1/role/create', ['name' => 'HR'], $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/role/list', ['search' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
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
     * Functional test to view Role
     */
    public function testForViewRole(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'id',
                    'role_name',
                    'system_role',
                    'status',
                    'parent_id',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
        /**
     * Functional test to update status for Role Required Validation
     */
    public function testForUpdateRoleStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => '','status' => ''], $this->getHeader());
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
     * Functional test to update status for Role Format/MinMax Validation
     */
    public function testForUpdateRoleStatusFormatAndMinMaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => 1,'status' => 12], $this->getHeader());
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
     * Functional test for update role Status
     */
    public function testForUpdateRoleStatus(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => 1, 'status' => 1], $this->getHeader(false));
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
        return ['name' => 'Admin'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'name' => 'Admin'];
    }
}
<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class AccessManagementUnitTest extends TestCase
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
     * Functional test to validate role id in role access creation.
     *
     * @return void
     */
    public function testForRoleAccessCreationRoleIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/accessManagement/create', array_merge($this->accessCreationData(), ['role_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['role_id' => ['The role id field is required.']]
        ]);
    }
    /**
     * Functional test to validate modules in role access creation.
     *
     * @return void
     */
    public function testForRoleAccessCreationModulesValidation(): void
    {
        $response = $this->json('POST', 'api/v1/accessManagement/create', array_merge($this->accessCreationData(), ['modules' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['modules' => ['The modules field is required.']]
        ]);
    }
    /**
     * Functional test to validate role id in role access updation.
     *
     * @return void
     */
    public function testForRoleAccessUpdationRoleIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/accessManagement/update', array_merge($this->accessUpdationData(), ['role_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['role_id' => ['The role id field is required.']]
        ]);
    }
    /**
     * Functional test to validate modules in role access updation.
     *
     * @return void
     */
    public function testForRoleAccessUpdationModulesValidation(): void
    {
        $response = $this->json('POST', 'api/v1/accessManagement/update', array_merge($this->accessUpdationData(), ['modules' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['modules' => ['The modules field is required.']]
        ]);
    }
    /**
     * Functional test to create role access.
     *
     * @return void
     */
    public function testForCreateRoleAccess(): void
    {
        $response = $this->json('POST', 'api/v1/accessManagement/create', $this->accessCreationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Permission Created Successfully']
        ]);
    }
    /**
     * Functional test to update role access.
     *
     * @return void
     */
    public function testForUpdateRoleAccess(): void
    {
        $this->json('POST', 'api/v1/accessManagement/create', $this->accessCreationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/accessManagement/update', $this->accessUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Permission Updated Successfully']
        ]);
    }
    /**
     * Functional test to list modules based on role.
     *
     * @return void
     */
    public function testForListModules(): void
    {
        $this->json('POST', 'api/v1/accessManagement/create', $this->accessCreationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/accessManagement/list', ['role_id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
    }
    /**
     * @param bool $artisan
     * @return array
     */
    public function accessCreationData(bool $artisan = true): array
    {
        if($artisan === true) {
            $this->artisan("db:seed --class=ModuleSeeder");
            $this->json('POST', 'api/v1/role/create', ['name' => 'HR'], $this->getHeader());
        }
        return ['role_id' => 1, 'modules' => [1,2,3]];
    }
    /**
     * @return array
     */
    public function accessUpdationData(): array
    {
        return ['role_id' => 1, 'modules' => [4,5]];
    }
}
<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class ModulesUnitTest extends TestCase
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
        $this->moduleTableSeed();
        $response = $this->json('POST', 'api/v1/module/dropDown', [], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * @return void
     */
    public function moduleTableSeed(): void
    {
        $this->artisan("db:seed --class=ModuleSeeder");
    }
}
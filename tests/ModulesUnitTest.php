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
     * Functional test to list modules in a dropdown
     * 
     * @return void
     */
    public function testForDropdownModules(): void
    {
        $response = $this->json('POST', 'api/v1/module/dropDown', [], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test to list features in a dropdown
     * 
     * @return void
     */
    public function testForDropdownFeatures(): void
    {
        $response = $this->json('POST', 'api/v1/module/featureDropDown', [], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
}
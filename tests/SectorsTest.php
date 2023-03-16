<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class SectorsTest extends TestCase
{
    private Generator $faker;
    // Sector Enum
    private $sectors = ["Agriculture","Construction","Electrical","Plumbing"];
    /**
     * A test method for create new sector.
     *
     * @return void
     */
    public function testNewSector()
    {
        $this->faker = Factory::create();
        $payload =  [
            'sector_name' => $this->sectors[array_rand($this->sectors,1)],
            'sub_sector_name' => $this->faker->text()
        ];
        $response = $this->post('/api/v1/sector/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'result' =>
                [
                    'headers',
                    'original' => [
                        "error",
                        "statusCode",
                        "statusMessage",
                        "data",
                        "responseTime"
                    ],
                    'exception'
                ]
        ]);
    }
    /**
     * A test method for update existing sector.
     *
     * @return void
     */
    public function testUpdateSector()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 5,
            'sector_name' => $this->sectors[array_rand($this->sectors,1)],
            'sub_sector_name' => $this->faker->text()
        ];
        $response = $this->put('/api/v1/sector/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'result' =>
                [
                    'headers',
                    'original' => [
                        "error",
                        "statusCode",
                        "statusMessage",
                        "data",
                        "responseTime"
                    ],
                    'exception'
                ]
        ]);
    }
    /**
     * A test method for delete existing sector.
     *
     * @return void
     */
    public function testDeleteSector()
    {
        $payload =  [
            'id' => 5
        ];
        $response = $this->post('/api/v1/sector/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'result' =>
                [
                    'headers',
                    'original' => [
                        "error",
                        "statusCode",
                        "statusMessage",
                        "data",
                        "responseTime"
                    ],
                    'exception'
                ]
        ]);
    }
    /**
     * A test method for retrieve all sectors.
     *
     * @return void
     */
    public function testShouldReturnAllSectors()
    {
        $response = $this->get("/api/v1/sector/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'result' =>
                [
                    'headers',
                    'original' => [
                        "error",
                        "statusCode",
                        "statusMessage",
                        "data",
                        "responseTime"
                    ],
                    'exception'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific Sector.
     *
     * @return void
     */
    public function testShouldReturnSpecificSector()
    {
        $response = $this->post("/api/v1/sector/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'result' =>
                [
                    'headers',
                    'original' => [
                        "error",
                        "statusCode",
                        "statusMessage",
                        "data",
                        "responseTime"
                    ],
                    'exception'
                ]
        ]);
    }
}

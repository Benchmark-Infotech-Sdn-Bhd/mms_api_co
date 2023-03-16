<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class CountriesTest extends TestCase
{
    private Generator $faker;
    // System type
    private $systemTypes = ["Embassy","FWCMS"];
    /**
     * A test method for create new country.
     *
     * @return void
     */
    public function testNewCountry()
    {
        $this->faker = Factory::create();
        $payload =  [
            'country_name' => $this->faker->country,
            'system_type' => $this->systemTypes[array_rand($this->systemTypes,1)],
            'fee' => random_int(10, 1000)
        ];
        $response = $this->post('/api/v1/country/create',$payload);
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
     * A test method for update existing country.
     *
     * @return void
     */
    public function testUpdateCountry()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 5,
            'country_name' => $this->faker->country,
            'system_type' => $this->systemTypes[array_rand($this->systemTypes,1)],
            'fee' => random_int(10, 1000)
        ];
        $response = $this->put('/api/v1/country/update',$payload);
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
     * A test method for delete existing country.
     *
     * @return void
     */
    public function testDeleteCountry()
    {
        $payload =  [
            'id' => 5
        ];
        $response = $this->post('/api/v1/country/delete',$payload);
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
     * A test method for retrieve all countries.
     *
     * @return void
     */
    public function testShouldReturnAllCountries()
    {
        $response = $this->get("/api/v1/country/retrieveAll");
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
     * A test method for retrieve specific country.
     *
     * @return void
     */
    public function testShouldReturnSpecificCountry()
    {
        $response = $this->post("/api/v1/country/retrieve",['id' => 1]);
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

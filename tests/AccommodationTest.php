<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class AccommodationTest extends TestCase
{    
    protected Generator $faker;
    /**
     * A test method for create new Accommodation.
     *
     * @return void
     */
    public function testCreateAccommodation()
    {
        $this->faker = Factory::create();
        $payload =  [
             'name' => $this->faker->name,
             'location' => $this->faker->address,
             'maximum_pax_per_unit' => random_int(10, 1000),
             'deposit' => random_int(10, 1000),
             'rent_per_month' => random_int(10, 1000),
             'vendor_id' => 1
        ];
        $response = $this->post('/api/v1/accommodation/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'name',
                    'location',
                    'maximum_pax_per_unit',
                    'deposit',
                    'rent_per_month',
                    'vendor_id',
                ]
        ]);
    }
    /**
     * A test method for update accommodation.
     *
     * @return void
     */
    public function testUpdateAccommodation()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 1,
            'name' => $this->faker->name,
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1
        ];
        $response = $this->post('/api/v1/accommodation/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve all accommodation.
     *
     * @return void
     */
    public function testRetrieveAllAccommodation()
    {
        $response = $this->get("/api/v1/accommodation/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ],
        ]);
    }
    /**
     * A test method for retrieve specific accommodation.
     *
     * @return void
     */
    public function testRetrieveSpecificAccommodation()
    {
        $response = $this->post("/api/v1/accommodation/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'name',
                    'location',
                    'maximum_pax_per_unit',
                    'deposit',
                    'rent_per_month',
                    'vendor_id',
                ]
        ]);
    }
    /**
     * A test method for delete existing accommodation.
     *
     * @return void
     */
    public function testDeleteAccommodation()
    {
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/accommodation/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
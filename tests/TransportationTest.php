<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class TransportationTest extends TestCase
{
    
    protected Generator $faker;
    /**
     * A test method for create new transportation.
     *
     * @return void
     */
    public function testCreateTransportation()
    {
        $this->faker = Factory::create();
        $payload =  [
             'driver_name' => $this->faker->name,
             'driver_contact_number' => random_int(10, 1000),
             'driver_license_number' => random_int(10, 1000),
             'vehicle_type' => 'type',
             'number_plate' => random_int(10, 1000),
             'vehicle_capacity' => random_int(10, 1000),
             'vendor_id' => 1
        ];
        $response = $this->post('/api/v1/transportation/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);

    }
    /**
     * A test method for update transportation.
     *
     * @return void
     */
    public function testUpdateTransportation()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 1,
            'driver_name' => $this->faker->name,
            'driver_contact_number' => random_int(10, 1000),
            'driver_license_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
        ];
        $response = $this->put('/api/v1/transportation/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for retrieve all transportation.
     *
     * @return void
     */
    public function testRetrieveAllTransportation()
    {
        $response = $this->get("/api/v1/transportation/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific transportation.
     *
     * @return void
     */
    public function testRetrieveSpecificTransportation()
    {
        $response = $this->post("/api/v1/transportation/retrieve",['id' => 2]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }

    /**
     * A test method for delete existing transportation.
     *
     * @return void
     */
    public function testDeleteTransportation()
    {
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/transportation/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
}
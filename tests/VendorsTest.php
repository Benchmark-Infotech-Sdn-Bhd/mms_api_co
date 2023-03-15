<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class VendorsTest extends TestCase
{
    
    private Generator $faker;
    /**
     * A test method for create new vendor.
     *
     * @return void
     */
    public function testCreateVendor()
    {
        $this->faker = Factory::create();
        $payload =  [
             'name' => $this->faker->name,
             'type' => 'type',
             'email_address' => $this->faker->unique()->safeEmail,
             'contact_number' => random_int(10, 1000),
             'person_in_charge' => 'test',
             'pic_contact_number' => random_int(10, 1000),
             'address' => $this->faker->address,
             'state' => $this->faker->state,
             'city' => $this->faker->city,
             'postcode' => random_int(10, 1000),
             'remarks' => 'test',
        ];
        $response = $this->post('/api/v1/vendor/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for update vendor.
     *
     * @return void
     */
    public function testUpdateVendor()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 1,
            'name' => $this->faker->name,
             'type' => 'type',
             'email_address' => $this->faker->unique()->safeEmail,
             'contact_number' => random_int(10, 1000),
             'person_in_charge' => 'test',
             'pic_contact_number' => random_int(10, 1000),
             'address' => $this->faker->address,
             'state' => $this->faker->state,
             'city' => $this->faker->city,
             'postcode' => random_int(10, 1000),
             'remarks' => 'test',
        ];
        $response = $this->post('/api/v1/vendor/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve all Vendor.
     *
     * @return void
     */
    public function testRetrieveAllVendors()
    {
        $response = $this->get("/api/v1/vendor/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific Vendor.
     *
     * @return void
     */
    public function testRetrieveSpecificVendor()
    {
        $response = $this->post("/api/v1/vendor/retrieve",['id' => 12]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for delete existing vendor.
     *
     * @return void
     */
    public function testDeleteVendor()
    {
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/vendor/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
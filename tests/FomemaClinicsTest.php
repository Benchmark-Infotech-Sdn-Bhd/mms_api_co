<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class FomemaClinicsTest extends TestCase
{
    protected Generator $faker;
    /**
     * A test method for create new Fomema Clinics.
     *
     * @return void
     */
    public function testCreateFomemaClinics()
    {
        $this->faker = Factory::create();
        $payload =  [
             'clinic_name' => $this->faker->name,
             'person_in_charge' => $this->faker->name,
             'pic_contact_number' => random_int(10, 1000),
             'address' => $this->faker->address,
             'state' => $this->faker->state,
             'city' => $this->faker->city,
             'postcode' => random_int(10, 1000),
        ];

        $response = $this->post('/api/v1/fomemaClinics/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'clinic_name',
                    'person_in_charge',
                    'pic_contact_number',
                    'address',
                    'state',
                    'city',
                    'postcode',
                ]
        ]);
    }
    /**
     * A test method for update Fomema Clinics.
     *
     * @return void
     */
    public function testUpdateFomemaClinics()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 1,
            'clinic_name' => $this->faker->name,
            'person_in_charge' => $this->faker->name,
            'pic_contact_number' => random_int(10, 1000),
            'address' => $this->faker->address,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'postcode' => random_int(10, 1000),
        ];
        $response = $this->put('/api/v1/fomemaClinics/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve all Fomema Clinics.
     *
     * @return void
     */
    public function testRetrieveAllFomemaClinics()
    {
        $response = $this->get("/api/v1/fomemaClinics/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific Fomema Clinics.
     *
     * @return void
     */
    public function testRetrieveSpecificFomemaClinics()
    {
        $response = $this->post("/api/v1/fomemaClinics/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'clinic_name',
                    'person_in_charge',
                    'pic_contact_number',
                    'address',
                    'state',
                    'city',
                    'postcode'
                ]
        ]);
    }
    /**
     * A test method for delete existing Fomema Clinics.
     *
     * @return void
     */
    public function testDeleteFomemaClinics()
    {
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/fomemaClinics/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
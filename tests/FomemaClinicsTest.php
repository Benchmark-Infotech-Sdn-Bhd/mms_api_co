<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class FomemaClinicsTest extends TestCase
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
     * A test method for validate clinic name
     * 
     * @return void
     */
    public function testClinicNameValidation(): void
    {
        $payload =  [
            'clinic_name' => '',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['clinic_name']
        ]);
    }
    /**
     * A test method for validate pic
     * 
     * @return void
     */
    public function testPICValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => '',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['person_in_charge']
        ]);
    }
    /**
     * A test method for validate pic contact number
     * 
     * @return void
     */
    public function testPICContactNumberValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => '',
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['pic_contact_number']
        ]);
    }
    /**
     * A test method for validate address
     * 
     * @return void
     */
    public function testAddressValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => '',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['address']
        ]);
    }
    /**
     * A test method for state
     * 
     * @return void
     */
    public function testStateValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => '',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['state']
        ]);
    }
    /**
     * A test method for city
     * 
     * @return void
     */
    public function testCityValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => '',
            'postcode' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['city']
        ]);
    }
    /**
     * A test method for postcode
     * 
     * @return void
     */
    public function testPostcodeValidation(): void
    {
        $payload =  [
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => '',
       ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['postcode']
        ]);
    }
    /**
     * A test method for create new Fomema Clinics.
     *
     * @return void
     */
    public function testCreateFomemaClinics()
    {
        $payload =  [
             'clinic_name' => 'name',
             'person_in_charge' => 'incharge',
             'pic_contact_number' => random_int(10, 1000),
             'address' => 'address',
             'state' => 'state',
             'city' => 'city',
             'postcode' => random_int(10, 1000),
        ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/create', $payload, $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
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
        $payload =  [
            'id' => 1,
            'clinic_name' => 'name',
            'person_in_charge' => 'incharge',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
        ];
        $response = $this->json('PUT', 'api/v1/fomemaClinics/update', $payload, $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
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
        $payload =  [
            'search_param' => '',
        ];
        $response = $this->json('POST', 'api/v1/fomemaClinics/list', $payload, $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/fomemaClinics/show', ['id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
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
        $response = $this->post('/api/v1/fomemaClinics/delete',$payload, $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
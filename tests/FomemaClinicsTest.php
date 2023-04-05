<?php

namespace Tests;

class FomemaClinicsTest extends TestCase
{
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
        $payload =  [
            'search' => '',
        ];
        $response = $this->post("/api/v1/fomemaClinics/list", $payload);
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
        $response = $this->post("/api/v1/fomemaClinics/show",['id' => 1]);
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
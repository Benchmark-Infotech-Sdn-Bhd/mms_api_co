<?php

namespace Tests;

class VendorsTest extends TestCase
{
    /**
     * A test method for create new vendor.
     *
     * @return void
     */
    public function testCreateVendor()
    {
        $payload =  [
             'name' => 'name',
             'type' => 'type',
             'email_address' => 'email@gmail.com',
             'contact_number' => random_int(10, 1000),
             'person_in_charge' => 'test',
             'pic_contact_number' => random_int(10, 1000),
             'address' => 'address',
             'state' => 'state',
             'city' => 'city',
             'postcode' => random_int(10, 1000),
             'remarks' => 'test',
        ];
        $response = $this->post('/api/v1/vendor/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'name',
                    'type',
                    'email_address',
                    'contact_number',
                    'person_in_charge',
                    'pic_contact_number',
                    'address',
                    'state',
                    'city',
                    'postcode',
                    'remarks',
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
        $payload =  [
            'id' => 1,
            'name' => 'name',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
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
        $payload =  [
            'search' => '',
        ];
        $response = $this->post("/api/v1/vendor/list",$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
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
        $response = $this->post("/api/v1/vendor/show",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'name',
                    'type',
                    'email_address',
                    'contact_number',
                    'person_in_charge',
                    'pic_contact_number',
                    'address',
                    'state',
                    'city',
                    'postcode',
                    'remarks',
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
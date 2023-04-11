<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class VendorsTest extends TestCase
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
     * A test method for validate name 
     * 
     * @return void
     */
    public function testVendorNameValidation(): void
    {
        $payload =  [
            'name' => '',
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
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['name']
        ]);
    }
    /**
     * A test method for validate type 
     * 
     * @return void
     */
    public function testVendorTypeValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => '',
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
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['type']
        ]);
    }
    /**
     * A test method for validate email address 
     * 
     * @return void
     */
    public function testVendorEmailValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => '',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['email_address']
        ]);
    }
    /**
     * A test method for validate contact number 
     * 
     * @return void
     */
    public function testVendorContactNumberValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => '',
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['contact_number']
        ]);
    }
    /**
     * A test method for validate PIC 
     * 
     * @return void
     */
    public function testVendorPICValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => '',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['person_in_charge']
        ]);
    }
    /**
     * A test method for validate PIC contact number 
     * 
     * @return void
     */
    public function testVendorPICContactNumberValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => '',
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['pic_contact_number']
        ]);
    }
    /**
     * A test method for validate Address
     * 
     * @return void
     */
    public function testVendorAddressValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => '',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['address']
        ]);
    }
    /**
     * A test method for validate state
     * 
     * @return void
     */
    public function testVendorStateValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => '',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['state']
        ]);
    }
    /**
     * A test method for validate city
     * 
     * @return void
     */
    public function testVendorCityValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => '',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['city']
        ]);
    }
    /**
     * A test method for validate postcode
     * 
     * @return void
     */
    public function testVendorPincodeValidation(): void
    {
        $payload =  [
            'name' => 'test',
            'type' => 'type',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => '',
            'remarks' => 'test',
       ];
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['postcode']
        ]);
    }
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
        $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/vendor/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/vendor/list', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
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
        $response = $this->json('POST', 'api/v1/vendor/list', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'data',
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
        $response = $this->post('/api/v1/vendor/delete', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class TransportationTest extends TestCase
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
     * A test method for validate driver name 
     * 
     * @return void
     */
    public function testDriverNameValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'driver_name' => '',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['driver_name']
        ]);
    }
    /**
     * A test method for validate driver contact number 
     * 
     * @return void
     */
    public function testDriverContactNumberValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'driver_name' => 'name',
            'driver_contact_number' => '',
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['driver_contact_number']
        ]);
    }
    /**
     * A test method for validate Vehicle type 
     * 
     * @return void
     */
    public function testVehicleTypeValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => '',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['vehicle_type']
        ]);
    }
    /**
     * A test method for validate number plate 
     * 
     * @return void
     */
    public function testNumberPlateValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => '',
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['number_plate']
        ]);
    }
    /**
     * A test method for validate vehicle capacity 
     * 
     * @return void
     */
    public function testVehicleCapacityValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => '',
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['vehicle_capacity']
        ]);
    }
    /**
     * A test method for create new transportation.
     *
     * @return void
     */
    public function testCreateTransportation()
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
             'driver_name' => 'name',
             'driver_contact_number' => random_int(10, 1000),
             'vehicle_type' => 'type',
             'number_plate' => random_int(10, 1000),
             'vehicle_capacity' => random_int(10, 1000),
             'vendor_id' => 1
        ];
        $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'driver_name',
                    'driver_contact_number',
                    'vehicle_type',
                    'number_plate',
                    'vehicle_capacity',
                    'vendor_id',
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
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $this->json('POST', 'api/v1/transportation/create', $this->creationTransportationData(), $this->getHeader());
        $payload =  [
            'id' => 1,
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
        ];
        $response = $this->json('POST', 'api/v1/transportation/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
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
        $response = $this->json('POST', 'api/v1/transportation/list', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/transportation/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
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
        $response = $this->json('POST', 'api/v1/transportation/delete', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }

        /**
     * A test method for delete existing transportation Attachment.
     *
     * @return void
     */
    public function testDeleteTransportationAttachments()
    {
        $response = $this->json('POST', 'api/v1/transportation/deleteAttachment', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    
    /**
     * @return array
     */
    public function creationVendorData(): array
    {
        return [
            'name' => 'test',
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
    }
    /**
     * @return array
     */
    public function creationTransportationData(): array
    {
        return [
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
       ];
    }
}
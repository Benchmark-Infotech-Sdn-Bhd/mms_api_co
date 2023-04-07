<?php

namespace Tests;

class TransportationTest extends TestCase
{
    /**
     * A test method for create new transportation.
     *
     * @return void
     */
    public function testCreateTransportation()
    {
        $payload =  [
             'driver_name' => 'name',
             'driver_contact_number' => random_int(10, 1000),
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
        $payload =  [
            'id' => 2,
            'driver_name' => 'name',
            'driver_contact_number' => random_int(10, 1000),
            'vehicle_type' => 'type',
            'number_plate' => random_int(10, 1000),
            'vehicle_capacity' => random_int(10, 1000),
            'vendor_id' => 1
        ];
        $response = $this->post('/api/v1/transportation/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $response = $this->post("/api/v1/transportation/list",['id' => 1]);
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
        $response = $this->post("/api/v1/transportation/show",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/transportation/deleteAttachment',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
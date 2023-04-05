<?php

namespace Tests;

class AccommodationTest extends TestCase
{ 
    /**
     * A test method for create new Accommodation.
     *
     * @return void
     */
    public function testCreateAccommodation()
    {
        $payload =  [
             'name' => 'test',
             'location' => 'test',
             'maximum_pax_per_unit' => random_int(10, 1000),
             'deposit' => random_int(10, 1000),
             'rent_per_month' => random_int(10, 1000),
             'vendor_id' => 1,
             'tnb_bill_account_Number' => random_int(10, 1000),
             'water_bill_account_Number' => random_int(10, 1000),
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
                    'tnb_bill_account_Number',
                    'water_bill_account_Number',
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
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
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
        $response = $this->post("/api/v1/accommodation/list",['vendor_id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ],
        ]);
    }
    /**
     * A test method for retrieve all accommodation.
     *
     * @return void
     */
    public function testSearchAccommodation()
    {
        $response = $this->post("/api/v1/accommodation/list",['vendor_id' => 1,'search' => 'test']);
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
        $response = $this->post("/api/v1/accommodation/show",['id' => 1]);
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
                    'tnb_bill_account_Number',
                    'water_bill_account_Number',
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
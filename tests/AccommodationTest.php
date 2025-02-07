<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AccommodationTest extends TestCase
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
    public function testAccommodationNameValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => '',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['name']
        ]);
    }
    /**
     * A test method for validate location
     * 
     * @return void
     */
    public function testAccommodationLocationValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => '',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['location']
        ]);
    }
    /**
     * A test method for validate maximum pax per unit
     * 
     * @return void
     */
    public function testAccommodationMaxPerPaxValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => '',
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['maximum_pax_per_unit']
        ]);
    }
    /**
     * A test method for validate deposit
     * 
     * @return void
     */
    public function testAccommodationDepositValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => '',
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['deposit']
        ]);
    }
    /**
     * A test method for validate Rent Per Month
     * 
     * @return void
     */
    public function testAccommodationRentPerMonthValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => '',
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['rent_per_month']
        ]);
    }
    /**
     * A test method for validate TNB bill
     * 
     * @return void
     */
    public function testAccommodationTnbBillFieldValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['tnb_bill_account_Number']
        ]);
    }
    /**
     * A test method for validate Water bill
     * 
     * @return void
     */
    public function testAccommodationWaterBillFieldValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => '',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['water_bill_account_Number']
        ]);
    }
    /**
     * A test method for create new Accommodation.
     *
     * @return void
     */
    public function testCreateAccommodation()
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $this->json('POST', 'api/v1/accommodation/create',  $this->creationAccommodationData(), $this->getHeader());
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
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/accommodation/list', ['vendor_id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/accommodation/list', ['vendor_id' => 1,'search_param' => 'test'], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/accommodation/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message',
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
        $response = $this->json('POST', 'api/v1/accommodation/delete', ['id' => 1], $this->getHeader());
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
    public function creationAccommodationData(): array
    {
        return [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
    }
}
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name field is required.']]
        ]);
    }
    /**
     * A test method for validate name format
     * 
     * @return void
     */
    public function testAccommodationNameFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'AccOne123',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * A test method for validate name size 
     * 
     * @return void
     */
    public function testAccommodationNameSizeValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'AccOnehfdkjsdhgdfhkjgndfjbhjvfsvnkadjsbfksjdhfkjdsbgjfkdhsdhguyggadshgfsjghdfkjghshfjghjkshfgdfjbgjkdsfbgjkwahfoiweuropljasfkhsdjgbfjsdhgdfsuighwjhfiuhiuhfgijksbhskjfgbkhsjfjasiljfhsdjgkhgierhtioguqpdjklahlfkwehngliwrhfijelrhjbfdkshuwkufhkjsadghfwuriklSNDJBSAVHBSDMHFGVDFHGFJGV',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name must not be greater than 150 characters.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location field is required.']]
        ]);
    }
    /**
     * A test method for validate location format
     * 
     * @return void
     */
    public function testAccommodationLocationFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'Kl85r8',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location format is invalid.']]
        ]);
    }
    /**
     * A test method for validate location size
     * 
     * @return void
     */
    public function testAccommodationLocationSizeValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'Klnfckndsjkvdfjkvnkjdfnjgsdlfmklsdnvijdfknzsdbhfbdhgvhsdbhfbhdsfvhjbvsdhfbfvhjdfbgdfhbghdfgjbadsnfbdsjhbfhhdsbfdbhfbdshbhsdbhfjhfkjdsnv nxcbvhjbgffhsfgukrhfsdghsjfhkncvksdjvniosfdjgkdfsngjndfkjbndfbvhjdfiugvnkjsnvjsb jvhsdfjkvnkjsbfvjdskfjvbdshvhjvnkj',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location must not be greater than 150 characters.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['maximum_pax_per_unit' => ['The maximum pax per unit field is required.']]
        ]);
    }
    /**
     * A test method for validate maximum pax per unit format
     * 
     * @return void
     */
    public function testAccommodationMaxPerPaxFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => '34DSFC',
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['maximum_pax_per_unit' => ['The maximum pax per unit format is invalid.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['deposit' => ['The deposit field is required.']]
        ]);
    }
    /**
     * A test method for validate deposit format
     * 
     * @return void
     */
    public function testAccommodationDepositFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => '100hfdg',
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['deposit' => ['The deposit format is invalid.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['rent_per_month' => ['The rent per month field is required.']]
        ]);
    }
     /**
     * A test method for validate Rent Per Month format
     * 
     * @return void
     */
    public function testAccommodationRentPerMonthFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => '100jsndjids',
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['rent_per_month' => ['The rent per month format is invalid.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number field is required.']]
        ]); 
    }
    /**
     * A test method for validate TNB bill format
     * 
     * @return void
     */
    public function testAccommodationTnbBillFieldFormatValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '7487jshfj',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number format is invalid.']]
        ]); 
    }
    /**
     * A test method for validate TNB bill size
     * 
     * @return void
     */
    public function testAccommodationTnbBillFieldSizeValidation(): void
    {
        $this->json('POST', 'api/v1/vendor/create', $this->creationVendorData(), $this->getHeader());
        $payload =  [
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '5475637465837458738456435',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number must not be greater than 12 characters.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number field is required.']]
        ]);
    }
    /**
     * A test method for validate Water bill format
     * 
     * @return void
     */
    public function testAccommodationWaterBillFieldFormatValidation(): void
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
            'water_bill_account_Number' => '6367bdj',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number format is invalid.']]
        ]);
    }
    /**
     * A test method for validate Water bill size
     * 
     * @return void
     */
    public function testAccommodationWaterBillFieldSizeValidation(): void
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
            'water_bill_account_Number' => '4738568734563458734897586875678756',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number must not be greater than 13 characters.']]
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
        $response = $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));
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
     * A test method for update accomodation validate id
     * 
     * @return void
     */
    public function testUpdateAccommodationIDValidation(): void
    {
        $payload =  [
            'id' => '',
            'name' => 'ACC One',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['id' => ['The id field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate name
     * 
     * @return void
     */
    public function testUpdateAccommodationNameValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => '',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate name format
     * 
     * @return void
     */
    public function testUpdateAccommodationNameFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'AccOne123',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate name size 
     * 
     * @return void
     */
    public function testUpdateAccommodationNameSizeValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'AccOnehfdkjsdhgdfhkjgndfjbhjvfsvnkadjsbfksjdhfkjdsbgjfkdhsdhguyggadshgfsjghdfkjghshfjghjkshfgdfjbgjkdsfbgjkwahfoiweuropljasfkhsdjgbfjsdhgdfsuighwjhfiuhiuhfgijksbhskjfgbkhsjfjasiljfhsdjgkhgierhtioguqpdjklahlfkwehngliwrhfijelrhjbfdkshuwkufhkjsadghfwuriklSNDJBSAVHBSDMHFGVDFHGFJGV',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name must not be greater than 150 characters.']]
        ]);
    }
    /**
     * A test method for update accomodation validate location
     * 
     * @return void
     */
    public function testUpdateAccommodationLocationValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => '',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate location format
     * 
     * @return void
     */
    public function testUpdateAccommodationLocationFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'Kl85r8',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate location size
     * 
     * @return void
     */
    public function testUpdateAccommodationLocationSizeValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'Klnfckndsjkvdfjkvnkjdfnjgsdlfmklsdnvijdfknzsdbhfbdhgvhsdbhfbhdsfvhjbvsdhfbfvhjdfbgdfhbghdfgjbadsnfbdsjhbfhhdsbfdbhfbdshbhsdbhfjhfkjdsnv nxcbvhjbgffhsfgukrhfsdghsjfhkncvksdjvniosfdjgkdfsngjndfkjbndfbvhjdfiugvnkjsnvjsb jvhsdfjkvnkjsbfvjdskfjvbdshvhjvnkj',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['location' => ['The location must not be greater than 150 characters.']]
        ]);
    }
    /**
     * A test method for update accomodation validate maximum pax per unit
     * 
     * @return void
     */
    public function testUpdateAccommodationMaxPerPaxValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => '',
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['maximum_pax_per_unit' => ['The maximum pax per unit field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate maximum pax per unit format
     * 
     * @return void
     */
    public function testUpdateAccommodationMaxPerPaxFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => '34DSFC',
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['maximum_pax_per_unit' => ['The maximum pax per unit format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate deposit
     * 
     * @return void
     */
    public function testUpdateAccommodationDepositValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => '',
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['deposit' => ['The deposit field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate deposit format
     * 
     * @return void
     */
    public function testUpdateAccommodationDepositFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => '100hfdg',
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['deposit' => ['The deposit format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate Rent Per Month
     * 
     * @return void
     */
    public function testUpdateAccommodationRentPerMonthValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => '',
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['rent_per_month' => ['The rent per month field is required.']]
        ]);
    }
     /**
     * A test method for update accomodation validate Rent Per Month format
     * 
     * @return void
     */
    public function testUpdateAccommodationRentPerMonthFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => '100jsndjids',
            'vendor_id' => 1,
            'tnb_bill_account_Number' => random_int(10, 1000),
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['rent_per_month' => ['The rent per month format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate TNB bill
     * 
     * @return void
     */
    public function testUpdateAccommodationTnbBillFieldValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number field is required.']]
        ]); 
    }
    /**
     * A test method for update accomodation validate TNB bill format
     * 
     * @return void
     */
    public function testUpdateAccommodationTnbBillFieldFormatValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '7487jshfj',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number format is invalid.']]
        ]); 
    }
    /**
     * A test method for update accomodation validate TNB bill size
     * 
     * @return void
     */
    public function testUpdateAccommodationTnbBillFieldSizeValidation(): void
    {
        $payload =  [
            'id' => 1,
            'name' => 'test',
            'location' => 'test',
            'maximum_pax_per_unit' => random_int(10, 1000),
            'deposit' => random_int(10, 1000),
            'rent_per_month' => random_int(10, 1000),
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '5475637465837458738456435',
            'water_bill_account_Number' => random_int(10, 1000),
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['tnb_bill_account_Number' => ['The tnb bill account  number must not be greater than 12 characters.']]
        ]); 
    }
    /**
     * A test method for update accomodation validate Water bill
     * 
     * @return void
     */
    public function testUpdateAccommodationWaterBillFieldValidation(): void
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
            'water_bill_account_Number' => '',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number field is required.']]
        ]);
    }
    /**
     * A test method for update accomodation validate Water bill format
     * 
     * @return void
     */
    public function testUpdateAccommodationWaterBillFieldFormatValidation(): void
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
            'water_bill_account_Number' => '6367bdj',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number format is invalid.']]
        ]);
    }
    /**
     * A test method for update accomodation validate Water bill size
     * 
     * @return void
     */
    public function testUpdateAccommodationWaterBillFieldSizeValidation(): void
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
            'water_bill_account_Number' => '4738568734563458734897586875678756',
       ];
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['water_bill_account_Number' => ['The water bill account  number must not be greater than 13 characters.']]
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
        $this->json('POST', 'api/v1/accommodation/create',  $this->creationAccommodationData(), $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/accommodation/update', $payload, $this->getHeader(false));
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
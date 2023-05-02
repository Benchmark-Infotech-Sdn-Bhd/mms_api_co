<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CountriesTest extends TestCase
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
     * Functional test to validate Required fields for Country creation
     * 
     * @return void
     */
    public function testForCountryCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/country/create', array_merge($this->creationData(), 
        ['country_name' => '', 'system_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
            "country_name" => [
                "The country name field is required."
            ],
            "system_type" => [
                "The system type field is required."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Country creation
     * 
     * @return void
     */
    public function testForCountryCreationMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/country/create', array_merge($this->creationData(), 
        ['country_name' => 'Malay Test fdtrddxrtdsrrtd hjuyrfds ygftrdrese erdsewswmmmmmmmmmmmkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv ewsw AAAAAAAAAAAAAAA', 
        'system_type' => 'ABC', 
        'fee' => 67, 
        'bond' => 6767]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "country_name" => [
                "The country name must not be greater than 150 characters."
            ],
            "bond" => [
                "The bond must not be greater than 3 characters."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in Country creation
     * 
     * @return void
     */
    public function testForCountryCreationFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/country/create', array_merge($this->creationData(), 
        ['country_name' => 'Malay123$', 
        'system_type' => 'Embassy', 
        'fee' => 67, 
        'bond' => null]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
            "country_name" => [
                "The country name format is invalid."
            ],
            "bond" => [
                "The bond format is invalid."
            ]
            ]
        ]);
    }
    /**
     * Functional test to validate country Updation
     * 
     * @return void
     */
    public function testForCountryUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/update', array_merge($this->updationData(), 
        ['id' => '', 'country_name' => '', 'system_type' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "country_name" => [
                    "The country name field is required."
                ],
                "system_type" => [
                    "The system type field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create Country
     */
    public function testForCreateCountry(): void
    {
        $response = $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'id',
                'country_name',
                'system_type',
                'costing_status',
                'fee',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at',
                'bond'
            ]
        ]);
    }
    /**
     * Functional test for update Country
     */
    public function testForUpdateCountry(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * Functional test for delete Country
     */
    public function testForDeleteCountry(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isDeleted',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to list Countries
     */
    public function testForListingCountriesWithSearch(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/list', ['search_param' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
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
     * Functional test to view Country Required Validation
     */
    public function testForViewCountryRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/country/show', ['id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to view Country
     */
    public function testForViewCountry(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'country_name',
                    'system_type',
                    'costing_status',
                    'fee',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'bond'
                ]
        ]);
    }
    /**
     * Functional test for Country dropdown
     */
    public function testForCountryDropdown(): void
    {
        $this->json('POST', 'api/v1/country/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/country/dropDown', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data"
        ]);
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10];
    }
}

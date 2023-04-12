<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class FeeRegistrationTest extends TestCase
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
     * A test method for validate item name 
     * 
     * @return void
     */
    public function testFeeRegItemNameValidation(): void
    {
        $payload =  [
            'item_name' => '',
            'cost' => '15',
            'fee_type' => 'Proposal',
            'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
            'sectors' => [1,2,3],
       ];
        $response = $this->json('POST', 'api/v1/feeRegistration/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['item_name']
        ]);
    }

    /**
     * A test method for validate cost
     * 
     * @return void
     */
    public function testFeeRegCostValidation(): void
    {
        $payload =  [
            'item_name' => 'test',
            'cost' => '',
            'fee_type' => 'Proposal',
            'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
            'sectors' => [1,2,3],
       ];
        $response = $this->json('POST', 'api/v1/feeRegistration/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['cost']
        ]);
    }

    /**
     * A test method for validate fee type
     * 
     * @return void
     */
    public function testFeeRegFeeTypeValidation(): void
    {
        $payload =  [
            'item_name' => 'Test',
            'cost' => '15',
            'fee_type' => '',
            'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
            'sectors' => [1,2,3],
       ];
        $response = $this->json('POST', 'api/v1/feeRegistration/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['fee_type']
        ]);
    }

    /**
     * A test method for create new Fee Registration.
     *
     * @return void
     */
    public function testCreateFeeRegistration()
    {
        $payload =  [
             'item_name' => 'Uplabs',
             'cost' => '15',
             'fee_type' => 'Proposal',
             'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
             'sectors' => [1,2,3],
        ];
        $response = $this->json('POST', 'api/v1/feeRegistration/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "item_name",
                    "cost",
                    "fee_type"
                ]
        ]);
    }
    /**
     * A test method for update Fee Registration.
     *
     * @return void
     */
    public function testUpdateFeeRegistration()
    {
        $payload =  [
            'id' => '1',
            'item_name' => 'Uplabs',
            'cost' => '15',
            'fee_type' => 'Monthly',
            'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
            'sectors' => [1,2,3],
        ];
        $response = $this->json('PUT', 'api/v1/feeRegistration/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "message"
                ]
        ]);
    }
    /**
     * A test method for retrieve all Fee Registration.
     *
     * @return void
     */
    public function testRetrieveAllFeeRegistration()
    {
        $payload =  [
            'search' => '',
        ];
        $response = $this->json('POST', 'api/v1/feeRegistration/list', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "data"
                ]
        ]);
    }
    /**
     * A test method for retrieve specific Fee Registration.
     *
     * @return void
     */
    public function testRetrieveSpecificFeeRegistration()
    {
        $response = $this->json('POST', 'api/v1/feeRegistration/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "data"
                ]
        ]);
    }
    /**
     * A test method for delete existing Fee Registration.
     *
     * @return void
     */
    public function testDeleteFeeRegistration()
    {
        $response = $this->json('POST', 'api/v1/feeRegistration/delete', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for search Fee Registration.
     *
     * @return void
     */
    public function testFeeRegistrationSearch()
    {
        $payload =  [
            'search' => 'test',
        ];
        $response = $this->json('POST', 'api/v1/feeRegistration/list', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "data"
                ]
        ]);
    }
    /**
     * A test method for filter the Fee Registration.
     *
     * @return void
     */
    public function testFeeRegistrationFilter()
    {
        $payload =  [
            'filter' => 'Proposal',
        ];
        $response = $this->json('POST', 'api/v1/feeRegistration/list', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    "data"
                ]
        ]);
    }
}
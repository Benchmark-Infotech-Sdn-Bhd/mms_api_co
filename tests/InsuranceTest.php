<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class InsuranceTest extends TestCase
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
     * A test method for validate no of worker from
     * 
     * @return void
     */
    public function testNoOfWorkerFromValidation(): void
    {
        $payload =  [
            'no_of_worker_from' => '',
            'no_of_worker_to' => random_int(10, 1000),
            'fee_per_pax' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['no_of_worker_from']
        ]);
    }
    /**
     * A test method for validate no of worker to
     * 
     * @return void
     */
    public function testNoOfWorkerToValidation(): void
    {
        $payload =  [
            'no_of_worker_from' => random_int(10, 1000),
            'no_of_worker_to' => '',
            'fee_per_pax' => random_int(10, 1000),
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['no_of_worker_to']
        ]);
    }
    /**
     * A test method for validate fee per pax
     * 
     * @return void
     */
    public function testFeePerPaxValidation(): void
    {
        $payload =  [
            'no_of_worker_from' => random_int(10, 1000),
            'no_of_worker_to' => random_int(10, 1000),
            'fee_per_pax' => '',
            'vendor_id' => 1
       ];
        $response = $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['fee_per_pax']
        ]);
    }
    /**
     * A test method for create new insurance.
     *
     * @return void
     */
    public function testCreateInsurance()
    {
        $payload =  [
             'no_of_worker_from' => random_int(10, 1000),
             'no_of_worker_to' => random_int(10, 1000),
             'fee_per_pax' => random_int(10, 1000),
             'vendor_id' => 1
        ];
        $response = $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message',
                ]
        ]);
    }
    /**
     * A test method for update insurance.
     *
     * @return void
     */
    public function testUpdateInsurance()
    {
        $payload =  [
            'id' => 1,
            'no_of_worker_from' => random_int(10, 1000),
            'no_of_worker_to' => random_int(10, 1000),
            'fee_per_pax' => random_int(10, 1000),
            'vendor_id' => 1
        ];
        $response = $this->json('PUT', 'api/v1/insurance/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve all insurance.
     *
     * @return void
     */
    public function testRetrieveallInsurance()
    {
        $response = $this->json('POST', 'api/v1/insurance/list', ['vendor_id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific insurance.
     *
     * @return void
     */
    public function testRetrieveSpecificInsurance()
    {
        $response = $this->json('POST', 'api/v1/insurance/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for delete existing insurance.
     *
     * @return void
     */
    public function testDeleteInsurance()
    {
        $response = $this->json('POST', 'api/v1/insurance/delete', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
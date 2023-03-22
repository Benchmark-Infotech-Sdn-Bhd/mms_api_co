<?php

namespace Tests;

class InsuranceTest extends TestCase
{
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

        $response = $this->post('/api/v1/insurance/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'no_of_worker_from',
                    'no_of_worker_to',
                    'fee_per_pax',
                    'vendor_id',
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
        $response = $this->put('/api/v1/insurance/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
    public function testRetrieveAllInsurance()
    {
        $response = $this->get("/api/v1/insurance/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $response = $this->post("/api/v1/insurance/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'no_of_worker_from',
                    'no_of_worker_to',
                    'fee_per_pax',
                    'vendor_id',
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
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/insurance/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
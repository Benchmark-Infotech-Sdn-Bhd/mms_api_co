<?php

namespace Tests;

class FeeRegistrationTest extends TestCase
{
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
             'fee_type' => 'Monthly',
             'applicable_for' => 'e-contract',
             'sectors' => 'Manufacturing',
        ];

        $response = $this->post('/api/v1/feeRegistration/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    "item_name",
                    "cost",
                    "fee_type",
                    "applicable_for",
                    "sectors"
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
            'applicable_for' => 'e-contract',
            'sectors' => 'Manufacturing',
        ];
        $response = $this->put('/api/v1/feeRegistration/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $response = $this->get("/api/v1/feeRegistration/retrieveAll");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $response = $this->post("/api/v1/feeRegistration/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    "id",
                    "item_name",
                    "cost",
                    "fee_type",
                    "applicable_for",
                    "sectors"
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
        $payload =  [
            'id' => 3
        ];
        $response = $this->post('/api/v1/feeRegistration/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
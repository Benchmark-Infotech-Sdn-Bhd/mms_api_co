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
             'fee_type' => 'Proposal',
             'applicable_for' => ["e-Contract","Total Management","Direct Recruitment"],
             'sectors' => [1,2,3],
        ];

        $response = $this->post('/api/v1/feeRegistration/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
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
        $payload =  [
            'search' => '',
        ];
        $response = $this->post("/api/v1/feeRegistration/list", $payload);
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
        $response = $this->post("/api/v1/feeRegistration/show",['id' => 2]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    "id",
                    "item_name",
                    "cost",
                    "fee_type"
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
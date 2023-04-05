<?php

namespace Tests;

class BranchesTest extends TestCase
{
    /**
     * A test method for create new Branch.
     *
     * @return void
     */
    public function testCreateBranch()
    {
        $payload =  [
             'branch_name' => 'test',
             'state' => 'state',
             'city' => 'city',
             'branch_address' => 'address',
             'postcode' => random_int(10, 1000),
             'service_type' => ["e-Contract","Total Management","Direct Recruitment"],
             'remarks' => 'test'
        ];    
        $response = $this->post('/api/v1/branch/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'branch_name',
                    'state',
                    'city',
                    'branch_address',
                    'postcode',
                    'remarks',
                ]
        ]);
    }
    /**
     * A test method for update Branch.
     *
     * @return void
     */
    public function testUpdateBranch()
    {
        $payload =  [
            'id' => 1,
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => ["e-Contract","Total Management","Direct Recruitment"],
            'remarks' => 'test'
        ];
        $response = $this->put('/api/v1/branch/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * A test method for retrieve all Branch.
     *
     * @return void
     */
    public function testRetrieveAllBranch()
    {
        $response = $this->post("/api/v1/branch/list");
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'data'
                ]
        ]);
    }
    /**
     * A test method for retrieve specific Branch.
     *
     * @return void
     */
    public function testRetrieveSpecificBranch()
    {
        $response = $this->post("/api/v1/branch/show",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'branch_name',
                    'state',
                    'city',
                    'branch_address',
                    'postcode',
                    'remarks',
                ]
        ]);
    }
    /**
     * A test method for delete existing Branch.
     *
     * @return void
     */
    public function testDeleteBranch()
    {
        $payload =  [
            'id' => 1
        ];
        $response = $this->post('/api/v1/branch/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}
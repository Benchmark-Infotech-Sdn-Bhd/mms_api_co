<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class BranchesTest extends TestCase
{
    protected Generator $faker;
    /**
     * A test method for create new Branch.
     *
     * @return void
     */
    public function testCreateBranch()
    {
        $this->faker = Factory::create();
        $payload =  [
             'branch_name' => $this->faker->name,
             'state' => $this->faker->state,
             'city' => $this->faker->city,
             'branch_address' => $this->faker->address,
             'postcode' => random_int(10, 1000),
             'service_type' => '1',
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
                    'service_type',
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
        $this->faker = Factory::create();
        $payload =  [
            'id' => 1,
            'branch_name' => $this->faker->name,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'branch_address' => $this->faker->address,
            'postcode' => random_int(10, 1000),
            'service_type' => '1',
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
        $response = $this->get("/api/v1/branch/retrieveAll");
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
        $response = $this->post("/api/v1/branch/retrieve",['id' => 1]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'branch_name',
                    'state',
                    'city',
                    'branch_address',
                    'postcode',
                    'service_type',
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
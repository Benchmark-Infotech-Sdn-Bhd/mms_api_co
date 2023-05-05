<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BranchesTest extends TestCase
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
    public function testBranchNameValidation(): void
    {
        $payload =  [
            'branch_name' => '',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['branch_name']
        ]);
    }
    /**
     * A test method for validate state
     * 
     * @return void
     */
    public function testBranchStateValidation(): void
    {
        $payload =  [
            'branch_name' => 'test',
            'state' => '',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['state']
        ]);
    }
    /**
     * A test method for validate city
     * 
     * @return void
     */
    public function testBranchCityValidation(): void
    {
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => '',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['city']
        ]);
    }
    /**
     * A test method for validate address
     * 
     * @return void
     */
    public function testBranchAddressValidation(): void
    {
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => '',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['branch_address']
        ]);
    }
    /**
     * A test method for validate postcode
     * 
     * @return void
     */
    public function testBranchPostcodeValidation(): void
    {
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => '',
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(422);
        $this->response->assertJsonStructure([
            'data' => ['postcode']
        ]);
    }
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
             'service_type' => [1,2,3],
             'remarks' => 'test'
        ];    
        $response = $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $payloadCreate =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
       ];    
       $this->json('POST', 'api/v1/branch/create', $payloadCreate, $this->getHeader());
        $payload =  [
            'id' => 1,
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
        ];
        $response = $this->json('POST', 'api/v1/branch/update', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $payload =  [
            'search_param' => '',
        ];
        $response = $this->json('POST', 'api/v1/branch/list', $payload, $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
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
        $response = $this->json('POST', 'api/v1/branch/show', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'data'
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
        $response = $this->json('POST', 'api/v1/branch/delete', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }

    /**
     * A test method for all active branch names dropdown.
     *
     * @return void
     */
    public function testdropDownBranch()
    {
        $response = $this->json('POST', 'api/v1/branch/dropDown', [], $this->getHeader());
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
}
<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class ForgotPasswordUnitTest extends TestCase
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
     * Functional test for forgot password email mandatory field validation 
     * 
     * @return void
     */
    public function testForforgotPasswordEmailRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/forgotPassword', ['email' => '']);
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for forgot password email field validation 
     * 
     * @return void
     */
    public function testForforgotPasswordEmailValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/forgotPassword', ['email' => 'test123@gmail.com']);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * Functional test for forgot password mail send 
     * 
     * @return void
     */
    public function testForforgotPassword(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/forgotPassword', ['email' => 'test@gmail.com']);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
    /**
     * @return array
     */
    public function creationSeeder(): void
    {
        $this->json('POST', 'api/v1/role/create', ['name' => 'Admin'], $this->getHeader());
        $payload =  [
            'name' => 'test', 
            'email' => 'test@gmail.com', 
            'password' => 'Welcome@123', 
            'reference_id' => 1, 
            'user_type' => 'Admin',
            'role_id' => 1,
            'status' => 1
        ];
        $this->json('POST', 'api/v1/user/register', $payload, $this->getHeader(false));
    }
    
}

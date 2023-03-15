<?php

namespace Tests;

class UserLoginTest extends TestCase
{
    protected $email;

    public function setUp(): void
    {
        parent::setUp();    
        $this->email = $this->createEmail();
    }

    /**
     * A test method for user login
     *
     * @return void
     */
    public function testUserLogin()
    {
        $payload =  [
            'email' => 'valarmathi@codtesma.com',
            'password' => 'Test1234$'
        ];
        $response = $this->post('/api/v1/login', $payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'token',
                    'user',
                    'token_type',
                    'expires_in',
                ]
        ]);
    }

    /**
     * A test method for user login
     *
     * @return void
     */
    public function testUserLoginWithInvalidCredentials()
    {
        $payload =  [
            'email' =>  $this->email,
            'password' => '12345'
        ];
        $response = $this->post('/api/v1/login', $payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}

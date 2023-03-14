<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserLoginTest extends TestCase
{
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
        // dd($response);
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
            'email' => 'valarmathi@codtesma.com',
            'password' => 'Test1234$$'
        ];
        $response = $this->post('/api/v1/login', $payload);
        // dd($response);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' =>
                [
                    'message'
                ]
        ]);
    }
}

<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * @param bool $artisan
     * @return array
     */
    public function getHeader(bool $artisan = true): array
    {
        $header = [];
        $header['Accept'] = 'application/json';
        $header['Authorization'] = 'Bearer '.$this->getToken($artisan);
        return $header;
    }
    /**
     * @param bool $artisan
     * @return array
     */
    public function getSuperHeader(bool $artisan = true): array
    {
        $header = [];
        $header['Accept'] = 'application/json';
        $header['Authorization'] = 'Bearer '.$this->getSuperAdminToken($artisan);
        return $header;
    }

    /**
     * @param bool $artisan
     * @return mixed
     */
    public function getToken(bool $artisan = true): mixed
    {
        if($artisan === true) {
            $this->artisan("db:seed --class=unit_testing_company");
            $this->artisan("db:seed --class=unit_testing_user");
            $this->artisan("db:seed --class=unit_testing_modules");
            $this->artisan("db:seed --class=unit_testing_user_company_modules");
        }
        $response = $this->call('POST', 'api/v1/login', ['email' => 'unittest@gmail.com', 'password' => 'Welcome@123']);
        $this->assertEquals(200, $response->status());
        return $response['data']['token'];
    }
    /**
     * @param bool $artisan
     * @return mixed
     */
    public function getSuperAdminToken(bool $artisan = true): mixed
    {
        if($artisan === true) {
            $this->artisan("db:seed --class=unit_testing_super_admin");
            $this->artisan("db:seed --class=unit_testing_modules");
        }
        $response = $this->call('POST', 'api/v1/login', ['email' => 'unittest@gmail.com', 'password' => 'Welcome@123']);
        $this->assertEquals(200, $response->status());
        return $response['data']['token'];
    }
}

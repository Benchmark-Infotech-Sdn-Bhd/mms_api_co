<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Faker\Factory;
use Faker\Generator;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var Generator
     */   
    protected Generator $faker;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    //Helper Methods

    /**
     * Creates the email.
     *
     * @return string
     */
    protected function createEmail(): string 
    {
        $this->faker = Factory::create();
        $this->faker->seed(1234);
        return $this->faker->email;
    }
}

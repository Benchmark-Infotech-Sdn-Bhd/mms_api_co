<?php

namespace Tests;
use Faker\Factory;
use Faker\Generator;

class EmbassyAttestationFileCostingTest extends TestCase
{
    protected Generator $faker;
    /**
     * A test method for create new EmbassyAttestationFileCosting.
     *
     * @return void
     */
    public function testNewEmbassyAttestationFileCosting()
    {
        $this->faker = Factory::create();
        $payload =  [
            'country_id' => 1,
            'title' => $this->faker->text(),
            'amount' => random_int(10, 1000)
        ];
        $response = $this->post('/api/v1/embassyAttestationFile/create',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for update existing EmbassyAttestationFileCosting.
     *
     * @return void
     */
    public function testUpdateEmbassyAttestationFileCosting()
    {
        $this->faker = Factory::create();
        $payload =  [
            'id' => 2,
            'country_id' => 1,
            'title' => $this->faker->text(),
            'amount' => random_int(10, 1000)
        ];
        $response = $this->put('/api/v1/embassyAttestationFile/update',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for delete existing EmbassyAttestationFileCosting.
     *
     * @return void
     */
    public function testDeleteEmbassyAttestationFileCosting()
    {
        $payload =  [
            'id' => 3
        ];
        $response = $this->post('/api/v1/embassyAttestationFile/delete',$payload);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
    /**
     * A test method for retrieve EmbassyAttestationFileCosting based on country.
     *
     * @return void
     */
    public function testShouldReturnEmbassyAttestationFileCostingByCountry()
    {
        $response = $this->post("/api/v1/embassyAttestationFile/retrieveByCountry",['country_id' => 2]);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            "error",
            "statusCode",
            "statusMessage",
            "data",
            "responseTime"
        ]);
    }
}

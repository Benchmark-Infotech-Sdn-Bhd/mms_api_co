<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AgentsTest extends TestCase
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
     * Functional test to validate Required fields for Agent creation
     * 
     * @return void
     */
    public function testForAgentCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => '', 'country_id' => '', 'city' => '', 'person_in_charge' => '',
        'pic_contact_number' => '', 'email_address' => '', 'company_address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "agent_name" => [
                    "The agent name field is required."
                ],
                "country_id" => [
                    "The country id field is required."
                ],
                "person_in_charge" => [
                    "The person in charge field is required."
                ],
                "pic_contact_number" => [
                    "The pic contact number field is required."
                ],
                "email_address" => [
                    "The email address field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Agent creation
     * 
     * @return void
     */
    public function testForAgentCreationMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ',
         'country_id' => 1, 
         'city' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu', 
         'person_in_charge' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdrter retyeyt etyuetesardkejjrkererrrrre',
         'pic_contact_number' => '9834736453465', 
         'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name must not be greater than 250 characters."
                ],
                "city" => [
                    "The city must not be greater than 150 characters."
                ],
                "person_in_charge" => [
                    "The person in charge must not be greater than 255 characters."
                ],
                "pic_contact_number" => [
                    "The pic contact number must not be greater than 11 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in Agent creation
     * 
     * @return void
     */
    public function testForAgentCreationFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => '12323Aer r4', 'country_id' => 1, 'city' => '12334@34fg r', 'person_in_charge' => 'Test',
        'pic_contact_number' => 'ABC', 'email_address' => 'test', 'company_address' => 'test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name format is invalid."
                ],
                "city" => [
                    "The city format is invalid."
                ],
                "pic_contact_number" => [
                    "The pic contact number format is invalid."
                ],
                "email_address" => [
                    "The email address must be a valid email address."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(), 
        ['id' => '','agent_name' => '', 'country_id' => '', 'city' => '', 'person_in_charge' => '',
        'pic_contact_number' => '', 'email_address' => '', 'company_address' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "agent_name" => [
                    "The agent name field is required."
                ],
                "country_id" => [
                    "The country id field is required."
                ],
                "person_in_charge" => [
                    "The person in charge field is required."
                ],
                "pic_contact_number" => [
                    "The pic contact number field is required."
                ],
                "email_address" => [
                    "The email address field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create Agent
     */
    public function testForCreateAgent(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $response = $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'id',
                'agent_name',
                'country_id',
                'city',
                'person_in_charge',
                'pic_contact_number',
                'email_address',
                'company_address',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            ]
        ]);
    }
    /**
     * Functional test for update Agent
     */
    public function testForUpdateAgent(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * Functional test for delete Agent
     */
    public function testForDeleteAgent(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isDeleted',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to list Agents
     */
    public function testForListingAgentsWithSearch(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/list', ['search_param' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
        ]);
    }
    /**
     * Functional test to view Agent Required Validation
     */
    public function testForViewAgentRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/show', ['id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to view Agent
     */
    public function testForViewAgent(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
                [
                    'id',
                    'agent_name',
                    'country_id',
                    'city',
                    'person_in_charge',
                    'pic_contact_number',
                    'email_address',
                    'company_address',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'countries'
                ]
        ]);
    }
    /**
     * Functional test to update status for Agent Required Validation
     */
    public function testForUpdateAgentStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/updateStatus', ['id' => '','status' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to update status for agent Format/MinMax Validation
     */
    public function testForUpdateAgentStatusFormatAndMinMaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/updateStatus', ['id' => 1,'status' => 12], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "status" => [
                    "The status format is invalid.",
                    "The status must not be greater than 1 characters."
                ],
            ]
        ]);
    }
    /**
     * Functional test for update agent Status
     */
    public function testForUpdateAgentStatus(): void
    {
        $this->json('POST', 'api/v1/country/create', ['country_name' => 'Malaysia', 'system_type' => 'Embassy', 'fee' => 350, 'bond' => 10], $this->getHeader());
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/updateStatus', ['id' => 1, 'status' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '9823477867', 'email_address' => 'test@gmail.com', 'company_address' => 'Test'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
        'pic_contact_number' => '9823477867', 'email_address' => 'test@gmail.com', 'company_address' => 'Test'];
    }
}

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
     * Functional test to validate Required fields for Agent name
     * 
     * @return void
     */
    public function testForAgentNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => '', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '9823477867', 'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "agent_name" => [
                    "The agent name field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for Country Id
     * 
     * @return void
     */
    public function testForCountryIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'Agent', 'country_id' => '', 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '9823477867', 'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "country_id" => [
                    "The country id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for PIC
     * 
     * @return void
     */
    public function testForPICRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => '',
    'pic_contact_number' => '9823477867', 'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "person_in_charge" => [
                    "The person in charge field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate Required fields for PIC Contact
     * 
     * @return void
     */
    public function testForPICContactRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '', 'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "pic_contact_number" => [
                    "The pic contact number field is required."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate Required fields for Email
     * 
     * @return void
     */
    public function testForEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '9823477867', 'email_address' => '', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "email_address" => [
                    "The email address field is required."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate Duplicate Email
     * 
     * @return void
     */
    public function testForEmailDuplicateValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationDuplicateEmailData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/create', $this->creationDuplicateEmailData(), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "email_address" => [
                    "The email address has already been taken."
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
     * Functional test to validate minimum/maximum characters for fields in Agent name
     * 
     * @return void
     */
    public function testForAgentNameMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ',
         'country_id' => 1, 
         'city' => 'ASG', 
         'person_in_charge' => 'ASGUYG',
         'pic_contact_number' => '98347364',
         'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name must not be greater than 250 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in City
     * 
     * @return void
     */
    public function testForCityMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ASGUYG',
         'country_id' => 1, 
         'city' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu', 
         'person_in_charge' => 'ASG',
         'pic_contact_number' => '983473',
         'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "city" => [
                    "The city must not be greater than 150 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in PIC
     * 
     * @return void
     */
    public function testForPICMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ASGUY',
         'country_id' => 1, 
         'city' => 'ASGUYGY', 
         'person_in_charge' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdrter retyeyt etyuetesardkejjrkererrrrre',
         'pic_contact_number' => '9834736', 
         'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "person_in_charge" => [
                    "The person in charge must not be greater than 255 characters."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate minimum/maximum characters for fields in PIC Contact
     * 
     * @return void
     */
    public function testForPICContactMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ASGUYG',
         'country_id' => 1, 
         'city' => 'ASGUYGY', 
         'person_in_charge' => 'ASGUYG',
         'pic_contact_number' => '9834736453465', 
         'email_address' => 'test@gmail.com', 'company_address' => 'Test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
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
     * Functional test to validate format for fields in Agent name
     * 
     * @return void
     */
    public function testForAgentNameFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => '12323Aer r4', 'country_id' => 1, 'city' => 'ABC', 'person_in_charge' => 'Test',
        'pic_contact_number' => '9843787', 'email_address' => 'test@gmail.com', 'company_address' => 'test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name format is invalid."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in City
     * 
     * @return void
     */
    public function testForCityFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => '12334@34fg r', 'person_in_charge' => 'Test',
        'pic_contact_number' => '9678676', 'email_address' => 'test@gmail.com', 'company_address' => 'test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "city" => [
                    "The city format is invalid."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in PIC Contact
     * 
     * @return void
     */
    public function testForPICContactFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'ABC', 'person_in_charge' => 'Test',
        'pic_contact_number' => 'ABC', 'email_address' => 'test@gmail.com', 'company_address' => 'test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "pic_contact_number" => [
                    "The pic contact number format is invalid."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate format for fields in Email
     * 
     * @return void
     */
    public function testForEmailFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/create', array_merge($this->creationData(), 
        ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'ABC', 'person_in_charge' => 'Test',
        'pic_contact_number' => '8656446', 'email_address' => 'test', 'company_address' => 'test']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
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
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
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
     * Functional test to validate Id in Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationIdValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['id' => '']), $this->getHeader(false));
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
     * Functional test to validate name Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationAgentNameValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['agent_name' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate country Id Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationCountryIdValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['country_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "country_id" => [
                    "The country id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate PIC in Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationPICValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['person_in_charge' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "person_in_charge" => [
                    "The person in charge field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate PIC Contact in Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationPicContactValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['pic_contact_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "pic_contact_number" => [
                    "The pic contact number field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate email in Agent Updation
     * 
     * @return void
     */
    public function testForAgentUpdationEmailValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['email_address' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "email_address" => [
                    "The email address field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate email in Agent Updation Email duplication
     * 
     * @return void
     */
    public function testForAgentUpdationEmailDuplicationValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationDuplicateEmailData(), $this->getHeader(false));
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['id' => 2,'email_address' => 'testduplicate@gmail.com']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "email_address" => [
                    "The email address has already been taken."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Agent name
     * 
     * @return void
     */
    public function testForAgentUpdationAgentNameMinMaxFieldValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['agent_name' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name must not be greater than 250 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in City
     * 
     * @return void
     */
    public function testForAgentUpdationCityMinMaxFieldValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['city' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "city" => [
                    "The city must not be greater than 150 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in PIC
     * 
     * @return void
     */
    public function testForAgentUpdationPICMinMaxFieldValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['person_in_charge' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdrter retyeyt etyuetesardkejjrkererrrrre']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "person_in_charge" => [
                    "The person in charge must not be greater than 255 characters."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate minimum/maximum characters for fields in PIC Contact
     * 
     * @return void
     */
    public function testForAgentUpdationPICContactMinMaxFieldValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['pic_contact_number' => '9834736453465']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "pic_contact_number" => [
                    "The pic contact number must not be greater than 11 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in Agent name
     * 
     * @return void
     */
    public function testForAgentUpdationAgentNameFieldFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['agent_name' => '12323Aer r4']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "agent_name" => [
                    "The agent name format is invalid."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in City
     * 
     * @return void
     */
    public function testForAgentUpdationCityFieldFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['city' => '12334@34fg r']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "city" => [
                    "The city format is invalid."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in PIC Contact Number
     * 
     * @return void
     */
    public function testForAgentUpdationPICContactNumberFieldFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['pic_contact_number' => 'ABC']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "pic_contact_number" => [
                    "The pic contact number format is invalid."
                ]
            ]
        ]);
    }
        /**
     * Functional test to validate format for fields in Email
     * 
     * @return void
     */
    public function testForAgentUpdationEmailFieldFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/update', array_merge($this->updationData(),
        ['email_address' => 'test']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "email_address" => [
                    "The email address must be a valid email address."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create Agent
     */
    public function testForCreateAgent(): void
    {
        $this->creationSeeder();
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
        $this->creationSeeder();
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
        $this->creationSeeder();
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
        $this->creationSeeder();
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
     * Functional test to view Agent Id Validation
     */
    public function testForViewAgentIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/agent/show', ['id' => 0], $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => [
                'message' => "Unauthorized"
            ]
        ]);
    }
    /**
     * Functional test to view Agent
     */
    public function testForViewAgent(): void
    {
        $this->creationSeeder();
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
        $this->creationSeeder();
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
     * Functional test for Agent dropdown
     */
    public function testForAgentDropdown(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/agent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/agent/dropdown', [], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data"
        ]);
    }
    /**
     * @return void
     */
    public function creationSeeder(): void
    {
        // $this->artisan("db:seed --class=unit_testing_company");
        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader());
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
    public function creationDuplicateEmailData(): array
    {
        return ['agent_name' => 'ABC', 'country_id' => 1, 'city' => 'CBE', 'person_in_charge' => 'ABC',
    'pic_contact_number' => '9823477867', 'email_address' => 'testduplicate@gmail.com', 'company_address' => 'Test'];
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

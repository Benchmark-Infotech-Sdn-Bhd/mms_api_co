<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class DirectRecruitmentOnboardingAttestationUnitTest extends TestCase
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
     * Functional test to List onboarding attestation
     * 
     * @return void
     */
    public function testToListOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/list', ['application_id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
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
     * Functional test to show onboarding attestation
     * 
     * @return void
     */
    public function testToShowOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/show', ['id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * Functional test to Update onboarding attestation mandatory field validation 
     * 
     * @return void
     */
    public function testToUpdateOnboardingAttestationIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/update', array_merge($this->attestationUpdateData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update onboarding attestation
     * 
     * @return void
     */
    public function testToUpdateOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/update', $this->attestationUpdateData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Attestation Updated Successfully']
        ]);
    }
    /**
     * Functional test to show dispatch onboarding attestation
     * 
     * @return void
     */
    public function testToShowDispatchOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/showDispatch', ['onboarding_attestation_id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * Functional test to Update dispatch onboarding attestation mandatory field validation 
     * 
     * @return void
     */
    public function testToUpdateDispatchOnboardingAttestationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/updateDispatch', array_merge($this->dipatchUpdateData(), ['onboarding_attestation_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_attestation_id' => ['The onboarding attestation id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update dispatch onboarding attestation
     * 
     * @return void
     */
    public function testToUpdateDispatchOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/updateDispatch', $this->dipatchUpdateData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Dispatch Updated Successfully']
        ]);
    }
    /**
     * Functional test to show embassy onboarding attestation
     * 
     * @return void
     */
    public function testToShowEmbassyOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/showEmbassyFile', ['onboarding_attestation_id' => 1, 'embassy_attestation_id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
     /**
     * @return void
     */
    public function creationSeeder(): void
    {
        $this->artisan("db:seed --class=ServiceSeeder");
        $this->artisan("db:seed --class=SystemTypeSeeder");
        $payload =  [
            'branch_name' => 'test',
            'state' => 'state',
            'city' => 'city',
            'branch_address' => 'address',
            'postcode' => random_int(10, 1000),
            'service_type' => [1,2,3],
            'remarks' => 'test'
        ];   
        $this->json('POST', 'api/v1/branch/create', $payload, $this->getHeader());

        $payload =  [
            'name' => 'HR'
        ];
        $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
       
        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => '1998-11-02', 
            'ic_number' => 222223434, 
            'passport_number' => 'ADI', 
            'email' => 'test@gmail.com', 
            'contact_number' => 238467,
            'address' => 'Addres', 
            'postcode' => 2344, 
            'position' => 'Position', 
            'branch_id' => 1,
            'role_id' => 1, 
            'salary' => 67.00, 
            'status' => 1, 
            'city' => 'ABC', 
            'state' => 'Malaysia'
        ];
        $this->json('POST', 'api/v1/employee/create', $payload, $this->getHeader(false));

        $payload =  [
            'sector_name' => 'Agriculture',
            'sub_sector_name' => 'Agriculture'
        ];  
        $this->json('POST', 'api/v1/sector/create', $payload, $this->getHeader(false));

        $payload = [
            'company_name' => 'ABC Firm', 
            'contract_type' => 'Zero Cost', 
            'roc_number' => 'APS6376', 
            'director_or_owner' => 'Test', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'address' => 'Coimbatore', 
            'pic_name' => 'PICTest', 
            'pic_contact_number' => '764859694', 
            'pic_designation' => 'Manager', 
            'registered_by' => 1, 
            'sector_type' => 1, 
            'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])
        ];
        $res = $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

        $payload = [
            'country_id' => 1, 
            'title' => 'Document', 
            'amount' => 10
        ];
        $this->json('POST', 'api/v1/embassyAttestationFile/create', $payload, $this->getHeader(false));

        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => '1998-11-02', 
            'ic_number' => 222223434, 
            'passport_number' => 'ADI', 
            'email' => 'test@gmail.com', 
            'contact_number' => 238467,
            'address' => 'Addres', 
            'postcode' => 2344, 
            'position' => 'Position', 
            'branch_id' => 1,
            'role_id' => 1, 
            'salary' => 67.00, 
            'status' => 1, 
            'city' => 'ABC', 
            'state' => 'Malaysia'
        ];
        $this->json('POST', 'api/v1/employee/create', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "country_id" => 1,
            "quota" => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $payload, $this->getHeader(false));

        $payload = [
            "agent_name" => 'ABC', 
            "country_id" => 1, 
            "city" => 'CBE', 
            "person_in_charge" => 'ABC',
            "pic_contact_number" => '9823477867', 
            "email_address" => 'test@gmail.com', 
            "company_address" => 'Test'
        ];
        $this->json('POST', 'api/v1/agent/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'quota' => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function attestationUpdateData(): array
    {
        return [
            "id" => 1,
            "submission_date" => "2023-06-06",
            "collection_date" => "2023-06-06",
            "file_url" => "google.com",
            "remarks" => "remarks testing"
        ];
    }
    /**
     * @return array
     */
    public function dipatchUpdateData(): array
    {
        return [
                "onboarding_attestation_id" => 1,
                "date" => "06/06/2023",
                "time" => "12:00 AM",
                "employee_id" => 1,
                "from" => "test",
                "calltime" => "12:00 AM",
                "area" => "Malaysia",
                "employer_name" => "test emp",
                "phone_number" => "02123456789",
                "remarks" => "remarks testing"
        ];
    }
}

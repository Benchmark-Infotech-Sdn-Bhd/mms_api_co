<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

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
     * Functional test to create onboarding attestation validaton
     * 
     * @return void
     */
    public function testToCreateOnboardingAttestationApplicationIdValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', array_merge($this->attestationCreateData(), ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to create onboarding attestation validaton
     * 
     * @return void
     */
    public function testToCreateOnboardingAttestationOnboardingCountryIdValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', array_merge($this->attestationCreateData(), ['onboarding_country_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_country_id' => ['The onboarding country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to create onboarding attestation validaton
     * 
     * @return void
     */
    public function testToCreateOnboardingAttestationKsmReferenceNumberValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', array_merge($this->attestationCreateData(), ['ksm_reference_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to create onboarding attestation
     * 
     * @return void
     */
    public function testToCreateOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Attestation Added Successfully']
        ]);
    }
    /**
     * Functional test to List onboarding attestation
     * 
     * @return void
     */
    public function testToListOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/list', ['application_id' => 1, 'onboarding_country_id' => 1], $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/showEmbassyFile', ['onboarding_attestation_id' => 1, 'embassy_attestation_id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data'
        ]);
    }
    /**
     * Functional test to list embassy onboarding attestation
     * 
     * @return void
     */
    public function testToListEmbassyOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/listEmbassy', ['onboarding_attestation_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
    ]);
    }
    /**
     * Functional test to upload EmbassyFile onboarding attestation validation
     * 
     * @return void
     */
    public function testToUploadEmbassyFileEmbassyOnboardingAttestationValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/uploadEmbassyFile', array_merge($this->UploadEmbassyData(), ['onboarding_attestation_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_attestation_id' => ['The onboarding attestation id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to upload EmbassyFile onboarding attestation
     * 
     * @return void
     */
    public function testToUploadEmbassyFileEmbassyOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/uploadEmbassyFile', $this->UploadEmbassyData(), $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
    ]);
    }
    /**
     * Functional test to delete EmbassyFile onboarding attestation
     * 
     * @return void
     */
    public function testToDeleteEmbassyFileEmbassyOnboardingAttestation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/create', $this->attestationCreateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/uploadEmbassyFile', $this->UploadEmbassyData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/deleteEmbassyFile', ['onboarding_embassy_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                []
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
            'name' => 'HR',
            'special_permission' => 0
        ];
        $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
       
        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'), 
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
            'state' => 'Malaysia',
            'subsidiary_companies' => []
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
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

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
            'id' => 1, 
            'crm_prospect_id' => 1, 
            'quota_applied' => 100, 
            'person_incharge' => 'test', 
            'cost_quoted' => 10.22, 
            'remarks' => 'test'
        ];
        
        $this->json('POST', 'api/v1/directRecrutment/submitProposal', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'application_id' => 1, 
            'item_name' => 'Document Checklist', 
            'application_checklist_status' => 'Completed', 
            'remarks' => 'test', 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationChecklist/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'submission_date' => Carbon::now()->format('Y-m-d'), 
            'applied_quota' => 25, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 25, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => Carbon::now()->format('Y-m-d'), 
            'payment_amount' => 10.87, 
            'approved_quota' => 25, 
            'ksm_reference_number' => 'My/643/7684548', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648000', 
            'remarks' => 'test create'
        ];
        $this->json('POST', 'api/v1/levy/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/992/095648000', 
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'country_id' => 1, 
            'ksm_reference_number' => 'My/992/095648000', 
            'valid_until' => Carbon::now()->format('Y-m-d'), 
            'quota' => 25
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'ksm_reference_number' => 'My/992/095648000',
            'quota' => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function attestationCreateData(): array
    {
        return [
            "application_id" => 1,
            "onboarding_country_id" => 1,
            "ksm_reference_number" => "My/992/095648000"
        ];
    }
    /**
     * @return array
     */
    public function attestationUpdateData(): array
    {
        return [
            "id" => 1,
            "submission_date" => Carbon::now()->format('Y-m-d'),
            "collection_date" => Carbon::now()->format('Y-m-d'),
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
                "date" => Carbon::now()->format('Y-m-d'),
                "time" => "12:00 AM",
                "employee_id" => 1,
                "from" => "test",
                "calltime" => Carbon::now()->format('Y-m-d'),
                "area" => "Malaysia",
                "employer_name" => "test emp",
                "phone_number" => "02123456789",
                "remarks" => "remarks testing"
        ];
    }
    /**
     * @return array
     */
    public function UploadEmbassyData(): array
    {
        return [
            "onboarding_attestation_id" => 1,
            "embassy_attestation_id" => 1,
            "amount" => "100",
            "attachment[]" => 'test.png'
        ];
    }
}

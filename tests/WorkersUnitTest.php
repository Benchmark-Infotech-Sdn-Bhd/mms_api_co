<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class WorkersUnitTest extends TestCase
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
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForWorkersCreateNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/create', array_merge($this->creationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'name' => ['The name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForWorkersCreatePassportValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/worker/create', array_merge($this->creationData(), ['passport_number' => 'PASS0001']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'passport_number' => ['The passport number has already been taken.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForWorkersCreateDOBValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/create', array_merge($this->creationData(), ['date_of_birth' => '11/11/2000']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'date_of_birth' => ['The date of birth does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForWorkersCreateAddressValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/create', array_merge($this->creationData(), ['address' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForWorkersCreateStateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/create', array_merge($this->creationData(), ['state' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state field is required.']
            ]
        ]);
    }

    /**
     * Functional test for worker create 
     * 
     * @return void
     */
    public function testForWorkersCreate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker update 
     * 
     * @return void
     */
    public function testForWorkersUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker show 
     * 
     * @return void
     */
    public function testForWorkersShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/show', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for workers listing
     * 
     * @return void
     */
    public function testForWorkersListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/list', ["search_param" => "","page" => 1], $this->getHeader(false));
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
     * Functional test for workers listing company Search
     * 
     * @return void
     */
    public function testForWorkersListingCompanySearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/list', ["search_param" => "","page" => 1, "crm_prospect_id" => 0], $this->getHeader(false));
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
     * Functional test for workers listing status Search
     * 
     * @return void
     */
    public function testForWorkersListingStatusSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/list', ["search_param" => "","page" => 1, "status" => "Assigned"], $this->getHeader(false));
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
     * Functional test for workers listing Search
     * 
     * @return void
     */
    public function testForWorkersListingSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/list', ["search_param" => "test","page" => 1], $this->getHeader(false));
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
     * Functional test for worker import 
     * 
     * @return void
     */
    /*public function testForWorkersImport(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/import', $this->importData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }*/
    /**
     * Functional test for worker export 
     * 
     * @return void
     */
    public function testForWorkersExport(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/export', $this->exportData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker drop down 
     * 
     * @return void
     */
    public function testForWorkersDropdown(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/dropdown', ["application_id" => 1,"onboarding_country_id" => 1,"agent_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker replace worker
     * 
     * @return void
     */
    public function testForWorkersreplaceWorker(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationDataTwo(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/replaceWorker', ["id" => 1,"replace_worker_id" => 2], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker status list
     * 
     * @return void
     */
    public function testForworkerStatusList(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/workerStatusList', ["application_id" => 1,"onboarding_country_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker onboarding agent
     * 
     * @return void
     */
    public function testForworkeronboardingAgent(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/onboardingAgent', ["application_id" => 1,"onboarding_country_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker attachment list
     * 
     * @return void
     */
    public function testForworkerattachmentList(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/listAttachment', ["worker_id" => 1,"page" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker attachment add
     * 
     * @return void
     */
    public function testForworkerattachmentAdd(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/addAttachment', ["worker_id" => 1,"attachment[]" => 'test.png'], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker attachment delete
     * 
     * @return void
     */
    public function testForworkerattachmentDelete(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/deleteAttachment', ["attachment_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker import history
     * 
     * @return void
     */
    public function testForworkerimportHistory(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/importHistory', ["page" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
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
            'name' => 'Supervisor',
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
            'name' => 'name',
            'type' => 'Transportation',
            'email_address' => 'email@gmail.com',
            'contact_number' => random_int(10, 1000),
            'person_in_charge' => 'test',
            'pic_contact_number' => random_int(10, 1000),
            'address' => 'address',
            'state' => 'state',
            'city' => 'city',
            'postcode' => random_int(10, 1000),
            'remarks' => 'test',
       ];
       $response = $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader(false));

       $payload =  [
        'driver_name' => 'name',
        'driver_contact_number' => random_int(10, 1000),
        'vehicle_type' => 'type',
        'number_plate' => random_int(10, 1000),
        'vehicle_capacity' => random_int(10, 1000),
        'vendor_id' => 1
   ];
   $response = $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader(false));

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
            'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "EContract"]])
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
            "country_name" => "India",
            "system_type" => "Embassy",
            "fee" => 500,
            "bond" => 25
        ];
        $this->json('POST', 'api/v1/country/create', $payload, $this->getHeader(false));

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
            'applied_quota' => 50, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 50, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => Carbon::now()->format('Y-m-d'), 
            'payment_amount' => 10.87, 
            'approved_quota' => 50, 
            'ksm_reference_number' => 'My/643/7684548', 
            'payment_reference_number' => 'SVZ498787', 
            'approval_number' => 'ADR4674', 
            'new_ksm_reference_number' => 'My/992/095648000', 
            'remarks' => 'test create'
        ];
        $this->json('POST', 'api/v1/levy/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->addYear()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'country_id' => 1, 
            'quota' => 20
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/countries/create', $payload, $this->getHeader(false));
        
        $payload = [
            'agent_name' => 'ABC', 
            'country_id' => 1, 
            'city' => 'CBE', 
            'person_in_charge' => 'ABC',
            'pic_contact_number' => '9823477867', 
            'email_address' => 'test@gmail.com', 
            'company_address' => 'Test'
        ];
        $this->json('POST', 'api/v1/agent/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'quota' => 20
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $payload, $this->getHeader(false));

        $payload = [
            "id" => 1,
            "submission_date" => Carbon::now()->format('Y-m-d'),
            "collection_date" => Carbon::now()->format('Y-m-d'),
            "file_url" => "google.com",
            "remarks" => "remarks testing"
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/attestation/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'agent_id' => 1,
            'name' => 'TestWorker',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Female',
            'passport_number' => 123456789154,
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 1234567890,
            'ksm_reference_number' => 'My/643/7684548',
            'calling_visa_reference_number' => '',
            'calling_visa_valid_until' => '',
            'entry_visa_valid_until' => '',
            'work_permit_valid_until' => '',
            'bio_medical_reference_number' => 'BIO1234567',
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'Test Clinic',
            'doctor_code' => 'Doc123',
            'allocated_xray' => 'Tst1234',
            'xray_code' => 'Xray1234',
            'ig_policy_number' => '',
            'ig_policy_number_valid_until' => '',
            'hospitalization_policy_number' => '',
            'hospitalization_policy_number_valid_until' => '',
            'bank_name' => 'Bank Name',
            'account_number' => 1234556678,
            'socso_number' => 12345678
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
            'name' => 'Testing worker one',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Male',
            'passport_number' => 'PASS0001',
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'ml',
            'city' => 'ml',
            'crm_prospect_id' => 0,
            'state' => 'ml',
            'fomema_attachment[]' => '',
            'passport_attachment[]' => '',
            'profile_picture[]' => '',
            'kin_name' => 'kin',
            'kin_relationship_id' => 1,
            'kin_contact_number' => '01234567899',
            'ksm_reference_number' => 'KSMREF001',
            'calling_visa_reference_number' => 'VISA001',
            'calling_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'entry_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'work_permit_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_visa_attachment[]' => '',
            'bio_medical_reference_number' => 012345,
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_bio_medical_attachment[]' => '',
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'abc',
            'doctor_code' => 'abc',
            'allocated_xray' => 'abc',
            'xray_code' => 'abc',
            'ig_policy_number' => 012345,
            'hospitalization_policy_number' => 012345,
            'insurance_expiry_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'bank_name' => 'bank',
            'account_number' => '0123456789',
            'socso_number' => 012345,
            'worker_attachment[]' => ''
        ];
    }
    /**
     * @return array
     */
    public function creationDataTwo(): array
    {
        return [
            'name' => 'Testing worker two',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Male',
            'passport_number' => 'PASS0002',
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'ml',
            'city' => 'ml',
            'crm_prospect_id' => 0,
            'state' => 'ml',
            'fomema_attachment[]' => '',
            'passport_attachment[]' => '',
            'profile_picture[]' => '',
            'kin_name' => 'kin',
            'kin_relationship_id' => 1,
            'kin_contact_number' => '01234567899',
            'ksm_reference_number' => 'KSMREF001',
            'calling_visa_reference_number' => 'VISA001',
            'calling_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'entry_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'work_permit_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_visa_attachment[]' => '',
            'bio_medical_reference_number' => 012345,
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_bio_medical_attachment[]' => '',
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'abc',
            'doctor_code' => 'abc',
            'allocated_xray' => 'abc',
            'xray_code' => 'abc',
            'ig_policy_number' => 012345,
            'hospitalization_policy_number' => 012345,
            'insurance_expiry_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'bank_name' => 'bank',
            'account_number' => '0123456789',
            'socso_number' => 012345,
            'worker_attachment[]' => ''
        ];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return [
            'id' => 1,
            'name' => 'Testing worker one update',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Male',
            'passport_number' => 'PASS0001',
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'ml',
            'city' => 'ml',
            'crm_prospect_id' => 0,
            'state' => 'ml',
            'fomema_attachment[]' => '',
            'passport_attachment[]' => '',
            'profile_picture[]' => '',
            'kin_name' => 'kin',
            'kin_relationship_id' => 1,
            'kin_contact_number' => '01234567899',
            'ksm_reference_number' => 'KSMREF001',
            'calling_visa_reference_number' => 'VISA001',
            'calling_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'entry_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'work_permit_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_visa_attachment[]' => '',
            'bio_medical_reference_number' => 012345,
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'worker_bio_medical_attachment[]' => '',
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'abc',
            'doctor_code' => 'abc',
            'allocated_xray' => 'abc',
            'xray_code' => 'abc',
            'ig_policy_number' => 012345,
            'hospitalization_policy_number' => 012345,
            'insurance_expiry_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'bank_name' => 'bank',
            'account_number' => '0123456789',
            'socso_number' => 012345,
            'worker_attachment[]' => ''
        ];
    }
    /**
     * @return array
     */
    public function importData(): array
    {
        return [
            "crm_prospect_id" => 0,
            "worker_file[]" => 'test.xlsx'
        ];
    }
    /**
     * @return array
     */
    public function exportData(): array
    {
        return [
            "search_param" => ''
        ];
    }
}

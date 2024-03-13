<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentWorkersUnitTest extends TestCase
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
    public function testForDirectRecruitmentWorkersCreateApplicationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreateOnboardingCountryIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['onboarding_country_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'onboarding_country_id' => ['The onboarding country id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreateAgentIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['agent_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'agent_id' => ['The agent id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreateNameValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['name' => '']), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersCreateDOBValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['date_of_birth' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'date_of_birth' => ['The date of birth field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreateDOBFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['date_of_birth' => Carbon::now()->subYear(25)->format('Y/m/d')]), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersCreateGenderValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['gender' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'gender' => ['The gender field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreatePassportNumberValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['passport_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'passport_number' => ['The passport number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreatePassportValidUntilValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['passport_valid_until' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'passport_valid_until' => ['The passport valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker create field validation 
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersCreateAddressValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['address' => '']), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersCreateStateValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', array_merge($this->creationData(), ['state' => '']), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersCreate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/update', $this->UpdationData(), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/show', ['id' => 1], $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/list', ["application_id" => 1,"onboarding_country_id" => 1,"search_param" => "","page" => 1], $this->getHeader(false));
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
     * Functional test for workers listing Agent Search
     * 
     * @return void
     */
    public function testForDirectRecruitmentWorkersListingAgentSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/list', ["application_id" => 1,"onboarding_country_id" => 1,"search_param" => "","page" => 1, "agent_id" => 1], $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersListingStatusSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/list', ["application_id" => 1,"onboarding_country_id" => 1,"search_param" => "","page" => 1, "status" => "Approved"], $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersListingSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/list', ["application_id" => 1,"onboarding_country_id" => 1,"search_param" => "test","page" => 1], $this->getHeader(false));
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
    /*public function testForDirectRecruitmentWorkersImport(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/import', $this->importData(), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersExport(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/export', $this->exportData(), $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersDropdown(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/dropdown', ["application_id" => 1,"onboarding_country_id" => 1,"agent_id" => 1], $this->getHeader(false));
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
    public function testForDirectRecruitmentWorkersreplaceWorker(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationDataTwo(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/replaceWorker', ["id" => 1,"replace_worker_id" => 2], $this->getHeader(false));
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
    public function testForDirectRecruitmentworkerStatusList(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/workerStatusList', ["application_id" => 1,"onboarding_country_id" => 1], $this->getHeader(false));
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
    public function testForDirectRecruitmentworkeronboardingAgent(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/onboardingAgent', ["application_id" => 1,"onboarding_country_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker kinRelationship
     * 
     * @return void
     */
    public function testForDirectRecruitmentworkerkinRelationship(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/kinRelationship', ["application_id" => 1,"onboarding_country_id" => 1], $this->getHeader(false));
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
    public function testForDirectRecruitmentworkerimportHistory(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/importHistory', ["page" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker failureExport
     * 
     * @return void
     */
    public function testForDirectRecruitmentworkerksfailureExport(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/failureExport', ["bulk_upload_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker ksm DropDownBased OnOnboardingAgent
     * 
     * @return void
     */
    public function testForDirectRecruitmentworkerksmDropDownBasedOnOnboardingAgent(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/ksmDropDownBasedOnOnboardingAgent', ["onboarding_country_id" => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker updateStatus
     * 
     * @return void
     */
    public function testForDirectRecruitmentworkerupdateStatus(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/updateStatus', ["id" => 1, "status" => 0], $this->getHeader(false));
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
            'ksm_reference_number' => 'My/992/095648000', 
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->addYear()->format('Y-m-d')
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
            'ksm_reference_number' => 'My/992/095648000',
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
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
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
            'ksm_reference_number' => 'My/992/095648000',
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
    }
    /**
     * @return array
     */
    public function creationDataTwo(): array
    {
        return [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'agent_id' => 1,
            'name' => 'TestWorkerTwo',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Female',
            'passport_number' =>'PASS0002',
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 1234567890,
            'ksm_reference_number' => 'My/992/095648000',
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
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return [
            'id' => 1,
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
            'ksm_reference_number' => 'My/992/095648000',
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
    }
    /**
     * @return array
     */
    public function importData(): array
    {
        return [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'agent_id' => 1,
            "worker_file" => ['test.xlsx']
        ];
    }
    /**
     * @return array
     */
    public function exportData(): array
    {
        return [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'agent_id' => 1,
            "search_param" => ''
        ];
    }
}

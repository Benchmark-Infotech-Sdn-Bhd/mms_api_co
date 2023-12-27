<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class WorkerEventUnitTest extends TestCase
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
     * Functional test for worker event create field validation 
     * 
     * @return void
     */
    public function testForWorkerEventCreateIDValidation(): void
    {
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', array_merge($this->creationData(), ['worker_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'worker_id' => ['The worker id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for worker event create 
     * 
     * @return void
     */
    public function testForWorkerEventCreate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker event update 
     * 
     * @return void
     */
    public function testForWorkerEventUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/workerEvent/update', $this->UpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker event show 
     * 
     * @return void
     */
    public function testForWorkerEventShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/workerEvent/show', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for worker event listing
     * 
     * @return void
     */
    public function testForWorkerEventListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/workerEvent/list', ["worker_id" => "1","filter" => "","page" => 1], $this->getHeader(false));
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
     * Functional test for worker event listing Filter
     * 
     * @return void
     */
    public function testForWorkerEventListingFilter(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/worker/workerEvent/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/worker/workerEvent/list', ["worker_id" => "1","filter" => "Counselling","page" => 1], $this->getHeader(false));
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
            'name' => 'Supervisor'
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
            'state' => 'Malaysia'
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
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));

        $payload = [
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
            'ksm_reference_number' => 'KSMREF002',
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
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'company_name' => 'ABC Firm', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'pic_name' => 'PICTest', 
            'sector' => 1, 
            'from_existing' => 0, 
            'client_quota' => 10, 
            'fomnext_quota' => 10, 
            'initial_quota' => 1, 
            'service_quota' => 1
        ];
        $this->json('POST', 'api/v1/totalManagement/addService', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "test name",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "employee_id" => 1,
            "transportation_provider_id" => 1,
            "driver_id" => 1,
            "assign_as_supervisor" => 0,
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10
        ];
        $this->json('POST', 'api/v1/totalManagement/project/add', $payload, $this->getHeader(false));

        $payload = [
            "project_id" => 1,
            "department" => "department",
            "sub_department" => "sub department",
            "accommodation_provider_id" => 0,
            "accommodation_unit_id" => 0,
            "work_start_date" =>  Carbon::now()->format('Y-m-d'),
            "workers" => [1]
        ];
        $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
            'worker_id' => 1,
            'project_id' => 1,
            'service_type' => 'Total Management',
            'event_date' => Carbon::now()->format('Y-m-d'),
            'event_type' => 'Counselling',
            'flight_number' => '', 
            'departure_date' => '', 
            'last_working_day' => '', 
            'remarks' => 'test',
            'attachment[]' => 'test.png'
        ];
    }
    /**
     * @return array
     */
    public function UpdationData(): array
    {
        return [
            'id' => 1,
            'worker_id' => 1,
            'project_id' => 1,
            'service_type' => 'Total Management',
            'event_date' => Carbon::now()->format('Y-m-d'),
            'event_type' => 'Counselling',
            'flight_number' => '', 
            'departure_date' => '', 
            'last_working_day' => '', 
            'remarks' => 'test',
            'attachment[]' => 'test.png'
        ];
    }
}

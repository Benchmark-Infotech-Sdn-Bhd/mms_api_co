<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DirectRecruitmentDispatchUnitTest extends TestCase
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
     * Functional test for Update Dispatch
     * 
     * @return void
     */
    /*public function testForUpdateDispatch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $this->updateData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Calling Visa Dispatch Updated Successfully']
        ]);
    }*/
    /**
     * Functional test for workers list
     * 
     * @return void
     */
    public function testForWorkersList(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $this->updateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/workersList', ["calling_visa_reference_number" => "AGTF/7637","ksm_reference_number"=> ""], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for workers list search
     * 
     * @return void
     */
    public function testForWorkersListSearch(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $this->updateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/workersList', ["calling_visa_reference_number" => "AGTF/7637","ksm_reference_number"=> "My/643/7684548"], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                ]
        ]);
    }
    /**
     * Functional test for list Based On CallingVisa
     * 
     * @return void
     */
    public function testForlistBasedOnCallingVisa(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $this->updateData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/listBasedOnCallingVisa', ["application_id"=> 1,"onboarding_country_id"=> 1,"agent_id"=> 1,"search"=> ""], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
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
            'contact_number' => '0123456789', 
            'email' => 'testcrm@gmail.com', 
            'address' => 'Coimbatore', 
            'pic_name' => 'PICTest', 
            'pic_contact_number' => '0123456789', 
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
        $response = $this->json('POST', 'api/v1/agent/create', $payload, $this->getHeader(false));

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
            'submission_date' => '2023-05-04', 
            'applied_quota' => 100, 
            'status' => 'Approved', 
            'ksm_reference_number' => 'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'ksm_reference_number' => 'My/643/7684548', 
            'schedule_date' => Carbon::now()->format('Y-m-d'), 
            'approved_quota' => 100, 
            'approval_date' => Carbon::now()->format('Y-m-d'),
            'status' => 'Approved',
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/applicationInterview/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'payment_date' => '2023-05-10', 
            'payment_amount' => 10.87, 
            'approved_quota' => 100, 
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
            'received_date' => '2023-05-13', 
            'valid_until' => '2023-06-13'
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
            'passport_number' => 12345678,
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 1234567890,
            'ksm_reference_number' => 'My/992/095648000',
            'bio_medical_reference_number' => '123456',
            'bio_medical_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'purchase_date' => Carbon::now()->format('Y-m-d'),
        ];

        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'calling_visa_reference_number' => '123456789', 
            'submitted_on' => Carbon::now()->format('Y-m-d'),
            'workers' => [1]
        ];
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/process/submitCallingVisa', $payload, $this->getHeader(false));

        $payload = [
            'name' => 'test',
            'type' => 'type',
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
       
       $payload = [
            'no_of_worker_from' => random_int(10, 1000),
            'no_of_worker_to' => random_int(10, 1000),
            'fee_per_pax' => random_int(10, 1000),
            'vendor_id' => 1
       ];
       $response = $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader(false));

       $payload = [
        "application_id"=> 1,
        "onboarding_country_id"=> 1,
        "ig_policy_number"=> 123456789,
        "hospitalization_policy_number"=> 123456789,
        "insurance_provider_id"=> 1,
        "ig_amount"=> "1000",
        "hospitalization_amount"=> "1000",
        "insurance_submitted_on"=> Carbon::now()->format('Y-m-d'),
        "insurance_expiry_date"=> Carbon::now()->addYear()->format('Y-m-d'),
        "workers"=> 1,
        "calling_visa_reference_number" => "123456789"
    ];
    $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/insurancePurchase/submit', $payload, $this->getHeader(false));

        $payload = [
            "application_id"=> 1,
            "onboarding_country_id"=> 1,
            "agent_id" => 1,
            "total_fee" => "999.99",
            "immigration_reference_number" => 123456789,
            "payment_date" => Carbon::now(),
            "attachment[]" => "/C:/Users/admin/Desktop/Accounting.png",
            "workers"=> 1,
        ];
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/immigrationFeePaid/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/generation/generatedStatusUpdate', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function updateData(): array
    {
        return [
            "application_id"=> 1,
            "onboarding_country_id"=> 1,
            "agent_id" => 1,
            "workers"=> 1,
            "dispatch_method" => "Courier",
            "dispatch_consignment_number" => 123456789,
            "dispatch_acknowledgement_number" => 12
        ];
    }
}

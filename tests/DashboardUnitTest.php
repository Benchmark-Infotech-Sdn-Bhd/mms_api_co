<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class DashboardUnitTest extends TestCase
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
     * Functional test for list
     * 
     * @return void
     */
    public function testList(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', '/api/v1/dashboard/list', [], $this->getHeader(false));
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
            'ksm_reference_number' => 'My/643/7684548', 
            'received_date' => '2023-05-13', 
            'valid_until' => '2023-06-13'
        ];
        $this->json('POST', 'api/v1/directRecruitmentApplicationApproval/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'country_id' => 1,
            'quota' => 10
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
            'quota' => 10
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/agent/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'calling_visa_reference_number' => 'AGTF/7637', 
            'submitted_on' => '2023-05-30', 
            'workers' => [1]
        ];
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/process/submitCallingVisa', $payload, $this->getHeader(false));

        $payload = [
            "application_id"=> 1,
            "onboarding_country_id"=> 1,
            "ig_policy_number"=> 123456789,
            "hospitalization_policy_number"=> 123456789,
            "insurance_provider_id"=> 1,
            "ig_amount"=> "1000.99",
            "hospitalization_amount"=> "2000.99",
            "insurance_submitted_on"=> Carbon::now(),
            "insurance_expiry_date"=> Carbon::now()->addYear()->format('Y-m-d'),
            "workers"=> [1],
            "calling_visa_reference_number"=>"AAAA/0001"
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
            "workers"=> [1],
        ];
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/immigrationFeePaid/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/generation/generatedStatusUpdate', $payload, $this->getHeader(false));
        
        $payload = [
            "application_id"=> 1,
            "onboarding_country_id"=> 1,
            "agent_id" => 1,
            "workers"=> [1],
            "dispatch_method" => "Courier",
            "dispatch_consignment_number" => 123456789,
            "dispatch_acknowledgement_number" => 12
        ];
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $payload, $this->getHeader(false));

        $payload = ['prospect_id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector_id' => 1, 'sector_name' => 'Agriculture', 'fomnext_quota' => 10, 'air_ticket_deposit' => 1.11, 'service_id' => 3, 'file_url' => 'test'];
        $response = $this->json('POST', 'api/v1/eContract/addService', $payload, $this->getHeader(false));

        $payload = ['prospect_id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector_id' => 1, 'sector_name' => 'Agriculture', 'fomnext_quota' => 10, 'air_ticket_deposit' => 1.11, 'service_id' => 2, 'file_url' => 'test'];
        $response = $this->json('POST', 'api/v1/totalManagement/addService', $payload, $this->getHeader(false));
    }
    
}

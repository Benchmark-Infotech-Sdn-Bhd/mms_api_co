<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class PostArrivalStatusUpdateUnitTest extends TestCase
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
     * Functional test for post arrival, arrived date mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalArrivedDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['arrived_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'arrived_date' => ['The arrived date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, entry visa valid until mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalEntryVisaValidUntilRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['entry_visa_valid_until' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'entry_visa_valid_until' => ['The entry visa valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, arrived date format validation 
     * 
     * @return void
     */
    public function testForPostArrivalArrivedDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['arrived_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'arrived_date' => ['The arrived date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, entry visa valid until format validation 
     * 
     * @return void
     */
    public function testForPostArrivalEntryVisaValidUntilFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['entry_visa_valid_until' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'entry_visa_valid_until' => ['The entry visa valid until does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, arrived date past date validation 
     * 
     * @return void
     */
    public function testForPostArrivalArrivedDatePastDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['arrived_date' => Carbon::now()->addYear()->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'arrived_date' => ['The arrived date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, entry visa valid until future date validation 
     * 
     * @return void
     */
    public function testForPostArrivalEntryVisaValidUntilFutureDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', array_merge($this->arrivedData(), ['entry_visa_valid_until' => Carbon::now()->subYear()->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'entry_visa_valid_until' => ['The entry visa valid until must be a date after yesterday.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, JTK submission date mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalJTKSubmissionDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateJTKSubmission', array_merge($this->jtkSubmissionData(), ['jtk_submitted_on' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'jtk_submitted_on' => ['The jtk submitted on field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, JTK submission date format validation 
     * 
     * @return void
     */
    public function testForPostArrivalJTKSubmissionDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateJTKSubmission', array_merge($this->jtkSubmissionData(), ['jtk_submitted_on' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'jtk_submitted_on' => ['The jtk submitted on does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, JTK submission past date validation 
     * 
     * @return void
     */
    public function testForPostArrivalJTKSubmissionPastDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateJTKSubmission', array_merge($this->jtkSubmissionData(), ['jtk_submitted_on' => Carbon::now()->addYear()->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'jtk_submitted_on' => ['The jtk submitted on must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, new arrival date mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalNewArrivalDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['new_arrival_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_arrival_date' => ['The new arrival date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival,  new arrival date format validation 
     * 
     * @return void
     */
    public function testForPostArrivalNewArrivalDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['new_arrival_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_arrival_date' => ['The new arrival date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival,  new arrival future date validation 
     * 
     * @return void
     */
    public function testForPostArrivalNewArrivalFutureDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['new_arrival_date' => Carbon::now()->subYear()->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_arrival_date' => ['The new arrival date must be a date after yesterday.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, arrival time mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalArrivalTimeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['arrival_time' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'arrival_time' => ['The arrival time field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, arrival time format validation 
     * 
     * @return void
     */
    public function testForPostArrivalArrivalTimeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['arrival_time' => '12:00 AM@@']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'arrival_time' => ['The arrival time format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, flight number mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFlightNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['flight_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'flight_number' => ['The flight number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, flight number format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFlightNumberFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', array_merge($this->postponedData(), ['flight_number' => 'DVT578678@@']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'flight_number' => ['The flight number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, arrived details update
     * 
     * @return void
     */
    public function testForPostArrivalDetailsUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', $this->arrivedData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Post Arrival Details Updated Successfully']
        ]);
    }
    /**
     * Functional test for post arrival, JTK submission details update
     * 
     * @return void
     */
    public function testForJTKSubmissionDateUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateJTKSubmission', $this->jtkSubmissionData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'JTK Submission Updated Successfully']
        ]);
    }
    /**
     * Functional test for post arrival, Cancellation
     * 
     * @return void
     */
    public function testForPostArrivalCancellation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateCancellation', $this->cancellationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Cancellation Updated Successfully']
        ]);
    }
    /**
     * Functional test for post arrival, postponed details update
     * 
     * @return void
     */
    public function testForPostponedDetailsUpdate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostponed', $this->postponedData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Postponed Status Updated Successfully']
        ]);
    }
    /**
     * Functional test for worker list search validation
     * 
     * @return void
     */
    public function testForWorkersListSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'search' => 'Wo', 'filter' => ''], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for worker list with search
     * 
     * @return void
     */
    public function testForWorkersListWithSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'search' => 'Wor', 'filter' => ''], $this->getHeader(false));
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
     * Functional test for worker list with filter
     * 
     * @return void
     */
    public function testForWorkersListWithfilter(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'search' => '', 'filter' => Carbon::now()->format('Y-m-d')], $this->getHeader(false));
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
            'name' => 'HR'
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
            'submission_date' => Carbon::now()->format('Y-m-d'), 
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
            'payment_date' => Carbon::now()->format('Y-m-d'), 
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
            'received_date' => Carbon::now()->format('Y-m-d'), 
            'valid_until' => Carbon::now()->format('Y-m-d')
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
            'name' => 'TestWorker',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Female',
            'passport_number' => 123456789154,
            'passport_valid_until' => Carbon::now()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 1234567890,
            'ksm_reference_number' => 'My/643/7684548',
            'calling_visa_reference_number' => 'asdfdq434214',
            'calling_visa_valid_until' => Carbon::now()->format('Y-m-d'),
            'entry_visa_valid_until' => Carbon::now()->format('Y-m-d'),
            'work_permit_valid_until' => Carbon::now()->format('Y-m-d'),
            'bio_medical_reference_number' => 'BIO1234567',
            'bio_medical_valid_until' => Carbon::now()->format('Y-m-d'),
            'purchase_date' => Carbon::now()->format('Y-m-d'),
            'clinic_name' => 'Test Clinic',
            'doctor_code' => 'Doc123',
            'allocated_xray' => 'Tst1234',
            'xray_code' => 'Xray1234',
            'ig_policy_number' => 'ig223422233',
            'ig_policy_number_valid_until' => Carbon::now()->format('Y-m-d'),
            'hospitalization_policy_number' => Carbon::now()->format('Y-m-d'),
            'hospitalization_policy_number_valid_until' => Carbon::now()->format('Y-m-d'),
            'bank_name' => 'Bank Name',
            'account_number' => 1234556678,
            'socso_number' => 12345678
        ];
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function arrivedData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'arrived_date' => '2023-06-27', 'entry_visa_valid_until' => '2023-07-27', 'workers' => [1]];
    }
    /**
     * @return array
     */
    public function jtkSubmissionData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'jtk_submitted_on' => '2023-06-27', 'workers' => [1]];
    }
    /**
     * @return array
     */
    public function cancellationData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'workers' => [1]];
    }
    /**
     * @return array
     */
    public function postponedData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'new_arrival_date' => Carbon::now()->addYear()->format('Y-m-d'), 'arrival_time' => '12:00 AM', 'flight_number' => 'ASG74837498', 'remarks' => 'test', 'workers' => [1]];
    }
}

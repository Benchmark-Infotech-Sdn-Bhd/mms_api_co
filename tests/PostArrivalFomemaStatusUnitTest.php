<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class PostArrivalFomemaStatusUnitTest extends TestCase
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
     * Functional test for post arrival, FOMEMA purchase date mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAPurchaseDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', array_merge($this->purchaseData(), ['purchase_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'purchase_date' => ['The purchase date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA purchase date format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAPurchaseDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', array_merge($this->purchaseData(), ['purchase_date' => '06-06-2023']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'purchase_date' => ['The purchase date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA purchase past date validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAPurchasePastDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', array_merge($this->purchaseData(), ['purchase_date' => '2035-07-27']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'purchase_date' => ['The purchase date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA total charge format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMATotalChargeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', array_merge($this->purchaseData(), ['fomema_total_charge' => 11133.1133]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomema_total_charge' => ['The fomema total charge format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA clinic name mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAClinicNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['clinic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'clinic_name' => ['The clinic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA clinic name format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAClinicNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['clinic_name' => 'Clinic Name113']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'clinic_name' => ['The clinic name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA doctor code mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMADoctorCodeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['doctor_code' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'doctor_code' => ['The doctor code field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA doctor code format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMADoctorCodeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['doctor_code' => 'AGV64873$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'doctor_code' => ['The doctor code format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA allocated xray mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAAllocatedXrayRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['allocated_xray' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'allocated_xray' => ['The allocated xray field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA allocated xray format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAAllocatedXrayFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['allocated_xray' => 'FGFSG VDHVG$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'allocated_xray' => ['The allocated xray format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA xray code mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAXrayCodeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['xray_code' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'xray_code' => ['The xray code field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA xray code format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAXrayCodeFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['xray_code' => 'DTF783848$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'xray_code' => ['The xray code format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA valid until mandatory field validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAValidUntilRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['fomema_valid_until' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomema_valid_until' => ['The fomema valid until field is required.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA valid until format validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAValidUntilFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['fomema_valid_until' => '06-06-2035']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomema_valid_until' => ['The fomema valid until does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA valid until future date validation 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAValidUntilFutureDateValidation(): void
    {
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', array_merge($this->fomemaFitData(), ['fomema_valid_until' => '2023-07-27']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomema_valid_until' => ['The fomema valid until must be a date after yesterday.']
            ]
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA purchase 
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAPurchase(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', $this->purchaseData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Purchase Details Updated Successfully']
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA Fit updation
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAFit(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', $this->fomemaFitData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'FOMEMA Status Updated Successfully']
        ]);
    }
    /**
     * Functional test for post arrival, FOMEMA UnFit updation
     * 
     * @return void
     */
    public function testForPostArrivalFOMEMAUnfit(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaUnfit', $this->fomemaUnfitData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'FOMEMA Status Updated Successfully']
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'search' => 'Wo'], $this->getHeader(false));
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
        $response = $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/workersList', ['application_id' => 1, 'onboarding_country_id' => 1, 'search' => 'Wor'], $this->getHeader(false));
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
            'name' => 'TestWorker',
            'date_of_birth' => '2023-05-13',
            'gender' => 'Female',
            'passport_number' => 123456789154,
            'passport_valid_until' => '2023-05-13',
            'fomema_valid_until' => '2023-05-13',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 1234567890,
            'ksm_reference_number' => 'My/643/7684548',
            'calling_visa_reference_number' => 'asdfdq434214',
            'calling_visa_valid_until' => '2023-05-13',
            'entry_visa_valid_until' => '2023-05-13',
            'work_permit_valid_until' => '2023-05-13',
            'bio_medical_reference_number' => 'BIO1234567',
            'bio_medical_valid_until' => '2023-05-13',
            'purchase_date' => '2023-05-13',
            'clinic_name' => 'Test Clinic',
            'doctor_code' => 'Doc123',
            'allocated_xray' => 'Tst1234',
            'xray_code' => 'Xray1234',
            'ig_policy_number' => 'ig223422233',
            'ig_policy_number_valid_until' => '2023-05-13',
            'hospitalization_policy_number' => '2023-05-13',
            'hospitalization_policy_number_valid_until' => '2023-05-13',
            'bank_name' => 'Bank Name',
            'account_number' => 1234556678,
            'socso_number' => 12345678
        ];
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function purchaseData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'purchase_date' => '2023-06-27', 'fomema_total_charge' => '111.99', 'convenient_fee' => 3, 'workers' => [1]];
    }
    /**
     * @return array
     */
    public function fomemaFitData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'clinic_name' => 'XYZ Clinic', 'doctor_code' => 'AGV64873', 'allocated_xray' => 'FGFSG VDHVG', 'xray_code' => 'DTF783848', 'fomema_valid_until' => '2035-08-31', 'workers' => [1]];
    }
    /**
     * @return array
     */
    public function fomemaUnfitData(): array
    {
        return ['application_id' => 1, 'onboarding_country_id' => 1, 'workers' => [1]];
    }
}

<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class TotalManagementAssignWorkerUnitTest extends TestCase
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
     * Functional test for assign worker accommodation provider dropdown
     * 
     * @return void
     */
    public function testForAccommodationProviderDropDown(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/accommodationProviderDropDown', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test for assign worker accommodation units dropdown
     * 
     * @return void
     */
    public function testForAccommodationUnitDropDown(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/accommodationUnitDropDown', ["id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test for aassign worker company details 
     * 
     * @return void
     */
    public function testForGetCompanyDetails(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/getCompany', ["application_id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test for assign worker ksm reference number dropdown
     * 
     * @return void
     */
    public function testForKSMReferenceNumberDropDown(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/ksmRefereneceNUmberDropDown', ["application_id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    
                ]
        ]);
    }
    /**
     * Functional test for get sector and valid until details
     * 
     * @return void
     */
    public function testForSectorAndValidUntilDate(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/getSectorAndValidUntil', ["prospect_id" => 1, "ksm_reference_number" => "My/992/095648000"], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    [
                    'sector_id', 
                    'sector_name', 
                    'valid_until'
                    ]
                ]
        ]);
    }
    /**
     * Functional test for get balanced quota
     * 
     * @return void
     */
    public function testForBalancedQuota(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/getBalancedQuota', ["application_id" => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'clientQuota',
                    'clientBalancedQuota',
                    'fomnextQuota',
                    'fomnextBalancedQuota'
                ]
        ]);
    }
    /**
     * Functional test for assign worker, accommodation provider id field validation 
     * 
     * @return void
     */
    public function testForAssignWorkerAccommodationProviderIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['accommodation_provider_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'accommodation_provider_id' => ['The accommodation provider id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, accommodation unit id field validation 
     * 
     * @return void
     */
    public function testForAssignWorkerAccommodationUnitIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['accommodation_unit_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'accommodation_unit_id' => ['The accommodation unit id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, work start date mandatory field validation 
     * 
     * @return void
     */
    public function testForAssignWorkerWorkStartDateRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['work_start_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'work_start_date' => ['The work start date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, work start date format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerWorkStartDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['work_start_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'work_start_date' => ['The work start date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, work start date past validation 
     * 
     * @return void
     */
    public function testForAssignWorkerWorkStartDatePastValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['work_start_date' => Carbon::now()->addYear()->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'work_start_date' => ['The work start date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, department format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerDepartmentFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['department' => 'department$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'department' => ['The department format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker, sub department format validation 
     * 
     * @return void
     */
    public function testForAssignWorkerSubDepartmentFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', array_merge($this->creationData(), ['sub_department' => 'SubDepartment$$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'sub_department' => ['The sub department format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for assign worker
     * 
     * @return void
     */
    public function testForAssignWorker(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Workers are Assigned Successfully']
        ]);
    }
    /**
     * Functional test for worker list for assign worker search validation
     * 
     * @return void
     */
    public function testForCancellationWorkersListSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/workerListForAssignWorker', ['search' => 'te'], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management, assign worker list with search
     * 
     * @return void
     */
    public function testForTotalManagementWorkerListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/workerListForAssignWorker', ['prospect_id' => 1, 'search' => 'tes', 'company_filter' => '', 'ksm_reference_number' => ''], $this->getHeader(false));
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

        $payload =  [
            'name' => 'VendorOne',
            'type' => 'Accommodation',
            'email_address' => 'vendorone@gmail.com',
            'contact_number' => 1234567890,
            'person_in_charge' => 'test',
            'pic_contact_number' => 1232134234,
            'address' => 'test',
            'state' => 'test',
            'city' => 'test',
            'postcode' => 45353,
            'remarks' => 'test'
        ];  
        $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader(false));

        $payload =  [
            'name' => 'VendorTwo',
            'type' => 'Transportation',
            'email_address' => 'vendortwo@gmail.com',
            'contact_number' => 1234567890,
            'person_in_charge' => 'test',
            'pic_contact_number' => 1232134234,
            'address' => 'test',
            'state' => 'test',
            'city' => 'test',
            'postcode' => 45353,
            'remarks' => 'test'
        ];  
        $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader(false));

        $payload =  [
            'name' => 'VendorThree',
            'type' => 'Insurance',
            'email_address' => 'vendorthree@gmail.com',
            'contact_number' => 1234567890,
            'person_in_charge' => 'test',
            'pic_contact_number' => 1232134234,
            'address' => 'test',
            'state' => 'test',
            'city' => 'test',
            'postcode' => 45353,
            'remarks' => 'test'
        ];  
        $this->json('POST', 'api/v1/vendor/create', $payload, $this->getHeader(false));

        $payload =  [
            'name' => 'AccOne',
            'location' => 'test',
            'maximum_pax_per_unit' => 3,
            'deposit' => 4,
            'rent_per_month' => 2,
            'vendor_id' => 1,
            'tnb_bill_account_Number' => '123',
            'water_bill_account_Number' => '123'
        ];  
        $this->json('POST', 'api/v1/accommodation/create', $payload, $this->getHeader(false));

        $payload =  [
            'driver_name' => 'TransOne',
            'driver_contact_number' => 1234567899,
            'vehicle_type' => 'test',
            'number_plate' => '1234',
            'vehicle_capacity' => 4,
            'vendor_id' => 2,
            'file_url' => 'test'
        ];  
        $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader(false));

        $payload =  [
            'no_of_worker_from' => 1,
            'no_of_worker_to' => 2,
            'fee_per_pax' => 2,
            'vendor_id' => 3
        ];  
        $this->json('POST', 'api/v1/insurance/create', $payload, $this->getHeader(false));

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
            'name' => 'DRWorker',
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
        $this->json('POST', 'api/v1/directRecruitment/onboarding/workers/create', $payload, $this->getHeader(false));

        $payload = [
            'name' => 'TestWorker',
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'),
            'gender' => 'Female',
            'passport_number' => 12345678954,
            'passport_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'kin_name' => 'Kin name',
            'kin_relationship_id' => 1,
            'kin_contact_number' => 12345678900,
            'ksm_reference_number' => '',
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
            'socso_number' => 12345678,
            'crm_prospect_id' => 1
        ];
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'agent_id' => 1, 
            'calling_visa_reference_number' => 'AGTF/7637', 
            'submitted_on' => Carbon::now()->format('Y-m-d'), 
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/process/submitCallingVisa', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'ig_policy_number' => '123456789',
            'hospitalization_policy_number' => '123456789',
            'insurance_provider_id' => 1,
            'ig_amount' => 100.00,
            'hospitalization_amount' => 200.00,
            'insurance_submitted_on' => Carbon::now()->format('Y-m-d'),
            'insurance_expiry_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'workers' => 1,
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/insurancePurchase/submit', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'calling_visa_generated' => Carbon::now()->format('Y-m-d'),
            'calling_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'status' => 'Approved',
            'workers' => [1],
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/approval/approvalStatusUpdate', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'total_fee' => 99.00,
            'immigration_reference_number' => '123456789',
            'payment_date' => Carbon::now()->format('Y-m-d'),
            'workers' => 1,
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/immigrationFeePaid/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/generation/generatedStatusUpdate', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'dispatch_method' => 'Courier',
            'dispatch_consignment_number' => '123456789',
            'dispatch_acknowledgement_number' => '123456789',
            'workers' => 1
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/callingVisa/dispatch/update', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'flight_date' => Carbon::now()->format('Y-m-d'),
            'arrival_time' => '12:00 AM',
            'flight_number' => '0123456789ABC',
            'workers' => [1],
            'remarks' => 1
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/arrival/submit', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'arrived_date' => Carbon::now()->format('Y-m-d'),
            'entry_visa_valid_until' => Carbon::now()->addYear()->format('Y-m-d'),
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updatePostArrival', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1,
            'onboarding_country_id' => 1,
            'jtk_submitted_on' => Carbon::now()->format('Y-m-d'),
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/arrival/updateJTKSubmission', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'purchase_date' => Carbon::now()->format('Y-m-d'), 
            'fomema_total_charge' => '111.99', 
            'convenient_fee' => 3, 
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/purchase', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'clinic_name' => 'XYZ Clinic', 
            'doctor_code' => 'AGV64873', 
            'allocated_xray' => 'FGFSG VDHVG', 
            'xray_code' => 'DTF783848', 
            'fomema_valid_until' => Carbon::now()->addYear()->format('Y-m-d'), 
            'workers' => 1, 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/fomema/fomemaFit', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'onboarding_country_id' => 1, 
            'plks_expiry_date' => Carbon::now()->addYear()->format('Y-m-d'), 
            'workers' => 1
        ];
        $this->json('POST', 'api/v1/directRecruitment/onboarding/postArrival/plks/updatePLKS', $payload, $this->getHeader(false));

        $payload = [
            "id" => 1, 
            "quota_requested" => 10, 
            "person_incharge" => "PICTest", 
            "cost_quoted" => 10.5, 
            "reamrks" => "remarks", 
            "file_url" => "test"
        ];
        $this->json('POST', 'api/v1/totalManagement/submitProposal', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "test name",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "employee_id" => 1,
            "transportation_provider_id" => 2,
            "driver_id" => 1,
            "assign_as_supervisor" => 0,
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10
        ];
        $this->json('POST', 'api/v1/totalManagement/project/add', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['project_id' => 1, 'department' => 'department', 'sub_department' => 'sub department', 'accommodation_provider_id' => 1, 'accommodation_unit_id' => 1, 'work_start_date' => Carbon::now()->format('Y-m-d'), 'workers' => [1]];
    }
}

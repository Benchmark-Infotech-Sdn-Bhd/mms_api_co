<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class TotalManagementServicesUnitTest extends TestCase
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
     * Functional test for total management, add service id mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceIdRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service company name mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceCompanyNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['company_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'company_name' => ['The company name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceContactNumberRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['contact_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service email mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceEmailRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service pic name mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServicePICNameRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['pic_name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'pic_name' => ['The pic name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service from existing mandatory field validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceFromExistingRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['from_existing' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'from_existing' => ['The from existing field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service client quota format validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceClientQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['client_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'client_quota' => ['The client quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service client quota size validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceClientQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['client_quota' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'client_quota' => ['The client quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service fomnext quota format validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceFomnextQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['fomnext_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service fomnext quota size validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceFomnextQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['fomnext_quota' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'fomnext_quota' => ['The fomnext quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service initial quota format validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceInitialQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['initial_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'initial_quota' => ['The initial quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service service quota format validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceServiceQuotaFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['service_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'service_quota' => ['The service quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management, add service service quota size validation 
     * 
     * @return void
     */
    public function testForTotalManagementAddServiceServiceQuotaSizeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/addService', array_merge($this->creationData(), ['service_quota' => 1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'service_quota' => ['The service quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management, get initial quota 
     * 
     * @return void
     */
    public function testForTotalManagementInitialQuota(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/getQuota', ['prospect_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'approvedQuota'
                ]
        ]);
    }
    /**
     * Functional test for total management, allocate quota validation for from existing
     * 
     * @return void
     */
    public function testForTotalManagementAllocateQuotaValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/allocateQuota', array_merge($this->allocateQuotaData(), ['service_quota' => 99]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'message' => 'Quota for service should not exceed to Initail quota'
            ]
        ]);
    }
    /**
     * Functional test for total management, allocate quota for from existing
     * 
     * @return void
     */
    public function testForTotalManagementAllocateQuotaFromExisting(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/allocateQuota', array_merge($this->allocateQuotaData(), ['client_quota' => NULL, 'fomnext_quota' => NULL]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Quota Allocated Successfully.']
        ]);
    }
    /**
     * Functional test for total management, allocate quota for not from existing
     * 
     * @return void
     */
    public function testForTotalManagementAllocateQuotaNotFromExisting(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/allocateQuota', array_merge($this->allocateQuotaData(), ['from_existing' => 0, 'initial_quota' => NULL, 'service_quota' => NULL]), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Quota Allocated Successfully.']
        ]);
    }
    /**
     * Functional test for total management application list search validation
     * 
     * @return void
     */
    public function testForTotalManagementApplicationListSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/applicationListing', ['search' => 'A'], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit Id mandatory field validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitIDRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit quota requested mandatory field validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitRequestedQuotaRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['quota_requested' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit person in charge mandatory field validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitPersonInChargeRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['person_incharge' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'person_incharge' => ['The person incharge field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit cost quoted mandatory field validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitCostQuotedRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['cost_quoted' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'cost_quoted' => ['The cost quoted field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit quota requested Format validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitRequestedQuotaFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['quota_requested' => 1.1]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit quota requested size validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitRequestedQuotaSizeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['quota_requested' => 100000]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'quota_requested' => ['The quota requested must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit cost quoted format validation
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmitCostQuotedFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', array_merge($this->submitProposalData(), ['cost_quoted' => 10.64644]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'cost_quoted' => ['The cost quoted format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management proposal submit
     * 
     * @return void
     */
    public function testForTotalManagementProposalSubmit(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/submitProposal', $this->submitProposalData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Proposal Submitted Successfully.']
        ]);
    }
    /**
     * Functional test for total management, application listing with search
     * 
     * @return void
     */
    public function testForTotalManagementApplicationListing(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/applicationListing', ['search' => 'ABC'], $this->getHeader(false));
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
     * Functional test to Display Proposal 
     * 
     * @return void
     */
    public function testToDisplayProposal(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/showProposal', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
            ]
        ]);
    }
    /**
     * Functional test to Display Service 
     * 
     * @return void
     */
    public function testToDisplayService(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/showService', ['prospect_service_id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
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
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['id' => 1, 'company_name' => 'ABC Firm', 'contact_number' => '768456948', 'email' => 'testcrm@gmail.com', 'pic_name' => 'PICTest', 'sector' => 1, 'from_existing' => 0, 'client_quota' => 10, 'fomnext_quota' => 10, 'initial_quota' => 1, 'service_quota' => 1];
    }
    /**
     * @return array
     */
    public function allocateQuotaData(): array
    {
        return ['id' => 1, 'prospect_service_id' => 2, 'from_existing' => 1, 'client_quota' => 10, 'fomnext_quota' => 10, 'initial_quota' => 1, 'service_quota' => 1];
    }
    /**
     * @return array
     */
    public function submitProposalData(): array
    {
        return [
            "id" => 1, "quota_requested" => 10, "person_incharge" => "PICTest", "cost_quoted" => 10.5, "reamrks" => "remarks", "file_url" => "test"
        ];
    }
}

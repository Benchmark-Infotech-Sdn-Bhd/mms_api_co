<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class ApplicationInterviewUnitTest extends TestCase
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
     * Functional test for Create Application Interview - Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationKsmReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Schedule Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationScheduleDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['schedule_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Approved Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApprovedQuotaRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approved_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Approval Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApprovalDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approval_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Status mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationStatusRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['status' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Schedule Date Format Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationScheduleDateFormatTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['schedule_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date does not match the format Y-m-d.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Schedule Past Date validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationSchedulePastDateTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['schedule_date' => Carbon::now()->subDays(1)->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date must be a date after yesterday.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview Approved Quota Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApprovedQuotaTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approved_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota format is invalid.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview Approved Quota Max validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApprovedQuotaMaxValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approved_quota' => 1000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota must not be greater than 3 characters.']
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Approval Date Format Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationApprovalDateFormatTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approval_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date does not match the format Y-m-d.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Create Application Interview - Approval Future Date validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationScheduleFutureDateTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', array_merge($this->creationData(), ['approval_date' => Carbon::now()->subDays(-1)->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date must be a date before tomorrow.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Application Interview Create
     * 
     * @return void
     */
    public function testForApplicationInterviewCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Application Interview Details Created Successfully']
        ]);
    }

    /**
     * Functional test for Application Interview Create
     * 
     * @return void
     */
    public function testForApplicationInterviewCreationKsmReferenceNumberUniqueValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number has already been taken.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        //$this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationKsmReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Schedule Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationScheduleDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['schedule_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Approved Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationApprovedQuotaRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approved_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Approval Date mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationnApprovalDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approval_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Status mandatory field validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationStatusRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['status' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Schedule Date Format Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationScheduleDateFormatTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['schedule_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date does not match the format Y-m-d.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Schedule Past Date validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationSchedulePastDateTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['schedule_date' => Carbon::now()->subDays(1)->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'schedule_date' => ['The schedule date must be a date after yesterday.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview Approved Quota Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationApprovedQuotaTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approved_quota' => 1.1]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota format is invalid.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview Approved Quota Max validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationApprovedQuotaMaxValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approved_quota' => 1000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota must not be greater than 3 characters.']
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Approval Date Format Type validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationApprovalDateFormatTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approval_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date does not match the format Y-m-d.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Update Application Interview - Approval Future Date validation 
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdationScheduleFutureDateTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', array_merge($this->updationData(), ['approval_date' => Carbon::now()->subDays(-1)->format('Y-m-d')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_date' => ['The approval date must be a date before tomorrow.'
                ]
            ]
        ]);
    }

    /**
     * Functional test for Application Interview Update
     * 
     * @return void
     */
    public function testForApplicationInterviewUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/update', $this->updationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Application Interview Details Updated Successfully']
        ]);
    }

    /**
     * Functional test to Show Application Interview Details
     * 
     * @return void
     */
    public function testToDisplayApplicationInterviewDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/show', ['id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
                'id',
                'ksm_reference_number',
                'item_name',
                'schedule_date',
                'approved_quota',
                'approval_date',
                'status',                    
                'remarks'
            ]
        ]);
    }

    /**
     * Functional test to List Application Interview
     * 
     * @return void
     */
    public function testToListApplicationInterviewDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/applicationInterview/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/applicationInterview/list', ['application_id' => 1], $this->getHeader());
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
     * Functional test to List Application Interview Dropdown KsmReferenceNumber
     * 
     * @return void
     */
    public function testToListApplicationInterviewDropdownKsmReferenceNumber(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/applicationInterview/dropdownKsmReferenceNumber', ['id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data'
        ]);
    }

    /**
     * @return array
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
            'name' => 'Admin'
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
            'prospect_service' => json_encode([["service_id" => 1, "service_name" => "Direct Recruitment"], ["service_id" => 2, "service_name" => "e-Contract"], ["service_id" => 3, "service_name" => "Total Management"]])];
        $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));

        $payload = [
            'application_id' => 1, 
            'submission_date' => '2023-05-04', 
            'applied_quota' => 10, 
            'status' => 'Submitted', 
            'ksm_reference_number' => 
            'My/643/7684548', 
            'remarks' => 'test'
        ];
        $this->json('POST', 'api/v1/fwcms/create', $payload, $this->getHeader(false));
    }

    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'ksm_reference_number' => 'My/643/7684548', 'schedule_date' => '2023-05-16', 'approved_quota' => 100, 'approval_date' => '2023-05-16','status' => 'Scheduled','remarks' => 'test'];
    }

    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'ksm_reference_number' => 'My/643/7684548', 'schedule_date' => '2023-05-16', 'approved_quota' => 100, 'approval_date' => '2023-05-16','status' => 'Completed','remarks' => 'test'];
    }
}

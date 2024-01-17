<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class FWCMSUnitTest extends TestCase
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
     * Functional test for Create FWCMS Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Submission Date mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationSubmissionDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['submission_date' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Status mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationStatusRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['status' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS KMS Referenece Number mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationKMSReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Applied Quota Type validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationAppliedQuotaTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['applied_quota' => 1.1]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'applied_quota' => ['The applied quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Applied Quota Max validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationAppliedQuotaMaxValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['applied_quota' => 1000]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'applied_quota' => ['The applied quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Submission Date Format Type validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationSubmissionDateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['submission_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS Submission Future Date validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationSubmissionDateFutureValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['submission_date' => Carbon::now()->addYear()->format('Y-m-d')]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for Create FWCMS KMS Referenece Number Type validation 
     * 
     * @return void
     */
    public function testForFWCMSCreationKMSReferenceNumberTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', array_merge($this->creationData(), ['ksm_reference_number' => 'My/992/095648967*%$']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for FWCMS Create
     * 
     * @return void
     */
    public function testForFWCMSCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'FWCMS Details Created Successfully']
        ]);
    }
    /**
     * Functional test for Update FWCMS ID mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['id' => '', 'ksm_reference_number' => 'My/567/7698']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['application_id' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Submission Date mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationSubmissionDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['submission_date' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Status mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationStatusRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['status' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS KMS Referenece Number mandatory field validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationKMSReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['ksm_reference_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Applied Quota Type validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationAppliedQuotaTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['applied_quota' => 1.1]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'applied_quota' => ['The applied quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Applied Quota Max validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationAppliedQuotaMaxValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['applied_quota' => 1000]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'applied_quota' => ['The applied quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Submission Date Format Type validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationSubmissionDateFormatValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['submission_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS Submission Future Date validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationSubmissionDateFutureValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['submission_date' => Carbon::now()->addYear()->format('Y-m-d')]), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'submission_date' => ['The submission date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS KMS Referenece Number Type validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationKMSReferenceNumberTypeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['ksm_reference_number' => 'My/992/095648967*%$']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update FWCMS KMS Referenece Number Size validation 
     * 
     * @return void
     */
    public function testForFWCMSUpdationKMSReferenceNumberSizeValidation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', array_merge($this->updationData(), ['ksm_reference_number' => 'VR123/746372473/94365843676347678/4987853846587364587/89475983475834657']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number must not be greater than 21 characters.']
            ]
        ]);
    }
    /**
     * Functional test for FWCMS Update
     * 
     * @return void
     */
    public function testForFWCMSUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'FWCMS Details Updated Successfully']
        ]);
    }
    /**
     * Functional test to Show FWCMS
     * 
     * @return void
     */
    public function testToDisplayFWCMSDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
                'id',
                'application_id',
                'submission_date',
                'applied_quota',
                'status',
                'ksm_reference_number',
                'remarks'
            ]
        ]);
    }
    /**
     * Functional test to List FWCMS
     * 
     * @return void
     */
    public function testToListFWCMSDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/fwcms/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/fwcms/list', ['application_id' => 1, 'page' => 1], $this->getHeader(false));
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
            'name' => 'HR',
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
            'contact_number' => 1234567890,
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
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'submission_date' => Carbon::now()->format('Y-m-d'), 'applied_quota' => 10, 'status' => 'Submitted', 'ksm_reference_number' => 'My/643/7684548', 'remarks' => 'test'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'submission_date' => Carbon::now()->format('Y-m-d'), 'applied_quota' => 10, 'status' => 'Query', 'ksm_reference_number' => 'My/643/7684548', 'remarks' => 'test'];
    }
}

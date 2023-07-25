<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class LevyUnitTest extends TestCase
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
     * Functional test to Create Levy Details Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details Payment Date mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details Payment Amount mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentAmountRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_amount' => ['The payment amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details Approved Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationApprovedQuotaRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['approved_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationKSMReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details Payment Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details Approval Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationApprovalNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['approval_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_number' => ['The approval number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Create Levy Details New KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyCreationNewKSMReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['new_ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_ksm_reference_number' => ['The new ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Payment Date Format Type validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentDateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_date' => '05-05-2023']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Payment Future Date validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentFutureDateValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_date' => '2100-05-10']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Payment Amount Decimal validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentAmountDecimalValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_amount' => 10.6565]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_amount' => ['The payment amount field must have 0-2 decimal places.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Approved Quota Maximum validation 
     * 
     * @return void
     */
    public function testForLevyCreationApprovedQuotaMaximumValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['approved_quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Approved Quota Format validation 
     * 
     * @return void
     */
    public function testForLevyCreationApprovedQuotaFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['approved_quota' => 'ABC']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Payment Reference Number Format validation 
     * 
     * @return void
     */
    public function testForLevyCreationPaymentReferenceNumberFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['payment_reference_number' => 'SVZ498787$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details Approval Number Format validation 
     * 
     * @return void
     */
    public function testForLevyCreationApprovalNumberFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['approval_number' => 'SVZ498787$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_number' => ['The approval number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details New KMS Referenece Number Type validation 
     * 
     * @return void
     */
    public function testForLevyCreationNewKMSReferenceNumberTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', array_merge($this->creationData(), ['new_ksm_reference_number' => 'My/992/095648967*%$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_ksm_reference_number' => ['The new ksm reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Create Levy Details 
     * 
     * @return void
     */
    public function testForLevyCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Levy Details Created SUccessfully']
        ]);
    }
    /**
     * Functional test to Update Levy Details ID mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Application ID mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApplicationIdRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Payment Date mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentDateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Payment Amount mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentAmountRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_amount' => ['The payment amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Approved Quota mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApprovedQuotaRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['approved_quota' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationKSMReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'ksm_reference_number' => ['The ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Payment Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details Approval Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApprovalNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['approval_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_number' => ['The approval number field is required.']
            ]
        ]);
    }
    /**
     * Functional test to Update Levy Details New KSM Reference Number mandatory field validation 
     * 
     * @return void
     */
    public function testForLevyUpdationNewKSMReferenceNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['new_ksm_reference_number' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_ksm_reference_number' => ['The new ksm reference number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Payment Date Format Type validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentDateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_date' => '05-05-2023']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Payment Future Date validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentFutureDateValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_date' => '2100-05-10']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date must be a date before tomorrow.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Payment Amount Decimal validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentAmountDecimalValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_amount' => 10.6565]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_amount' => ['The payment amount field must have 0-2 decimal places.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Approved Quota Maximum validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApprovedQuotaMaximumValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['approved_quota' => 10000]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota must not be greater than 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Approved Quota Format validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApprovedQuotaFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['approved_quota' => 'ABC']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approved_quota' => ['The approved quota format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Payment Reference Number Format validation 
     * 
     * @return void
     */
    public function testForLevyUpdationPaymentReferenceNumberFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['payment_reference_number' => 'SVZ498787$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details Approval Number Format validation 
     * 
     * @return void
     */
    public function testForLevyUpdationApprovalNumberFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['approval_number' => 'SVZ498787$$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'approval_number' => ['The approval number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details New KMS Referenece Number Type validation 
     * 
     * @return void
     */
    public function testForLevyUpdationNewKMSReferenceNumberTypeValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/levy/update', array_merge($this->updationData(), ['new_ksm_reference_number' => 'My/992/095648967*%$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_ksm_reference_number' => ['The new ksm reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for Update Levy Details 
     * 
     * @return void
     */
    public function testForLevyUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/levy/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/levy/update', $this->updationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Levy Details Updated SUccessfully']
        ]);
    }
    /**
     * Functional test to List Levy Details 
     * 
     * @return void
     */
    public function testToListLevyDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/levy/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/levy/list', ['application_id' => 1], $this->getHeader());
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
     * Functional test to Display Levy Details 
     * 
     * @return void
     */
    public function testToDisplayLevyDetails(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/levy/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/levy/show', ['id' => 1], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
                'id',
                'application_id',
                'item',
                'payment_date',
                'payment_amount',
                'approved_quota',
                'status',
                'ksm_reference_number',
                'payment_reference_number',
                'approval_number',
                'new_ksm_reference_number',
                'remarks',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at',
                'deleted_at'
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
            'name' => 'Administrator'
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
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['application_id' => 1, 'payment_date' => '2023-05-10', 'payment_amount' => 10.87, 'approved_quota' => 10, 'ksm_reference_number' => 'My/643/7684548', 'payment_reference_number' => 'SVZ498787', 'approval_number' => 'ADR4674', 'new_ksm_reference_number' => 'My/992/095648000', 'remarks' => 'test create'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'application_id' => 1, 'payment_date' => '2023-05-11', 'payment_amount' => 100.87, 'approved_quota' => 15, 'ksm_reference_number' => 'My/643/7684548', 'payment_reference_number' => 'SVZ498787', 'approval_number' => 'ADR4674', 'new_ksm_reference_number' => 'My/992/095648000', 'remarks' => 'test update'];
    }
}

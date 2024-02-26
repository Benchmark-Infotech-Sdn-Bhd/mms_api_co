<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class TotalManagementExpensesUnitTest extends TestCase
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
     * Functional test for total management create expense, worker_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateWorkerIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['worker_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'worker_id' => ['The worker id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, application_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateApplicationIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, project_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateProjectIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['project_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'project_id' => ['The project id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, title required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateTitleRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, type required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateTypeRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'type' => ['The type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreatePaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, amount required field validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateAmountRequiredFieldValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, payment date format validation 
     * 
     * @return void
     */
    public function testForTotalManagementCreatePaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create', array_merge($this->creationData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, amount format validation
     * 
     * @return void
     */
    public function testForTotalManagementCreateAmountFormatValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['amount' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management create expense, payment_reference_number format validation
     * 
     * @return void
     */
    public function testForTotalManagementCreatePaymentReferenceNumberFormatValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/create',  array_merge($this->creationData(), ['payment_reference_number' => 'APY8759348*']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, worker_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateWorkerIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['worker_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'worker_id' => ['The worker id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, application_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateApplicationIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, project_id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateProjectIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['project_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'project_id' => ['The project id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, title required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateTitleRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, type required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateTypeRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'type' => ['The type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdatePaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, amount required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateAmountRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, payment date format validation 
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdatePaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update', array_merge($this->updationData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, amount format validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdateAmountFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['amount' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management update expense, payment_reference_number format validation
     * 
     * @return void
     */
    public function testForTotalManagementUpdatePaymentReferenceNumberFormatValidation(): void
    {
        $response = $this->json('POST', '/api/v1/totalManagement/manage/expense/update',  array_merge($this->updationData(), ['payment_reference_number' => 'APY8759348*']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_reference_number' => ['The payment reference number format is invalid.']
            ]
        ]);
    }

    /**
     * Functional test for total management expense update
     * 
     * @return void
     */
    public function testForTotalManagementExpenseUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/totalManagement/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Updated Successfully']
        ]);
    }
    /**
     * Functional test for total management expense create
     * 
     * @return void
     */
    public function testForTotalManagementCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Added Successfully']
        ]);
    }
    /**
     * Functional test for total management payback expense, id required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBackIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack',  array_merge($this->payBackData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management payback expense, amount_paid required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBackAmountPaidRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack',  array_merge($this->payBackData(), ['amount_paid' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount_paid' => ['The amount paid field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management payback expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBackPaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack',  array_merge($this->payBackData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for total management payback expense, amount paid format validation
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBackAmountPaidFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack',  array_merge($this->payBackData(), ['amount_paid' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount_paid' => ['The amount paid format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for total management payback expense, payment date format validation 
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBackPaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack', array_merge($this->payBackData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for total management payback expense
     * 
     * @return void
     */
    public function testForTotalManagementExpensePayBack(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/totalManagement/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/payBack', $this->payBackData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'PayBack Added Successfully']
        ]);
    }
    /**
     * Functional test for total management expense show
     * 
     * @return void
     */
    public function testForTotalManagementExpenseShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for total management expense list search validation
     * 
     * @return void
     */
    public function testForTotalManagementExpenseListWithSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/list', ['search' => 'te', 'worker_id' => 1], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for total management expense list with search
     * 
     * @return void
     */
    public function testForTotalManagementExpenseListWithSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/list', ['search' => 'tes', 'worker_id' => 1], $this->getHeader(false));
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
     * Functional test for total management expense delete
     * 
     * @return void
     */
    public function testForTotalManagementExpenseDelete(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/totalManagement/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/totalManagement/manage/expense/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Deleted Successfully']
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
            'contact_number' => 238467,
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

        $payload =  [
            'name' => 'VendorOne',
            'type' => 'Transportation',
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
            'driver_name' => 'TransOne',
            'driver_contact_number' => 1234567899,
            'driver_email' => 'test@gmail.com',
            'vehicle_type' => 'test',
            'number_plate' => '1234',
            'vehicle_capacity' => 4,
            'vendor_id' => 1,
            'file_url' => 'test'
        ];   
        $this->json('POST', 'api/v1/transportation/create', $payload, $this->getHeader(false));

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
            'id' => 1, 
            'quota_requested' => 10, 
            'person_incharge' => 'PICTest', 
            'cost_quoted' => 10.5, 
            'reamrks' => 'remarks', 
            'file_url' => 'test.pdf'
        ];
        $this->json('POST', 'api/v1/totalManagement/submitProposal', $payload, $this->getHeader(false));

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
            "hospitalization_leave" => 10,
            "supervisor_id" => 1,
            "supervisor_type" => "employee"
        ];
        $this->json('POST', 'api/v1/totalManagement/project/add', $payload, $this->getHeader(false));

        $payload = [
            'project_id' => 1, 
            'department' => 'department', 
            'sub_department' => 'sub department', 
            'accommodation_provider_id' => 1, 
            'accommodation_unit_id' => 1, 
            'work_start_date' => Carbon::now()->format('Y-m-d'), 
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/totalManagement/manage/workerAssign/assignWorker', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return [
            "worker_id" => 1,
            "application_id" => 1,
            "project_id" => 1,
            "title" => "Worker Expense",
            "type" => "Advance",
            "payment_reference_number" => "APY8759348",
            "payment_date" =>  Carbon::now()->format('Y-m-d'),
            "amount" =>  10000.00,
            "remarks" => 'test remarks',
            "file_url"=>  "test.pdf"
        ];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return [
            "id" => 1,
            "worker_id" => 1,
            "application_id" => 1,
            "project_id" => 1,
            "title" => "Worker Expense",
            "type" => "Deposit",
            "payment_reference_number" => "APY8759348",
            "payment_date"=>  Carbon::now()->format('Y-m-d'),
            "amount" =>  10000.00,
            "remarks" => 'test remarks',
            "file_url"=>  "test.pdf"
        ];
    }
    /**
     * @return array
     */
    public function payBackData(): array
    {
        return [
            "id" => 1,
            "amount_paid" => 100.00,
            "payment_date" => Carbon::now()->format('Y-m-d')
        ];
    }
}

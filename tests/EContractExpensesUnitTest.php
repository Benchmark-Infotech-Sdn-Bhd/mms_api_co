<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class EContractExpensesUnitTest extends TestCase
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
     * Functional test for e-Contract create expense, worker_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateWorkerIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['worker_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'worker_id' => ['The worker id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, application_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateApplicationIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, project_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateProjectIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['project_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'project_id' => ['The project id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, title required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateTitleRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, type required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateTypeRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'type' => ['The type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreatePaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, amount required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateAmountRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, payment date format validation 
     * 
     * @return void
     */
    public function testForEContractExpenseCreatePaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create', array_merge($this->creationData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract create expense, amount format validation
     * 
     * @return void
     */
    public function testForEContractExpenseCreateAmountFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create',  array_merge($this->creationData(), ['amount' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract expense create
     * 
     * @return void
     */
    public function testForEContractExpenseCreation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Added Successfully']
        ]);
    }
    /**
     * Functional test for e-Contract update expense, id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, worker_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateWorkerIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['worker_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'worker_id' => ['The worker id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, application_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateApplicationIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['application_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'application_id' => ['The application id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, project_id required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateProjectIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['project_id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'project_id' => ['The project id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, title required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateTitleRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['title' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'title' => ['The title field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, type required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateTypeRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'type' => ['The type field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdatePaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, amount required field validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateAmountRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['amount' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, payment date format validation 
     * 
     * @return void
     */
    public function testForEContractExpenseUpdatePaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update', array_merge($this->updationData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract update expense, amount format validation
     * 
     * @return void
     */
    public function testForEContractExpenseUpdateAmountFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update',  array_merge($this->updationData(), ['amount' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount' => ['The amount format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract expense update
     * 
     * @return void
     */
    public function testForEContractExpenseUpdation(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/eContract/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Updated Successfully']
        ]);
    }
    /**
     * Functional test for e-Contract payback expense, id required field validation
     * 
     * @return void
     */
    public function testForEContractExpensePayBackIdRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack',  array_merge($this->payBackData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract payback expense, amount_paid required field validation
     * 
     * @return void
     */
    public function testForEContractExpensePayBackAmountPaidRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack',  array_merge($this->payBackData(), ['amount_paid' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount_paid' => ['The amount paid field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract payback expense, payment_date required field validation
     * 
     * @return void
     */
    public function testForEContractExpensePayBackPaymentDateRequiredFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack',  array_merge($this->payBackData(), ['payment_date' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date field is required.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract payback expense, amount paid format validation
     * 
     * @return void
     */
    public function testForEContractExpensePayBackAmountPaidFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack',  array_merge($this->payBackData(), ['amount_paid' => 1000.1111]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'amount_paid' => ['The amount paid format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract payback expense, payment date format validation 
     * 
     * @return void
     */
    public function testForEContractExpensePayBackPaymentDateFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack', array_merge($this->payBackData(), ['payment_date' => Carbon::now()->format('d-m-Y')]), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'payment_date' => ['The payment date does not match the format Y-m-d.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract payback expense
     * 
     * @return void
     */
    public function testForEContractExpensePayBack(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/eContract/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/payBack', $this->payBackData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'PayBack Added Successfully']
        ]);
    }
    /**
     * Functional test for e-Contract expense show
     * 
     * @return void
     */
    public function testForEContractExpenseShow(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => []
        ]);
    }
    /**
     * Functional test for e-Contract expense list search validation
     * 
     * @return void
     */
    public function testForEContractExpenseListWithSearchValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/list', ['search' => 'te', 'worker_id' => 1], $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search' => ['The search must be at least 3 characters.']
            ]
        ]);
    }
    /**
     * Functional test for e-Contract expense list with search
     * 
     * @return void
     */
    public function testForEContractExpenseListWithSearch(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/list', ['search' => 'tes', 'worker_id' => 1], $this->getHeader(false));
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
     * Functional test for e-Contract expense delete
     * 
     * @return void
     */
    public function testForEContractExpenseDelete(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/eContract/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Expense Deleted Successfully']
        ]);
    }
    /**
     * Functional test for e-Contract expense delete attachment
     * 
     * @return void
     */
    public function testForEContractExpensedeleteAttachment(): void
    {
        $this->creationSeeder();
        $this->json('POST', 'api/v1/eContract/manage/expense/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/eContract/manage/expense/deleteAttachment', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            'data' => []
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
            'special_permission' => '',
            'system_role' => 0,
            'status' => 1,
            'parent_id' => 0,
            'company_id' => 1
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
            'crm_prospect_id' => 0
        ];
        $this->json('POST', 'api/v1/worker/create', $payload, $this->getHeader(false));

        $payload = [
            'prospect_id' => 1, 
            'company_name' => 'ABC Firm', 
            'contact_number' => '768456948', 
            'email' => 'testcrm@gmail.com', 
            'pic_name' => 'PICTest', 
            'sector_id' => 1, 
            'sector_name' => 'Agriculture', 
            'fomnext_quota' => 10, 
            'air_ticket_deposit' => 1.11, 
            'service_id' => 2, 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/eContract/addService', $payload, $this->getHeader(false));

        $payload = [
            'id' => 1, 
            'crm_prospect_id' => 1, 
            'quota_requested' => 10, 
            'person_incharge' => 'PICTest', 
            'cost_quoted' => 20, 
            'remarks' => 'testRemark', 
            'file_url' => 'test'
        ];
        $this->json('POST', 'api/v1/eContract/proposalSubmit', $payload, $this->getHeader(false));

        $payload = [
            "application_id" => 1,
            "name" => "test name",
            "state" => "state test",
            "city" => "city test",
            "address" => "test address",
            "annual_leave" => 10,
            "medical_leave" => 10,
            "hospitalization_leave" => 10,
            "attachment" => "test.png",
            "valid_until" => Carbon::now()->format('Y-m-d')
        ];
        $this->json('POST', 'api/v1/eContract/project/add', $payload, $this->getHeader(false));

        $payload = [
            'project_id' => 1, 
            'department' => 'department', 
            'sub_department' => 'sub department', 
            'work_start_date' => Carbon::now()->format('Y-m-d'), 
            'workers' => [1]
        ];
        $this->json('POST', 'api/v1/eContract/manage/workerAssign/assignWorker', $payload, $this->getHeader(false));
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
            "payment_date"=>  Carbon::now()->format('Y-m-d'),
            "amount"=>  10000.00,
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
            "amount"=>  10000.00,
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

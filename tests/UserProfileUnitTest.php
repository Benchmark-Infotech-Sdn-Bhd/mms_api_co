<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class UserProfileUnitTest extends TestCase
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
     * Functional test for employee user profile name mandatory field validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateNameRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['name' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'name' => ['The name field is required.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile contact number mandatory field validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateContactNumberRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['contact_number' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number field is required.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile address mandatory field validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateAddressRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['address' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'address' => ['The address field is required.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile state mandatory field validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateStateRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['state' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state field is required.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile city mandatory field validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateCityRequiredValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['city' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'city' => ['The city field is required.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile name format validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateNameFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['name' => 'Valae$$$']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'name' => ['The name format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile Contact number format validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateContactNumberFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['contact_number' => '6454564jfj']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile Contact number length validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateContactNumberLengthValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['contact_number' => '54326547648712']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'contact_number' => ['The contact number must not be greater than 11 characters.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile state formant validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateStateFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['state' => 'State6456&&']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'state' => ['The state format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test for employee user profile city formant validation 
     * 
     * @return void
     */
    public function testForEmployeeUserProfileUpdateCityFormatValidation(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', array_merge($this->employeeUpdationData(), ['city' => 'City6456&&']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'city' => ['The city format is invalid.']
            ]
        ]);
    }
    /**
     * Functional test to Display Profile 
     * 
     * @return void
     */
    public function testToDisplayProfile(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/showUser', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => [
            ]
        ]);
    }
    /**
     * Functional test for update Employee user
     * 
     * @return void
     */
    public function testForUpdateEmployeeUser(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', $this->employeeUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'User Profile Updated Successfully']
        ]);
    }
    /**
     * Functional test for update Admin user
     * 
     * @return void
     */
    public function testForUpdateAdminUser(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', $this->adminUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'User Profile Updated Successfully']
        ]);
    }
    /**
     * Functional test for update Customer user
     * 
     * @return void
     */
    public function testForUpdateCustomerUser(): void
    {
        $this->creationSeeder();
        $response = $this->json('POST', 'api/v1/user/updateUser', $this->customerUpdationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'User Profile Updated Successfully']
        ]);
    }
    /**
     * Functional test for retest password new password mandatory field validation 
     * 
     * @return void
     */
    public function testForResetPasswordNewPasswordRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/resetPassword', array_merge($this->resetPaaswordData(), ['new_password' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'new_password' => ['The new password field is required.']
            ]
        ]);
    }
    /**
     * Functional test for retest password current password mandatory field validation 
     * 
     * @return void
     */
    public function testForResetPasswordCurrentPasswordRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/resetPassword', array_merge($this->resetPaaswordData(), ['current_password' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'current_password' => ['The current password field is required.']
            ]
        ]);
    }
    /**
     * Functional test for retest password incorrect current password
     * 
     * @return void
     */
    public function testForIncorrectCurrentPassword(): void
    {
        $response = $this->json('POST', 'api/v1/user/resetPassword', array_merge($this->resetPaaswordData(), ['current_password' => 'City6456&&']), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Current password keyed in is incorrect']
        ]);
    }
    /**
     * Functional test for retest password 
     * 
     * @return void
     */
    public function testForResetPassword(): void
    {
        $response = $this->json('POST', 'api/v1/user/resetPassword', $this->resetPaaswordData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Password Updated Successfully']
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
            "special_permission" => 0
        ];
        $this->json('POST', 'api/v1/role/create', $payload, $this->getHeader(false));
       
        $payload = [
            'employee_name' => 'Test', 
            'gender' => 'Female', 
            'date_of_birth' => Carbon::now()->subYear(25)->format('Y-m-d'), 
            'ic_number' => 222223434, 
            'passport_number' => 'ADI', 
            'email' => 'testemp@gmail.com', 
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
            "subsidiary_companies" => []
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
        $response = $this->json('POST', 'api/v1/crm/create', $payload, $this->getHeader(false));
    }
    /**
     * @return array
     */
    public function employeeUpdationData(): array
    {
        return [
            'id' => 2, 
            'name' => 'Test',  
            'contact_number' => '4765348758',
            'address' => 'address',
            'state' => 'state',
            'city' => 'city'
        ];
    }
    /**
     * @return array
     */
    public function adminUpdationData(): array
    {
        return [
            'id' => 1, 
            'name' => 'AdminSky'
        ];
    }
    /**
     * @return array
     */
    public function customerUpdationData(): array
    {
        return [
            'id' => 3, 
            'contact_number' => '642534545'
        ];
    }
     /**
     * @return array
     */
    public function resetPaaswordData(): array
    {
        return [
            'id' => 1, 
            'new_password' => 'Test123',
            'current_password' => 'Welcome@123'
        ];
    }
}

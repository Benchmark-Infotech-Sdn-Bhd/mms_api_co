<?php

namespace Tests;
use Laravel\Lumen\Testing\DatabaseMigrations;

class EmployeeTest extends TestCase
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
     * Functional test to validate Required fields for Employee creation
     * 
     * @return void
     */
    public function testForEmployeeCreationRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/create', array_merge($this->creationData(), 
        ['employee_name' => '', 'gender' => '', 'date_of_birth' => '', 
        'ic_number' => '', 'passport_number' => '', 'email' => '', 'contact_number' => '',
        'address' => '', 'postcode' => '', 'position' => '', 'branch_id' => '',
        'role_id' => '', 'salary' => '', 'status' => '', 'city' => '', 'state' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [ 
                "employee_name" => [
                    "The employee name field is required."
                ],
                "gender" => [
                    "The gender field is required."
                ],
                "date_of_birth" => [
                    "The date of birth field is required."
                ],
                "ic_number" => [
                    "The ic number field is required."
                ],
                "email" => [
                    "The email field is required."
                ],
                "contact_number" => [
                    "The contact number field is required."
                ],
                "address" => [
                    "The address field is required."
                ],
                "postcode" => [
                    "The postcode field is required."
                ],
                "position" => [
                    "The position field is required."
                ],
                "branch_id" => [
                    "The branch id field is required."
                ],
                "role_id" => [
                    "The role id field is required."
                ],
                "salary" => [
                    "The salary field is required."
                ],
                "state" => [
                    "The state field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate minimum/maximum characters for fields in Employee creation
     * 
     * @return void
     */
    public function testForEmployeeCreationMinMaxFieldValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/create', array_merge($this->creationData(), 
        ['employee_name' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ',
         'gender' => 'Femaleeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'date_of_birth' => '1998-11-12', 
         'ic_number' => 222223434354656574, 'passport_number' => 'ADI',
         'email' => 'ASGUYGYuiayegrieiriueaiuytweitywiuerytiyAHIUGIUFGRIUigsritgitgirgthsdnvidjshfiueryhuieruyhiuieuhyriueywijkkkuiueyiruyeiwutyiurwiuyeriuASGUYGYuiayegrieiriueaiuytweitywiuerytiyAHIUGIUFGRhiigsritgitgirgthsd@gmail.com', 
         'contact_number' => 23846746554768,
         'address' => 'Addres', 'postcode' => 23444543,
         'position' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ', 
         'branch_id' => 1,
         'role_id' => 1, 
         'salary' => 67.00, 'status' => 1, 
         'city' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt ', 
         'state' => 'ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiueryhui iueygriueyiuyieruyhiu ieuhyriueywhiu iueyiruyeiwutyiurw iuyeriu ASGUYGY uiayegrieiriue aiuytweitywiuerytiy AHIUGIUFGRIU igsritgitgirgthsdnvidjshfiuery sdjrkwiherihwijerhtwrt  ']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "employee_name" => [
                    "The employee name must not be greater than 255 characters."
                ],
                "gender" => [
                    "The gender must not be greater than 15 characters."
                ],
                "ic_number" => [
                    "The ic number must not be greater than 12 characters."
                ],
                "email" => [
                    "The email must not be greater than 150 characters."
                ],
                "contact_number" => [
                    "The contact number must not be greater than 11 characters."
                ],
                "postcode" => [
                    "The postcode must not be greater than 5 characters."
                ],
                "position" => [
                    "The position must not be greater than 150 characters."
                ],
                "city" => [
                    "The city must not be greater than 150 characters."
                ],
                "state" => [
                    "The state must not be greater than 150 characters."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate format for fields in Employee creation
     * 
     * @return void
     */
    public function testForEmployeeCreationFieldFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/create', array_merge($this->creationData(), 
        ['employee_name' => 'Test', 'gender' => 'Female', 'date_of_birth' => '12-11-1998', 
        'ic_number' => 'ABC', 'passport_number' => 'ADI@3', 'email' => 'testgmail.com', 'contact_number' => 'ABC',
        'address' => 'Addres', 'postcode' => 'ABC', 'position' => 'Position', 'branch_id' => 1,
        'role_id' => 1, 'salary' => 67.00, 'status' => 1, 'city' => 'Malay123A', 'state' => 'Malaysia123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "date_of_birth" => [
                    "The date of birth does not match the format Y-m-d."
                ],
                "ic_number" => [
                    "The ic number format is invalid."
                ],
                "passport_number" => [
                    "The passport number format is invalid."
                ],
                "email" => [
                    "The email must be a valid email address."
                ],
                "contact_number" => [
                    "The contact number format is invalid."
                ],
                "postcode" => [
                    "The postcode format is invalid."
                ],
                "city" => [
                    "The city format is invalid."
                ],
                "state" => [
                    "The state format is invalid."
                ]
            ]
        ]);
    }
    /**
     * Functional test to validate employee Updation
     * 
     * @return void
     */
    public function testForEmployeeUpdationValidation(): void
    {
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/employee/update', array_merge($this->updationData(), 
        ['id' => '', 'employee_name' => '', 'gender' => '', 'date_of_birth' => '', 
        'ic_number' => '', 'passport_number' => '', 'email' => '', 'contact_number' => '',
        'address' => '', 'postcode' => '', 'position' => '', 'branch_id' => '',
        'role_id' => '', 'salary' => '', 'status' => 1, 'city' => '', 'state' => '']), $this->getHeader(false));
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "employee_name" => [
                    "The employee name field is required."
                ],
                "gender" => [
                    "The gender field is required."
                ],
                "date_of_birth" => [
                    "The date of birth field is required."
                ],
                "ic_number" => [
                    "The ic number field is required."
                ],
                "email" => [
                    "The email field is required."
                ],
                "contact_number" => [
                    "The contact number field is required."
                ],
                "address" => [
                    "The address field is required."
                ],
                "postcode" => [
                    "The postcode field is required."
                ],
                "position" => [
                    "The position field is required."
                ],
                "branch_id" => [
                    "The branch id field is required."
                ],
                "role_id" => [
                    "The role id field is required."
                ],
                "salary" => [
                    "The salary field is required."
                ],
                "state" => [
                    "The state field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test for create employee
     */
    public function testForCreateEmployee(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $response = $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data"
        ]);
    }
    /**
     * Functional test for update Employee
     */
    public function testForUpdateEmployee(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to update status for Employee Required Validation
     */
    public function testForUpdateEmployeeStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/updateStatus', ['id' => '','status' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ],
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to update status for Employee Format/MinMax Validation
     */
    public function testForUpdateEmployeeStatusFormatAndMinMaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/updateStatus', ['id' => 1,'status' => 12], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "status" => [
                    "The status format is invalid.",
                    "The status must not be greater than 1 characters."
                ],
            ]
        ]);
    }
    /**
     * Functional test for update Employee Status
     */
    public function testForUpdateEmployeeStatus(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/updateStatus', ['id' => 1, 'status' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isUpdated',
                'message'
            ]
        ]);
    }
    /**
     * Functional test for delete Employee
     */
    public function testForDeleteEmployee(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/delete', ['id' => 1], $this->getHeader(false));
        $response->seeStatusCode(200);
        $this->response->assertJsonStructure([
            "data" =>
            [
                'isDeleted',
                'message'
            ]
        ]);
    }
    /**
     * Functional test to list Employees
     */
    public function testForListingEmployeesWithSearch(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/list', ['search_param' => ''], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" =>
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
     * Functional test to view Employee Required Validation
     */
    public function testForViewEmployeeRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/employee/show', ['id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            "data" => [
                "id" => [
                    "The id field is required."
                ]
            ]
        ]);
    }
    /**
     * Functional test to view Employee
     */
    public function testForViewEmployee(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data" 
        ]);
    }
    /**
     * Functional test for Employee dropdown
     */
    public function testForEmployeeDropdown(): void
    {
        $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
        'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
        'remarks' => 'test'], $this->getHeader());
        $this->json('POST', 'api/v1/employee/create', $this->creationData(), $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/employee/dropDown', [], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            "data"
        ]);
    }
    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['employee_name' => 'Test', 'gender' => 'Female', 'date_of_birth' => '1998-11-02', 
        'ic_number' => 222223434, 'passport_number' => 'ADI', 'email' => 'test@gmail.com', 'contact_number' => 238467,
        'address' => 'Addres', 'postcode' => 2344, 'position' => 'Position', 'branch_id' => 1,
        'role_id' => 1, 'salary' => 67.00, 'status' => 1, 'city' => 'ABC', 'state' => 'Malaysia'];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'employee_name' => 'Test', 'gender' => 'Female', 'date_of_birth' => '1998-11-02', 
        'ic_number' => 222223434, 'passport_number' => 'ADI', 'email' => 'test@gmail.com', 'contact_number' => 238467,
        'address' => 'Addres', 'postcode' => 2344, 'position' => 'Position', 'branch_id' => 1,
        'role_id' => 1, 'salary' => 67.00, 'status' => 1, 'city' => 'ABC', 'state' => 'Malaysia'];
    }
}

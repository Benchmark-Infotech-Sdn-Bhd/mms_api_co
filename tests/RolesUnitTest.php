<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class RolesUnitTest extends TestCase
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
     * Functional test to validate name in Role creation
     * 
     * @return void
     */
    public function testForRoleCreationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', array_merge($this->creationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name field is required.']]
        ]);
    }
    /**
     * Functional test to validate name format in Role creation
     * 
     * @return void
     */
    public function testForRoleCreationNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', array_merge($this->creationData(), ['name' => 'HR123']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * Functional test to validate Admin name in Role creation
     * 
     * @return void
     */
    public function testForRoleCreationAdminNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', array_merge($this->creationData(), ['name' => 'Admin']), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Name as Admin is not allowed, kindly provide a different Role Name.']
        ]);
    }
    /**
     * Functional test to validate Role Updation
     * 
     * @return void
     */
    public function testForRoleUpdationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/update', array_merge($this->updationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name field is required.']]
        ]);
    }
    /**
     * Functional test to validate Role format Updation
     * 
     * @return void
     */
    public function testForRoleUpdationNameFormatValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/update', array_merge($this->updationData(), ['name' => 'HR$']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name format is invalid.']]
        ]);
    }
    /**
     * Functional test to validate Role Updation
     * 
     * @return void
     */
    public function testForRoleUpdationIdValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/update', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['id' => ['The id field is required.']]
        ]);
    }
    /**
     * Functional test for create Role
     */
    public function testForCreateRole(): void
    {
        $response = $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Created Successfully']
        ]);
    }
//     /**
//      * Functional test to validate admin permission to create special permission role
//      */
//     public function testToValidateAdminPErmissionToCreateSpecialPermissionRole(): void
//     {
//         // dd($this->getHeader());exit;
//         $this->json('POST', 'api/v1/role/create', array_merge($this->creationData(), ['name' => 'CEO']), $this->getHeader());

//         $payload = ['role_id' => 1, 'modules' => json_encode([["module_id" => 9, "permission_id" => 1], ["module_id" => 9, "permission_id" => 2], ["module_id" => 9, "permission_id" => 3], ["module_id" => 9, "permission_id" => 4], ["module_id" => 9, "permission_id" => 5]])];
//         $response = $this->json('POST', 'api/v1/accessManagement/create', $payload, $this->getHeader(false));
        
//         $payload = ['employee_name' => 'Test', 'gender' => 'Female', 'date_of_birth' => '1998-11-02', 
//         'ic_number' => 222223434, 'passport_number' => 'ADI', 'email' => 'employeeceo@gmail.com', 'contact_number' => 238467,
//         'address' => 'Addres', 'postcode' => 2344, 'position' => 'Position', 'branch_id' => 1,
//         'role_id' => 1, 'salary' => 67.00, 'status' => 1, 'city' => 'ABC', 'state' => 'Malaysia', 'subsidiary_companies' => []];
//         $this->json('POST', 'api/v1/branch/create', ['branch_name' => 'Test', 'state' => 'state',
//         'city' => 'city', 'branch_address' => 'address', 'postcode' => 9876, 'service_type' => [1,2,3],
//         'remarks' => 'test'], $this->getHeader(false));
//         // $this->artisan("db:seed --class=ServiceSeeder");
//         // $this->artisan("db:seed --class=ServiceSeeder");
//         $this->json('POST', 'api/v1/employee/create', $payload, $this->getHeader(false));
//         $response = $this->call('POST', 'api/v1/login', ['email' => 'employeeceo@gmail.com', 'password' => 'Welcome@123']);

//         $header = [];
//         $header['Accept'] = 'application/json';
//         $header['Authorization'] = 'Bearer '.$response['data']['token'];
// // dd($header);exit;
//         $result = $this->json('POST', 'api/v1/role/create', ['name' => 'Manager', 'special_permission' => 1, 'system_role' => 0, 'status' => 1, 'company_id' => 1], $header);
//         dd($result);exit;

//         $response->seeStatusCode(200);
//         $response->seeJson([
//             'data' => ['message' => 'Only Admin can Create Role with Speacial Permission']
//         ]);
//     }
//     /**
//      * Functional test to validate company
//      */
//     public function testToValidateSubsidiaryCompany(): void
//     {
//         // dd($this->getHeader());exit;
//         $payload = ['company_name' => 'Test Company', 'register_number' => 'APS646-46877', 'country' => 'India', 'state' => 'TamilNadu', 'pic_name' => '', 'role' => 'Admin', 'parent_id' => 0, 'system_color' => '#cesser', 'file_url' => 'test.png'];
//         $this->json('POST', 'api/v1/company/create', $this->creationData(), $this->getHeader());
//         $res = $this->json('POST', 'api/v1/company/assignSubsidiary', ['parent_company_id' => 1, 'subsidiary_company' => [2]], $this->getHeader(false));

//         $response = $this->json('POST', 'api/v1/user/register', $this->registrationData(), $this->getHeader(false));

// // dd($header);exit;
//         $result = $this->json('POST', 'api/v1/role/create', ['name' => 'Manager', 'special_permission' => 1, 'system_role' => 0, 'status' => 1, 'company_id' => 1], $header);
//         dd($result);exit;

//         $response->seeStatusCode(200);
//         $response->seeJson([
//             'data' => ['message' => 'Only Admin can Create Role with Speacial Permission']
//         ]);
//     }
    /**
     * Functional test for update Role
     */
    public function testForUpdateRole(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/update', $this->updationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Role Updated Successfully']
        ]);
    }
    /**
     * Functional test to list Roles
     */
    public function testForListingRolesWithSearch(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $this->json('POST', 'api/v1/role/create', ['name' => 'HR'], $this->getHeader(false));
        $response = $this->json('POST', 'api/v1/role/list', ['search' => ''], $this->getHeader(false));
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
     * Functional test to view Role
     */
    public function testForViewRole(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/show', ['id' => 1], $this->getHeader(false));
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' =>
                [
                    'id',
                    'role_name',
                    'system_role',
                    'status',
                    'parent_id',
                    'created_by',
                    'modified_by',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ]
        ]);
    }
    /**
     * Functional test to update status for Role Required Validation
     */
    public function testForUpdateRoleStatusRequiredValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => '','status' => ''], $this->getHeader());
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
     * Functional test to update status for Role Format/MinMax Validation
     */
    public function testForUpdateRoleStatusFormatAndMinMaxValidation(): void
    {
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => 1,'status' => 12], $this->getHeader());
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
     * Functional test for update role Status
     */
    public function testForUpdateRoleStatus(): void
    {
        $this->json('POST', 'api/v1/role/create', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/role/updateStatus', ['id' => 1, 'status' => 1], $this->getHeader(false));
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
     * @return array
     */
    public function creationData(): array
    {
        return ['name' => 'Manager','special_permission' => '', 'system_role' => 0, 'status' => 1, 'parent_id' => 0, 'company_id' => 1];
    }
    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 1, 'name' => 'Manager', 'special_permission' => '', 'system_role' => 0, 'status' => 1, 'parent_id' => 0, 'company_id' => 1];
    }
}
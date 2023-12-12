<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;

class AdminUserUnitTest extends TestCase
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
     * Functional test to List Admin User 
     * 
     * @return void
     */
    public function testToListAdminUsers(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminList', ['search_param' => ''], $this->getHeader());
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
     * Functional test to List Admin User serach validation
     * 
     * @return void
     */
    public function testToListAdminUsersWithSearchParamValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminList', ['search_param' => 'te'], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'search_param' => ['The search param must be at least 3 characters.']
            ]
        ]);
    }

    /**
     * Functional test to List Admin User 
     * 
     * @return void
     */
    public function testToListAdminUsersWithSearchParam(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminList', ['search_param' => 'test'], $this->getHeader());
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
     * Functional test for Show Admin User Details api - ID mandatory field validation 
     * 
     * @return void
     */
    public function testForDisplayAdminUsersDetailsIdRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminShow', ['id' => ''], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }

    /**
     * Functional test to Show Admin User Details
     * 
     * @return void
     */
    public function testToDisplayAdminUsersDetails(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminShow', ['id' => '1'], $this->getHeader());
        $response->assertEquals(200, $this->response->status());
        $this->response->assertJsonStructure([
            'data' => 
            [
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - ID mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserStatusIdRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdateStatus', ['status' => 0], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - Status mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserStatusStatusRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdateStatus', ['id' => 1], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - Status format validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserStatusStatusFormatValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdateStatus', ['id' => 1, 'status' => 5], $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'status' => ['The status format is invalid.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User Details - disable
     * 
     * @return void
     */
    public function testForUpdateAdminUserDisableStatus(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdateStatus', ['id' => 1, 'status' => 0], $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            "data" =>
            [
                'isUpdated' => true,
                'message' => 'Updated Successfully'
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User Details - enable
     * 
     * @return void
     */
    public function testForUpdateAdminUserEnableStatus(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdateStatus', ['id' => 1, 'status' => 1], $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            "data" =>
            [
                'isUpdated' => true,
                'message' => 'Updated Successfully'
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - ID mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserIdRequiredValidation(): void
    {
        //$this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdate', array_merge($this->updationData(), ['id' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'id' => ['The id field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - Name mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserNameRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdate', array_merge($this->updationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'name' => ['The name field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User - Email mandatory field validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserEmailRequiredValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdate', array_merge($this->updationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email field is required.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User  Email unique validation 
     * 
     * @return void
     */
    public function testForUpdateAdminUserEmailUniqueValidation(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdate', array_merge($this->updationData(), ['email' => 'unittest@gmail.com']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => [
                'email' => ['The email has already been taken.']
            ]
        ]);
    }

    /**
     * Functional test for Update Status Admin User 
     * 
     * @return void
     */
    public function testForUpdateAdminUser(): void
    {
        $this->json('POST', 'api/v1/user/register', $this->creationData(), $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/adminUpdate', $this->updationData(), $this->getHeader());
        $response->seeStatusCode(200);
        $response->seeJson([
            "data" =>
            [
                'isUpdated' => true,
                'message' => 'Updated Successfully'
            ]
        ]);
    }

    /**
     * @return array
     */
    public function creationData(): array
    {
        return ['name' => 'Test Admin', 'user_type' => 'Admin', 'email' => 'test@test.com'];
    }

    /**
     * @return array
     */
    public function updationData(): array
    {
        return ['id' => 2, 'name' => 'Test Admin edit', 'email' => 'test@test.com'];
    }
}

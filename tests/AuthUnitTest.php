<?php
namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthUnitTest extends TestCase
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
     * A functional test for Validate name in registration .
     *
     * @return void
     */
    public function testForRegistrationNameValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/register', array_merge($this->registrationData(), ['name' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['name' => ['The name field is required.']]
        ]);
    }
    /**
     * A functional test for Validate email in registration .
     *
     * @return void
     */
    public function testForRegistrationEmailValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/register', array_merge($this->registrationData(), ['email' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['email' => ['The email field is required.']]
        ]);
    }
    /**
     * A functional test for Validate user type in registration .
     *
     * @return void
     */
    public function testForRegistrationUserTypeValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/register', array_merge($this->registrationData(), ['user_type' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['user_type' => ['The user type field is required.']]
        ]);
    }
    /**
     * A functional test for Validate email exists in registration .
     *
     * @return void
     */
    public function testForRegistrationEmailExistsValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/register', array_merge($this->registrationData(), ['email' => 'unittest@gmail.com']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['email' => ['The email has already been taken.']]
        ]);
    }
    /**
     * A functional test for registration .
     *
     * @return void
     */
    public function testForRegistration(): void
    {
        $this->json('POST', 'api/v1/role/create', ['name' => 'Administrator'], $this->getHeader());
        $response = $this->json('POST', 'api/v1/user/register', $this->registrationData(), $this->getHeader(false));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Successfully User was created']
        ]);
    }
    /**
     * A functional test for Validate email in registration .
     *
     * @return void
     */
    public function testForLoginEmailValidation(): void
    {
        $response = $this->json('POST', 'api/v1/login', array_merge($this->loginData(), ['email' => '']));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['email' => ['The email field is required.']]
        ]);
    }

    /**
     * A functional test for Validate password in registration .
     *
     * @return void
     */
    public function testForLoginPasswordValidation(): void
    {
        $response = $this->json('POST', 'api/v1/login', array_merge($this->loginData(), ['password' => '']));
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['password' => ['The password field is required.']]
        ]);
    }

    /**
     * A functional test for invalid email .
     *
     * @return void
     */
    public function testForInvalidEmailLogin(): void
    {
        $response = $this->json('POST', 'api/v1/login', array_merge($this->loginData(), ['email' => 'test123@gmail.com']));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Invalid Credentials']
        ]);
    }
    /**
     * A functional test for invalid password .
     *
     * @return void
     */
    public function testForInvalidPasswordLogin(): void
    {
        $response = $this->json('POST', 'api/v1/login', array_merge($this->loginData(), ['password' => '123456789']));
        $response->seeStatusCode(200);
        $response->seeJson([
            'data' => ['message' => 'Invalid Credentials']
        ]);
    }
    /**
     * A functional test for invalid password .
     *
     * @return void
     */
    public function testForValidLogin(): void
    {
        $this->getToken();
        $response = $this->call('POST', 'api/v1/login', $this->loginData());
        $this->assertEquals(200, $response->status());
        $response->assertJsonStructure([
            'data' =>
                [
                    'token',
                    'user',
                    'token_type',
                    'expires_in',
                ]
        ]);

    }
    /**
     * @return array
     */
    public function registrationData(): array
    {
        return ['name' => 'test', 'email' => 'test@gmail.com', 'user_type' => 'Admin', 'status' => 1, 'company_id' => 1];
    }
    /**
     * @return array
     */
    public function loginData(): array
    {
        return ['email' => 'unittest@gmail.com', 'password' => 'Welcome@123'];
    }
}

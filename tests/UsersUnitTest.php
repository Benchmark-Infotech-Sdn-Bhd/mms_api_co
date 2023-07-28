<?php
namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;

class UsersUnitTest extends TestCase
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
     * A functional test for Validate password in registration .
     *
     * @return void
     */
    public function testForRegistrationPasswordValidation(): void
    {
        $response = $this->json('POST', 'api/v1/user/register', array_merge($this->registrationData(), ['password' => '']), $this->getHeader());
        $response->seeStatusCode(422);
        $response->seeJson([
            'data' => ['password' => ['The password field is required.']]
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
        $response = $this->json('POST', 'api/v1/login', array_merge($this->registrationData(), ['email' => '']));
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
        $response = $this->json('POST', 'api/v1/login', array_merge($this->registrationData(), ['password' => '']));
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
        $response = $this->json('POST', 'api/v1/login', array_merge($this->registrationData(), ['email' => 'test123@gmail.com']));
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
        $response = $this->json('POST', 'api/v1/login', array_merge($this->registrationData(), ['password' => '123456789']));
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
        $this->json('POST', 'api/v1/role/create', ['name' => 'Administrator'], $this->getHeader());
        $this->json('POST', 'api/v1/user/register', $this->registrationData(), $this->getHeader(false));
        $response = $this->call('POST', 'api/v1/login', ['email' => 'test@gmail.com', 'password' => 'Welcome@123']);
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
        return ['name' => 'test', 'email' => 'test@gmail.com', 'password' => 'Welcome@123', 'reference_id' => 1, 'user_type' => 'Administrator','role_id' => 1,'status' => 1];
    }
}

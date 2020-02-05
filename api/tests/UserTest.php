<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Firebase\JWT\JWT;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function testUserRegistration()
    {
        $data = [
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ];

        $this->json('POST', '/api/register', $data);
        $this->assertResponseOk();
        $this->receiveJson();

        $this->seeJson(['message' => 'User successfully registred']);
        $this->seeInDatabase('users', ['email' => $data['email']]);
    }

    public function testMissingNameParamUserRegistration()
    {
        $data = [
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ];

        $request = $this->json('POST', '/api/register', $data);

        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['name'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['name' => ['The name field is required.']]
        ]);
    }

    public function testMissingEmailParamUserRegistration()
    {
        $data = [
            'name' => $this->faker()->name,
            'password' => $this->faker()->password
        ];

        $request = $this->json('POST', '/api/register', $data);

        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['email'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['email' => ['The email field is required.']]
        ]);
    }

    public function testMissingPasswordParamUserRegistration()
    {
        $data = [
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
        ];

        $request = $this->json('POST', '/api/register', $data);

        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['password'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['password' => ['The password field is required.']]
        ]);
    }

    public function testDuplicatedEmailUserRegistration()
    {
        $data = [
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ];

        $this->json('POST', '/api/register', $data);
        $this->assertResponseOk();
        $this->receiveJson();

        $this->seeJson(['message' => 'User successfully registred']);
        $this->seeInDatabase('users', ['email' => $data['email']]);

        $this->json('POST', '/api/register', $data);
        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['email'],
            'code',
            'message'
        ]);

        $this->seeJson([
            'message' => 'The given data was invalid.',
            'invalid_form' => ['email' => ['The email has already been taken.']]
        ]);

    }

    public function testUserLogin()
    {
        $data = [
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ];

        $this->json('POST', '/api/register', $data);
        $this->assertResponseOk();

        $this->json('POST', '/api/login', Arr::only($data,['email', 'password']));
        $this->assertResponseOk();

        $this->seeJsonStructure(['user','token']);
    }

    public function testMissingEmailUserLogin()
    {
        $this->json('POST', '/api/login', ['password' => $this->faker()->password]);
        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['email'],
            'code',
            'message'
        ]);
    }

    public function testMissingPasswordUserLogin()
    {
        $this->json('POST', '/api/login', ['email' => $this->faker()->email]);
        $this->assertResponseStatus(422);
        $this->receiveJson();

        $this->seeJsonStructure([
            'invalid_form' => ['password'],
            'code',
            'message'
        ]);
    }

    public function testInvalidUserLogin()
    {
        $this->json('POST', '/api/login', [
            'email' => $this->faker()->email,
            'password' => $this->faker()->password,
        ]);
        $this->assertResponseStatus(400);
        $this->receiveJson();

        $this->seeJson([
            'message' => 'Invalid credentials',
        ]);
    }

    public function testUserProfile()
    {
        $data = [
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ];

        $this->json('POST', '/api/register', $data);
        $this->assertResponseOk();

        $request = $this->json('POST', '/api/login', Arr::only($data,['email', 'password']));
        $this->assertResponseOk();

        $headers = ['Authorization' => 'Bearer '.$request->response->getData()->token];

        $this->json('GET', '/api/profile', [], $headers);
        $this->assertResponseOk();

        $this->seeJson([
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }

    public function testInvalidTokenUserProfile()
    {
        $headers = ['Authorization' => 'Bearer '.$this->faker()->sha256];

        $this->json('GET', '/api/profile', [], $headers);
        $this->assertResponseStatus(401);
        $this->seeJson([
            'message' => 'Error decoding token',
        ]);
    }

    public function testExpiredTokenUserProfile()
    {
        $iat = time() - 60*60;
        $payload = [
            'sub' => 1,
            'iss' => "taskmgr",
            'iat' => $iat,
            'exp' => $iat + 60*60
        ];

        $token = JWT::encode($payload, env('JWT_KEY'));

        $headers = ['Authorization' => 'Bearer '.$token];

        $this->json('GET', '/api/profile', [], $headers);
        $this->assertResponseStatus(401);
        $this->seeJson([
            'message' => 'The provided token is expired',
        ]);
    }
}

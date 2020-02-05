<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

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
}

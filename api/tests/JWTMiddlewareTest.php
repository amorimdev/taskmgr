<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Http\Request;

class JWTMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserAndGetToken() {
        $user = User::create([
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
            'password' => $this->faker()->password
        ]);

        return JWT::encode([
            'sub' => $user->id,
            'iss' => "taskmgr",
            'iat' => time(),
            'exp' => time() + 60*60
        ], env('JWT_KEY'));
    }

    public function testValidToken()
    {
        $request = Request::create('/api/profile', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$this->createUserAndGetToken());

        $middleware = new \App\Http\Middleware\JWTMiddleware();

        $middleware->handle($request, function ($req) {
            $this->assertObjectHasAttribute('current_user', $req);
            $this->assertInstanceOf(User::class, $req->current_user);
        });
    }

    public function testInvalidToken()
    {
        $request = Request::create('/api/profile', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$this->faker()->sha256);

        $middleware = new JWTMiddleware();

        $response = $middleware->handle($request, function ($req) {});
        $this->assertEquals($response->getStatusCode(), 401);
        $this->assertEquals($response->getContent(), '{"message":"Error decoding token"}');
    }

    public function testExpiredToken()
    {
        $iat = time() - 60*60;
        $payload = [
            'sub' => 1,
            'iss' => "taskmgr",
            'iat' => $iat,
            'exp' => $iat + 60*60
        ];

        $token = JWT::encode($payload, env('JWT_KEY'));

        $request = Request::create('/api/profile', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$token);

        $middleware = new JWTMiddleware();
        $response = $middleware->handle($request, function ($req) {});

        $this->assertEquals($response->getStatusCode(), 401);
        $this->assertEquals($response->getContent(), '{"message":"The provided token is expired"}');
    }

    public function testNoToken()
    {
        $request = Request::create('/api/profile', 'GET');

        $middleware = new JWTMiddleware();
        $response = $middleware->handle($request, function ($req) {});

        $this->assertEquals($response->getStatusCode(), 401);
        $this->assertEquals($response->getContent(), '{"message":"The token was not provided"}');
    }
}

<?php

namespace App\Http\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use Closure;
use Exception;
use App\Models\User;

class JWTMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if(!$token) {
            return response()->json([
                'error' => 'The token was not provided'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'error' => 'The provided token is expired'
            ], 400);
        } catch(Exception $e) {
            return response()->json([
                'error' => 'Error decoding token'
            ], 400);
        }

        $request->current_user = User::find($credentials->sub);
        return $next($request);
    }
}

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
                'message' => 'The token was not provided'
            ], 401);
        }

        try {
            $credentials = JWT::decode($token, env('JWT_KEY'), ['HS256']);
        } catch(ExpiredException $e) {
            return response()->json([
                'message' => 'The provided token is expired'
            ], 401);
        } catch(Exception $e) {
            return response()->json([
                'message' => 'Error decoding token'
            ], 401);
        }

        $request->current_user = User::find($credentials->sub);
        return $next($request);
    }
}

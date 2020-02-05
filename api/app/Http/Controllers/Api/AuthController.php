<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    public function login(Request $request) {

        $this->validate($request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !$user->checkPassword($request->input('password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 400);
        }

        $payload = [
            'sub' => $user->id,
            'iss' => "taskmgr",
            'iat' => time(),
            'exp' => time() + 60*60
        ];

        return response()->json([
            'token' => JWT::encode($payload, env('JWT_KEY')),
            'user' => $user
        ], 200);
    }

    public function register(Request $request) {

        $this->validate($request, [
            'name'  => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required'
        ]);

        $user = new User($request->only(['name', 'email', 'password']));
        $user->save();

        return response()->json([
            'message' => 'User successfully registred',
        ], 200);
    }

    public function profile(Request $request) {
        return $request->current_user;
    }
}

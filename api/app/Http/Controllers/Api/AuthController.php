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
                'error' => 'Invalid credentials'
            ], 400);
        }

        $payload = [
            'sub' => $user->id,
            'iss' => "taskmgr",
            'iat' => time(),
            'exp' => time() + 60*60
        ];

        return response()->json([
            'token' => JWT::encode($payload, env('JWT_KEY'))
        ], 200);
    }

    public function register(Request $request) {

        $this->validate($request, [
            'name'  => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required'
        ]);

        try {
            $user = new User($request->only(['name', 'email', 'password']));
            $user->save();
            return response()->json(['message' => 'Sucess'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 400);
        }
    }
}

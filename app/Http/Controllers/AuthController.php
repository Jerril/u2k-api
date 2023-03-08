<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    public function register(SignupRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);

        // send verification code to phone number

        return $this->sendSuccess($user, 'User registration successful');
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('phone_number', $request->phone_number)->first();
        if(!$user || !Hash::check($request->password, $user->password))
            return $this->sendError('The credentials do not match', 422);

        $user->tokens()->delete();
        $token = $user->createToken($request->device_name ?? 'unknown-device')->plainTextToken;

        return $this->sendSuccess($user, 'Login successful!', extra: ['token' => $token]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendSuccess(null, 'Logout successful!');
    }
}

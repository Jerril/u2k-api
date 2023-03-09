<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use Utils;

    public function register(SignupRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);
        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess($user, 'User registration successful');
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('phone_number', $request->phone_number)->first();
        if(!$user || !Hash::check($request->password, $user->password))
            return $this->sendError('The credentials do not match', 422);

        if(!$user->hasVerifiedPhoneNumber)
            return $this->sendError('Please verify your phone address', 422);

        $user->tokens()->delete();
        $token = $user->createToken($request->device_name ?? 'unknown-device')->plainTextToken;

        return $this->sendSuccess($user, 'Login successful!', extra: ['token' => $token]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendSuccess(null, 'Logout successful!');
    }

    public function sendPhoneNumberVerificationCode(Request $request)
    {
        $data = $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();
        if($user->hasVerifiedPhoneNumber){
            return $this->sendError("Phone number is already verified", 422);
        }

        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess(message: 'Check your '.$data['type'].' for the token');
    }

    public function verifyPhoneNumber(Request $request)
    {
        $data = $request->validate([
            'phone_number' => 'required|string|exists:users,phone_number',
            'token' => 'required|string'
        ]);

        $existing_token = DB::table('user_verification_tokens')->where([
            'phone_number' => $request->phone_number,
            'type' => 'phone',
            'token' => $request->token
        ])->first();

        if(!$existing_token) return $this->sendError('Token not found. Kindly request for a new token');
        // check if token has expired

        DB::table('user_verification_tokens')->where([
            'phone_number' => $request->phone_number,
            'type' => 'phone',
            'token' => $request->token
        ])->delete();

        User::where('phone_number', $request->phone_number)->update(['phone_number_verified_at' => now()]);

        return $this->sendSuccess(message: 'User phone number verification successful');
    }
}

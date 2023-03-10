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

        return $this->sendSuccess($user, 'Registration successful. Check your phone for verification code');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $user = User::where('phone_number', $phone_number)->first();
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
            'phone_number' => 'required|string',
        ]);
        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $user = User::where('phone_number', $phone_number)->first();
        if($user->hasVerifiedPhoneNumber){
            return $this->sendError("Phone number is already verified", 422);
        }

        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess(message: 'Check your phone for verification code');
    }

    public function verifyPhoneNumber(Request $request)
    {
        $data = $request->validate([
            'phone_number' => 'required|string',
            'token' => 'required|string'
        ]);
        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $existing_token = DB::table('user_verification_tokens')->where([
            'phone_number' => $phone_number,
            'type' => 'phone',
            'token' => $request->token
        ])->first();

        if(!$existing_token) return $this->sendError('Code not found. Kindly request for a new code');
        // check if token has expired

        DB::table('user_verification_tokens')->where([
            'phone_number' => $request->phone_number,
            'type' => 'phone',
            'token' => $request->token
        ])->delete();

        User::where('phone_number', $phone_number)->update(['phone_number_verified_at' => now()]);

        return $this->sendSuccess(message: 'Phone number verified successfully');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $user = User::where('phone_number', $phone_number)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return $this->sendError('The credentials do not match', 422);
        }

        if(!$user->hasVerifiedPhoneNumber){
            return $this->sendError('Please verify your phone number', 422);
        }

        $user->tokens()->delete();
        $token = $user->createToken($request->device_name ?? 'unknown-device')->plainTextToken;

        return $this->sendSuccess($user, 'Login successful!', extra: ['token' => $token]);
    }
}

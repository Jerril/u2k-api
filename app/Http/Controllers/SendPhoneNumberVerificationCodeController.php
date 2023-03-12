<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SendPhoneNumberVerificationCodeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $user = User::where('phone_number', $phone_number)->first();

        if(!$user){
            return $this->sendError('User not found', 404);
        }

        if($user->hasVerifiedPhoneNumber){
            return $this->sendError("Phone number is already verified", 422);
        }

        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess(message: 'Check your phone for verification code');
    }
}

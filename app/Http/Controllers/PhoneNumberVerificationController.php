<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PhoneNumberVerificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'code' => 'required|string'
        ]);

        // convert phone number to international format
        $phone_number = $this->formatPhoneNumber($request->phone_number);

        $user = User::where('phone_number', $phone_number)->first();

        if(!$user){
            return $this->sendError('User not found', 404);
        }

        $existing_token = DB::table('user_verification_tokens')->where([
            'phone_number' => $phone_number,
            'type' => 'phone',
            'token' => $request->code
        ])->first();

        if(!$existing_token){
            return $this->sendError('Code not found. Kindly request for a new code', 422);
        }

        // check if token has expired

        DB::table('user_verification_tokens')->where([
            'phone_number' => $phone_number,
            'type' => 'phone',
            'token' => $request->code
        ])->delete();

        User::where('phone_number', $phone_number)->update(['phone_number_verified_at' => now()]);

        return $this->sendSuccess(message: 'Phone number verified successfully');
    }
}

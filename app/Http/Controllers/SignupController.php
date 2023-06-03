<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SignupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SignupRequest $request)
    {
        $data = $request->validated();

        // check if phone_number already exist
        $user = User::where('phone_number', $this->formatPhoneNumber($request->phone_number))->first();
        if($user){
            return $this->sendError('User already exist', 401);
        }

        $user = User::create($data);

        // generate qrcode for user
        $user->qrcode = (string)QrCode::size(250)->generate($user->id);
        $user->save();

        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess($user, 'Registration successful. Check your phone for verification code');
    }
}

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

        // generate qrcode for user
        $data['qrcode'] = (string)QrCode::size(250)->generate('Your QR code data here');

        $user = User::create($data);
        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess($user, 'Registration successful. Check your phone for verification code');
    }
}

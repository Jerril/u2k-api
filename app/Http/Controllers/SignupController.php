<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SignupRequest;
use App\Models\User;

class SignupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SignupRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);
        $this->sendVerificationCode($user, 'phone');

        return $this->sendSuccess($user, 'Registration successful. Check your phone for verification code');
    }
}

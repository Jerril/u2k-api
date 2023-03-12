<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        auth()->user()->tokens()->delete();
        return $this->sendSuccess(null, 'Logout successful!');
    }
}

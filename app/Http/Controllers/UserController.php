<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    protected function setUserPin(Request $request)
    {
        $data = $request->validate([
            'pin' => 'integer|max_digits:4|confirmed'
        ]);

        auth()->user()->pin = $data['pin'];
        auth()->user()->save();

        return $this->sendSuccess(auth()->user(), 'Pin set successsfully');
    }

    public function getUserTransactions()
    {
        return auth()->user()->balance;
    }

    public function transferToWallet()
    {
        // withdraw from one
        // add to another
        return "transfer initiated";
    }

    public function depositToWallet()
    {
        return auth()->user()->deposit(10);
    }

    public function withdrawFromWallet()
    {
        return auth()->user()->withdraw(10);
    }
}

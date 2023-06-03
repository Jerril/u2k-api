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


    protected function getUser(User $user)
    {   
        return $this->sendSuccess($user);
    }

    protected function getUserTransactions()
    {
        return auth()->user()->balance;
    }

    protected function transferToWallet()
    {
        // withdraw from one
        // add to another
        return "transfer initiated";
    }

    protected function depositToWallet()
    {
        return auth()->user()->deposit(10);
    }

    protected function withdrawFromWallet()
    {
        return auth()->user()->withdraw(10);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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

    public function getWalletBalance()
    {
        return $this->sendSuccess(auth()->user()->balanceInt);
    }

    protected function transferToWallet(Request $request)
    {
        $data = request->validate([
            'recipient_id' =>  'required|exists:users,id',
            'amount' => 'integer'
        ]);

        // check for insufficient balance
        if($data['amount'] > auth()->user()->balanceInt){
            return $this->sendError('Insufficient balance', 401);
        }
        
        auth()->user()->withdraw($data['amount']);
        
        $recipient = User::where('id', $data['recipient_id'])->first();
        if(!$recipient){
            return $this->sendError('Recipient not found', 401);
        }

        auth()->user()->deposit($data['amount']);

        // store in transaction table

        return $this->success([], 'Transfer successful!', 200);
    }

    protected function depositToWallet()
    {
        return "transfer from bank to u2k wallet";
        // receive money from bank through paystack to our paystack wallet
        // return auth()->user()->deposit(10);
    }

    protected function withdrawFromWallet()
    {
        return "transfer from u2k wallet to bank account";
        // send money from our paystack wallet to users bank account
        // return auth()->user()->withdraw(10);
    }
}

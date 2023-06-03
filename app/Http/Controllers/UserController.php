<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    protected function setUserPin(Request $request)
    {
        $data = $request->validate([
            'pin' => 'integer|min_digits:4|max_digits:4|confirmed'
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
        $balance = [
            'balance' => auth()->user()->balanceFloat
        ];

        return $this->sendSuccess($balance);
    }

    protected function transferToWallet(Request $request)
    {
        $data = $request->validate([
            'recipient_id' =>  'required|exists:users,id',
            'amount' => 'required|decimal:2'
        ]);
        
        auth()->user()->withdrawFloat($data['amount']);
        
        $recipient = User::where('id', $data['recipient_id'])->first();
        if(!$recipient){
            return $this->sendError('Recipient not found', 401);
        }

        auth()->user()->depositFloat($data['amount']);

        // store in transaction table

        return $this->sendSuccess([], 'Transfer successful!', 200);
    }

    protected function depositToWallet(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|decimal:2'
        ]);

        // process bank details
        // receive money from bank through paystack to our paystack wallet
        
        auth()->user()->depositFloat($data['amount']);

        // store in transaction table

        return $this->sendSuccess([], 'Deposit successful!', 200);
    }

    protected function withdrawFromWallet(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|decimal:2'
        ]);

        // process bank details
        // send money from our paystack wallet to users bank account

        auth()->user()->withdrawFloat($data['amount']);

        // store in transaction table
        return $this->sendSuccess([], 'Withdrawal successful!', 200);
    }
}

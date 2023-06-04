<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use App\Services\Paystack;

class UserController extends Controller
{
    protected $paystack;

    public function __constructor(Paystack $paystack) {
        $thi->paystack = $paystack;
    }

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
        $balance = ['balance' => auth()->user()->balanceFloat];

        return $this->sendSuccess($balance);
    }

    protected function transferToWallet(Request $request)
    {
        $data = $request->validate([
            'recipient_id' =>  'required|exists:users,id',
            'amount' => 'required|decimal:2'
        ]);

        DB::beginTransaction();
        try {
            auth()->user()->withdrawFloat($data['amount']);
        
            $recipient = User::where('id', $data['recipient_id'])->first();
            if(!$recipient){
                return $this->sendError('Recipient not found', 401);
            }

            $recipient->depositFloat($data['amount']);

            // store in transaction table
            $transaction = Transaction::create([
                'type' => 'transfer',
                'sender' => auth()->user(),
                'receiver' => $recipient,
                'ref' => 'random ref',
                'state' => 'successful'
            ]);

            DB::commit();
            return $this->sendSuccess($transaction, 'Transfer successful!', 200);

        } catch(Exception $ex) {
            DB::rollback();
            return $this->sendError($ex->getMessage());
        }
    }

    protected function depositToWallet(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|decimal:2',
            'email' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // process bank details
            // receive money from bank through paystack to our paystack wallet
            $payment_info = $this->paystack->initializeTransfer($amount);

            auth()->user()->depositFloat($data['amount']);

            // store in transaction table
            $transaction = Transaction::create([
                'type' => 'deposit',
                'sender' => auth()->user(),
                'receiver' => $recipient,
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'paystack_ref' => $payment_info['reference'],
                'state' => 'initiated'
            ]);

            DB::commit();
            return $this->sendSuccess($payment_info['authorization_url'], 'Deposit initiated!', 200);

        } catch(Exception $ex) {
            DB::rollback();
            return $this->sendError($ex->getMessage());
        }
    }

    public function verifyWalletDeposit(Request $request)
    {
        $data = $request->validate([
            'ref' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $payment_verification = $this->paystack->verifyTransaction($amount);

            // if verification not successful, deduct from the wallet,
            // set trx state to failed

            // if true update ref in transaction table to successful
            $transaction = Transaction::where('ref', $data['ref'])->update(['state' => 'successful']);

            DB::commit();
            return $this->sendSuccess($payment_info, 'Deposit initiated!', 200);

        } catch(Exception $ex) {
            DB::rollback();
            return $this->sendError($ex->getMessage());
        }
    }

    protected function withdrawFromWallet(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|decimal:2',
            'account_no' => 'required|string',
            'bank_code' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // process bank details
            // send money from our paystack wallet to users bank account

            // crete transfer recipient
            $transfer_recipient = $this->paystack->createTransferRecipient(auth()->user()->phone_number, $data['account_no'], $data['bank_code']);

            // initiate transfer
            $transfer_recipient = $this->paystack->initiateTransfer($amount, $data['account_no'], $data['bank_code']);

            auth()->user()->withdrawFloat($data['amount']);

            // store in transaction table
            $transaction = Transaction::create([
                'type' => 'withdrawal',
                'sender' => auth()->user(),
                'receiver' => $data['account_no'],
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'paystack_ref' => $payment_info['reference'],
                'state' => 'initiated'
            ]);

            DB::commit();
            return $this->sendSuccess($transaction, 'Withdrawal successful!', 200);

        } catch(Exception $ex) {
            DB::rollback();
            return $this->sendError($ex->getMessage());
        }
    }
}

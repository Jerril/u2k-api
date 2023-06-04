<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Trx;
use App\Services\Paystack;

class UserController extends Controller
{
    protected $paystack;

    public function __construct(Paystack $paystack) {
        $this->paystack = $paystack;
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

        $recipient = User::where('id', $data['recipient_id'])->first();
        if(!$recipient){
            return $this->sendError('Recipient not found', 401);
        }

        DB::beginTransaction();
        try {
            auth()->user()->withdrawFloat($data['amount']);

            $recipient->depositFloat($data['amount']);

            // store in transaction table
            $transaction = Trx::create([
                'type' => 'transfer',
                'sender_id' => auth()->id(),
                'receiver_id' => $recipient->id,
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'state' => 'successful'
            ]);

            DB::commit();
            return $this->sendSuccess($transaction, 'Transfer successful!', 200);

        } catch(Exception $ex) {
            DB::rollback();

            $transaction = Trx::create([
                'type' => 'transfer',
                'sender_id' => auth()->id(),
                'receiver_id' => $recipient->id,
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'state' => 'failed'
            ]);

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
            // get paystack checkout modal link
            $payment_info = $this->paystack->initializeTransfer($data['amount']);

            auth()->user()->depositFloat($data['amount']);

            // store in transaction table
            // TODO: state should ideally be initated until trx is verfied
            $transaction = Trx::create([
                'type' => 'deposit',
                'sender_id' => auth()->id(),
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'paystack_ref' => $payment_info['data']['reference'],
                'state' => 'successful'
            ]);

            DB::commit();
            return $this->sendSuccess($payment_info, 'Deposit initiated!', 200);

        } catch(Exception $ex) {
            DB::rollback();

            $transaction = Trx::create([
                'type' => 'deposit',
                'sender_id' => auth()->id(),
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'state' => 'failed'
            ]);

            return $this->sendError($ex->getMessage());
        }
    }

    public function verifyWalletDeposit(Request $request)
    {
        $data = $request->validate([
            'reference' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $payment_verification = $this->paystack->verifyTransaction($data['reference']);

            // if verification not successful, deduct from the wallet,
            // set trx state to failed

            // if true update ref in transaction table to successful
            // $transaction = Trx::where('ref', $data['ref'])->update(['state' => 'successful']);

            DB::commit();
            return $this->sendSuccess($payment_verification);

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
            // create transfer recipient
            $transfer_recipient = $this->paystack->createTransferRecipient(auth()->user()->phone_number, $data['account_no'], $data['bank_code']);

            // initiate transfer
            $transfer_info = $this->paystack->initiateTransfer($data['amount'], $transfer_recipient['data']['recipient_code']);

            auth()->user()->withdrawFloat($data['amount']);

            // store in transaction table
            // TODO: state should ideally be initated until trx is verfied
            $transaction = Trx::create([
                'type' => 'withdrawal',
                'sender_id' => auth()->id(),
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'paystack_ref' => $transfer_info['data']['reference'],
                'details' => $transfer_recipient['data']['details'],
                'state' => 'successful'
            ]);

            DB::commit();
            return $this->sendSuccess($transfer_info, 'Withdrawal successful!');

        } catch(Exception $ex) {
            DB::rollback();

            $transaction = Trx::create([
                'type' => 'withdrawal',
                'sender_id' => auth()->id(),
                'amount' => $data['amount'],
                'ref' => time()."U".auth()->id(),
                'state' => 'failed'
            ]);

            return $this->sendError($ex->getMessage());
        }
    }

    // verify withdrawal



    protected function getBanks()
    {
        return $this->sendSuccess($this->paystack->listBanks(), 'All Banks');
    }

    protected function verifyBankDetails(Request $request, Paystack $paystack)
    {
        $data = $request->validate([
            'account_no' => 'required|string',
            'bank_code' => 'required|string',
        ]);

        return $this->sendSuccess($this->paystack->verifyAccountDetails($data['account_no'], $data['bank_code']));
    }
}

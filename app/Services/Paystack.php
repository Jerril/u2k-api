<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class Paystack
{
    /*
        WITHDRAWAL TRANSACTION - FROM U2K PAYSTACK WALLET TO BANK ACCOUNT (SENDING MONEY TO BANK ACCOUNT FROM PAYSTACK WALLET)
    */

    // get NG banks
    public function listBanks()
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->get("https://api.paystack.co/bank?currency=NGN")->json();

        return $response;
    }

    // verify bank details
    public function verifyAccountDetails($account_no, $bank_code)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->get("https://api.paystack.co/bank/resolve?account_number=$account_no&bank_code=$bank_code")->json();

        return $response;
    }

    // create transfer recipient
    public function createTransferRecipient($name, $account_no, $bank_code)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->post("https://api.paystack.co/transferrecipient", [
            "type" => "nuban",
            "name" => $name,
            "account_number" => $account_no,
            "bank_code" => $bank_code,
            "currency" => "NGN"
        ])->json();

        return $response;
    }

    // initiate transfer
    public function initiateTransfer($amount, $recipient)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->post("https://api.paystack.co/transfer", [
            "source" => "balance", 
            "amount" => $amount*100,
            "reference" => time(),
            "recipient" => $recipient,
            "reason" => "Holiday Flexing"
        ])->json();

        return $response;
    }

    // verify successful transfer/withdrawal using webhook

    
    
    /*
        DEPOSIT TRANSACTION - FROM BANK ACCOUNT THRU CARD PAYMENT INTO U2K PAYSTACK WALLET
    */

    // initialize transfer & get paystack payment modal
    public function initializeTransfer($amount, $email = 'dayoolapeju@gmail.com')
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->post("https://api.paystack.co/transaction/initialize", [
            "email" => $email,
            "amount" => $amount * 100,
            "callback_url" => env('PAYSTACK_CALLBACK_URL'),
            "currency" => "NGN"
        ])->json();

        return $response;
    }

    public function verifyTransaction($reference)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->get("https://api.paystack.co/transaction/verify/$reference")->json();

        return $response;
    }

    // for recurring/subsequent payment
    public function charge_authorization($authorization_code, $email, $amount=10500)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->post("https://api.paystack.co/transaction/charge_authorization", [
            "authorization_code" => $authorization_code,
            "email" => $email,
            "amount" => $amount * 100
        ])->json();

        return $response;
    }

}
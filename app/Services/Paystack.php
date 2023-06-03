<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class Paystack
{
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

    // create transfer recipient

    // initialize transfer & get paystack payment modal
    public function initializeTransfer($email, $amount=10500, $currency="NGN")
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env('PAYSTACK_SECRET_KEY'),
            "Cache-Control" => 'no-cache'
        ])->post("https://api.paystack.co/transaction/initialize", [
            "email" => $email,
            "amount" => $amount * 100,
            "callback_url" => env('PAYSTACK_CALLBACK_URL'),
            "currency" => $currency
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
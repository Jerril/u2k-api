<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class Termii
{
    protected $url;

    public function __construct()
    {
        $this->url = "https://api.ng.termii.com/api/sms/send";
    }

    public function sendSMS($to, $message)
    {
        $response = Http::post($this->url, [
            "to" => $to,
            "from" => "SellersMart",
            "sms" => $message,
            "type" => "plain",
            "api_key" => env('TERMII_KEY'),
            "channel" => "generic"
        ]);

        return $response->json();
    }
}

<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Jobs\SendPhoneNumberVerificationCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait Utils
{
    public function sendVerificationCode(User $user, string $type)
    {

        DB::table('user_verification_tokens')->where(['type' => $type, 'phone_number' => $user->phone_number])->delete();

        $token = rand(100000, 999999);

        DB::table('user_verification_tokens')->insert([
            'phone_number' => $user->phone_number,
            'token'=> $token,
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $message = "<#> Dear ".$user->name.", your U2K confirmation code is ".$token.". Do not share this code with anyone. Thank you for choosing us.";
        SendPhoneNumberVerificationCode::dispatch($user, $message);
        // (new \App\Services\Termii())->sendSMS($user->phone_number, $message);

        return;
    }

    public function formatPhoneNumber(string $phone_number): string
    {
        $phone_number = preg_replace('/[^0-9]/', '', $phone_number);
        $phone_number = ltrim($phone_number, '0');
        $phone_number = '234'.$phone_number;
        return $phone_number;
    }
}

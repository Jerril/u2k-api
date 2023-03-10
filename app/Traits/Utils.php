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

        $message = "<#> Dear ".$user->name.", your U2K confirmation code is ".$token.". Do not share this code with anyone. Thank you for choosing U2K.";
        SendPhoneNumberVerificationCode::dispatch($user, $message);
        // (new \App\Services\Termii())->sendSMS($user->phone_number, $message);

        return;
    }
}

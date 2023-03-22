<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PhoneNumberVerificationController;
use App\Http\Controllers\SendPhoneNumberVerificationCodeController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', fn() => "u2k: Kindly go back");

Route::prefix('users')->group(function() {
    Route::post('/', SignupController::class);
    Route::post('/login', LoginController::class);
    Route::put('/verify', PhoneNumberVerificationController::class);
    Route::post('/verification-code', SendPhoneNumberVerificationCodeController::class);
});

Route::middleware('auth:sanctum')->group(function() {
    Route::delete('/users/logout', LogoutController::class);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PhoneNumberVerificationController;
use App\Http\Controllers\SendPhoneNumberVerificationCodeController;
use App\Http\Controllers\UserController;
use App\Services\Paystack;
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

Route::get('/', fn() => "u2k API: Kindly go back");

Route::prefix('users')->group(function() {
    Route::post('/', SignupController::class);
    Route::post('/login', LoginController::class);
    Route::put('/verify', PhoneNumberVerificationController::class);
    Route::post('/verification-code', SendPhoneNumberVerificationCodeController::class);
});

Route::middleware('auth:sanctum')->group(function() {
    Route::prefix('users')->group(function() {
        Route::delete('/logout', LogoutController::class);
        Route::put('/pin', [UserController::class, 'setUserPin']);
        Route::get('/{user}', [UserController::class, 'getUser']);

        Route::prefix('/wallet')->group(function() {
            Route::get('/balance', [UserController::class, 'getWalletBalance']);
            Route::get('/history', [UserController::class, 'getTransactionHistory']);

            Route::post('/transfer', [UserController::class, 'transferToWallet']);
            Route::post('/deposit', [UserController::class, 'depositToWallet']);
            Route::post('/deposit/verify', [UserController::class, 'verifyWalletDeposit']);
            Route::post('/withdraw', [UserController::class, 'withdrawFromWallet']);
        });
    });

    Route::prefix('banks')->group(function() {
        Route::get('/', [UserController::class, 'getBanks']);
        Route::post('/verify', [UserController::class, 'verifyBankDetails']);
    });
});

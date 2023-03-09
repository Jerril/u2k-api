<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

Route::controller(AuthController::class)->group(function() {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

// Route::get('/send-sms', function(App\Services\Termii $termii) {
//     return $termii->sendSMS("2349049423109", "hello customer");
// });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

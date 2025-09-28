<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::group([], function () {
    Route::any('razorpay/status', 'RazorPayController@status')->name('razorpay.status')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('razorpay/webhook', 'RazorPayController@webhook')->name('razorpay.webhook')->withoutMiddleware([VerifyCsrfToken::class]);
});

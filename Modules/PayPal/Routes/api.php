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
    // PayPal
    Route::any('paypal/status', 'PayPalController@status')->name('paypal.status')->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('paypal/webhook', 'PayPalController@webhookHandler')->name('paypal.webhook')->withoutMiddleware([VerifyCsrfToken::class]);
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Subscription\Http\Controllers\API\SubscriptionController;

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

Route::get('/subscription/plans', [SubscriptionController::class, 'getPlans']);
Route::get('/subscription/plans/productids', [SubscriptionController::class, 'getPlansProductIds']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('subscription', SubscriptionController::class)->names('subscription');
    Route::post('/subscription/create', [SubscriptionController::class, 'purchase']);
});

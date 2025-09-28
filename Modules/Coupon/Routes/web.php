<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth'], 'namespace' => 'Backend', 'as' => 'backend.'], function () {

    // Coupon
    Route::resource('backend/coupon', 'CouponController', ['except' => ['show']]);
    Route::post('backend/coupon-status', 'CouponController@toggleStatus')->name('coupon-status');
    Route::put('coupon/status/{id}', 'CouponController@status')->name('coupon.status');
    Route::delete('delete-coupons', 'CouponController@deleteRows')->name('delete.coupons');

});

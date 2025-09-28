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
    //plan
    Route::resource('backend/plan', 'PlanController', ['except' => ['show']]);
    Route::get('backend/subscriptions', 'PlanController@subscription')->name('subscription.index')->middleware('can:backend.plan.index');
    Route::put('plan/status/{id}', 'PlanController@toggleStatus')->name('plan.status')->middleware('can:backend.plan.edit');
    Route::delete('delete-plans', 'PlanController@deleteRows')->name('delete.plans')->middleware('can:backend.plan.destroy');
});

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/resetPassword/{token}', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('resetPassword');
Route::post('/updatePassword', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('updatePassword');
Route::get('/orderPlace250', [App\Http\Controllers\AutomaticOrderController::class, 'orderPlace250'])->name('orderPlace250');

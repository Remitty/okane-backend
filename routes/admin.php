<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BankTypeController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "admin" middleware group. Enjoy building your admin!
|
*/


Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin');
Route::get('/index', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');

Route::get('/login', [App\Http\Controllers\Auth\LoginAdminController::class, 'loginForm'])->name('admin.login.form');
Route::post('/login', [App\Http\Controllers\Auth\LoginAdminController::class, 'authenticate'])->name('admin.login');
Route::get('/logout', [App\Http\Controllers\Auth\LoginAdminController::class, 'logout'])->name('admin.logout');

Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

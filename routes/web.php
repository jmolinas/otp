<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Middleware\EnsureOtpVerified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate;


Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth')->get('/otp-verify', [OtpController::class, 'index'])->name('otp.verify');
Auth::routes();
Route::middleware([Authenticate::class, EnsureOtpVerified::class])->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

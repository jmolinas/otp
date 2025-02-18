<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate;


Route::get('/', function () {
    return view('welcome');
});
Auth::routes();
Route::middleware([Authenticate::class])->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

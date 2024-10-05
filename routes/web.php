<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (auth()->guest()) {
        // return redirect()->route('sso.login');
    }

    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Single Sign-On

// Request Authorization
Route::get('/sso/login', [App\Http\Controllers\SSOController::class, 'redirect'])->name('sso.login');

// Callback & Request Token
Route::get('/sso/callback', [App\Http\Controllers\SSOController::class, 'callback'])->name('sso.callback');

// Authenticate
Route::get('/sso/authenticate', [App\Http\Controllers\SSOController::class, 'authenticate'])->name('sso.authenticate');

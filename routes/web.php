<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Single Sign-On
Route::get('/sso/login', [App\Http\Controllers\SSOController::class, 'redirect'])->name('sso.login');
Route::get('/sso/callback', [App\Http\Controllers\SSOController::class, 'callback'])->name('sso.callback');
Route::get('/sso/authenticate', [App\Http\Controllers\SSOController::class, 'authenticate'])->name('sso.authenticate');

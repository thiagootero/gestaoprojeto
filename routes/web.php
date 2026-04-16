<?php

use App\Http\Controllers\SsoLoginController;
use App\Http\Controllers\SsoLogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/sso/login', SsoLoginController::class)
    ->name('sso.login');
Route::get('/sso/logout', SsoLogoutController::class)
    ->name('sso.logout');

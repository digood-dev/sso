<?php

use Illuminate\Support\Facades\Route;
use Digood\Sso\Http\Controllers\SsoController;

Route::prefix('sso')
    ->middleware('web')
    ->withoutMiddleware('auth')
    ->group(function () {
        Route::get('/sign-in', [SsoController::class, 'sign_in'])->name('sso.sign-in');
        Route::get('/sign-out', [SsoController::class, 'sign_out'])->name('sso.sign-out');
        Route::get('/sign-in/callback', [SsoController::class, 'sign_in_callback'])->name('sso.sign-in.callback');
    });
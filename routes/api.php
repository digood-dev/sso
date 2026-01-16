<?php

use Digood\Sso\Http\Controllers\SsoApiController;
use Illuminate\Support\Facades\Route;
use Digood\Sso\Http\Middleware\SsoApiMiddleware;

Route::middleware(SsoApiMiddleware::class)
    ->prefix('sso/api')
    ->group(function () {
        Route::post('/sign-in/key', [SsoApiController::class, 'sign_in_key'])->name('sso.api.sign-in.key');
    });
<?php

use Digood\Sso\Http\Controllers\GoController;
use Digood\Sso\Http\Controllers\SsoApiController;
use Digood\Sso\Http\Middleware\SsoMiddleware;
use Illuminate\Support\Facades\Route;
use Digood\Sso\Http\Controllers\SsoController;

Route::prefix('sso')
    ->withoutMiddleware('auth')
    ->group(function () {
        // Web层面
        Route::middleware('web')->group(function () {
            Route::get('/sign-in', [SsoController::class, 'sign_in'])->name('sso.sign-in');
            Route::get('/sign-out', [SsoController::class, 'sign_out'])->name('sso.sign-out');
            Route::get('/sign-in/by_token/{token}', [SsoController::class, 'sign_in_by_token'])->name('sso.sign-in.by_token');
            Route::get('/sign-in/by_key/{key}', [SsoController::class, 'sign_in_by_key'])->name('sso.sign-in.by_key');// 其它子系统通过token来登录
            Route::get('/sign-in/callback', [SsoController::class, 'sign_in_callback'])->name('sso.sign-in.callback');
            Route::get('/go/sub_system', [GoController::class, 'sub_system'])->name('sso.go.sub-system');// 跳转子系统

        });

        // Api层面
        Route::middleware(SsoMiddleware::class)->prefix('api')->group(function () {
            Route::get('sign-in/by_pat_token', [SsoApiController::class, 'sign_in_by_pat_token'])->name('sso.api.sign-in.by_pat_token');
        });

    });
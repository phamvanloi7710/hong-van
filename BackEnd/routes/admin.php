<?php

use App\Http\Controllers\Api\V1\Auth\AuthSessionController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\SystemPingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/v1')
    ->name('admin.api.v1.')
    ->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('login', [AuthSessionController::class, 'store'])
                ->middleware('throttle:auth.login')
                ->name('login');
            Route::post('forgot-password', ForgotPasswordController::class)
                ->middleware('throttle:auth.password')
                ->name('forgot-password');
            Route::post('reset-password', ResetPasswordController::class)
                ->middleware('throttle:auth.password')
                ->name('reset-password');

            Route::middleware('auth:sanctum')->group(function (): void {
                Route::get('me', [AuthSessionController::class, 'show'])->name('me');
                Route::post('logout', [AuthSessionController::class, 'destroy'])->name('logout');
            });
        });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('system/ping', SystemPingController::class)
                ->middleware('can:system_health')
                ->name('system.ping');
        });
    });

<?php

use App\Http\Controllers\Api\V1\SystemPingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/v1')
    ->name('admin.api.v1.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('system/ping', SystemPingController::class)
            ->middleware('can:system_health')
            ->name('system.ping');
    });

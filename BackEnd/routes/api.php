<?php

use App\Http\Controllers\Api\V1\SystemPingController;
use Illuminate\Support\Facades\Route;

Route::prefix('public/v1')
    ->name('public.api.v1.')
    ->group(function (): void {
        Route::get('system/ping', SystemPingController::class)->name('system.ping');
    });

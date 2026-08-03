<?php

use App\Http\Controllers\Api\V1\SystemPingController;
use App\Http\Controllers\Api\V1\Transportation\PublicTransportRequestController;
use App\Http\Controllers\Api\V1\Warehouses\PublicWarehouseRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('public/v1')
    ->name('public.api.v1.')
    ->group(function (): void {
        Route::get('system/ping', SystemPingController::class)->name('system.ping');
        Route::post('transport-requests', PublicTransportRequestController::class)->middleware('throttle:public.forms')->name('transport-requests.store');
        Route::post('warehouse-requests', PublicWarehouseRequestController::class)->middleware('throttle:public.forms')->name('warehouse-requests.store');
    });

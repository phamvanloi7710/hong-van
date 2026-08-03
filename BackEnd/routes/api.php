<?php

use App\Http\Controllers\Api\V1\Leads\PublicContactController;
use App\Http\Controllers\Api\V1\Leads\PublicQuoteController;
use App\Http\Controllers\Api\V1\Media\PublicMediaContentController;
use App\Http\Controllers\Api\V1\Search\PublicRelatedContentController;
use App\Http\Controllers\Api\V1\Search\PublicSearchController;
use App\Http\Controllers\Api\V1\SystemPingController;
use App\Http\Controllers\Api\V1\Transportation\PublicTransportRequestController;
use App\Http\Controllers\Api\V1\Warehouses\PublicWarehouseRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('public/v1')
    ->name('public.api.v1.')
    ->group(function (): void {
        Route::get('system/ping', SystemPingController::class)->name('system.ping');
        Route::get('search', PublicSearchController::class)->middleware('throttle:public.search')->name('search.index');
        Route::get('related/{type}/{publicId}', PublicRelatedContentController::class)
            ->whereIn('type', config('search.types', []))
            ->whereUlid('publicId')
            ->name('related.index');
        Route::get('media/{media:public_id}/content', PublicMediaContentController::class)->name('media.content');
        Route::post('contact-requests', PublicContactController::class)->middleware('throttle:public.forms')->name('contact-requests.store');
        Route::post('quote-requests', PublicQuoteController::class)->middleware('throttle:public.forms')->name('quote-requests.store');
        Route::post('transport-requests', PublicTransportRequestController::class)->middleware('throttle:public.forms')->name('transport-requests.store');
        Route::post('warehouse-requests', PublicWarehouseRequestController::class)->middleware('throttle:public.forms')->name('warehouse-requests.store');
    });

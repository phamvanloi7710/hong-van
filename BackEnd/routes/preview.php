<?php

use App\Http\Controllers\PublicSite\PageBuilderPreviewController;
use App\Http\Controllers\PublicSite\ThemePreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('preview')
    ->name('preview.')
    ->group(function (): void {
        Route::get('page-builder/{token}', PageBuilderPreviewController::class)
            ->where('token', '[A-Za-z0-9]{64}')
            ->middleware(['signed:relative', 'auth', 'throttle:preview.views'])
            ->name('page-builder');
        Route::get('theme/{theme:public_id}/{version:public_id}', ThemePreviewController::class)
            ->middleware(['signed', 'throttle:preview.views'])
            ->name('theme');
    });

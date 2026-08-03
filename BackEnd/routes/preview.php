<?php

use App\Http\Controllers\PublicSite\ThemePreviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('preview')
    ->name('preview.')
    ->group(function (): void {
        Route::get('theme/{theme:public_id}/{version:public_id}', ThemePreviewController::class)
            ->middleware('signed')
            ->name('theme');
    });

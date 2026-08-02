<?php

use Illuminate\Support\Facades\Route;

Route::prefix('preview')
    ->name('preview.')
    ->group(function (): void {
        // Preview endpoints must use signed, expiring URLs and noindex responses.
    });

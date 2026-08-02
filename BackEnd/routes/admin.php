<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin/v1')
    ->name('admin.api.v1.')
    ->group(function (): void {
        // Authenticated and authorized admin API routes are added by feature prompts.
    });

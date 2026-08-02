<?php

use App\Http\Controllers\AdminSpaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/{path?}', AdminSpaController::class)
    ->where('path', '.*')
    ->name('admin.spa');

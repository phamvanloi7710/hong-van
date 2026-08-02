<?php

use App\Http\Controllers\AdminSpaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('public.locale')->name('public.home');

Route::get('/{locale}', function () {
    return view('welcome');
})->whereIn('locale', config('localization.supported_locales', ['vi', 'en', 'zh']))
    ->middleware('public.locale')
    ->name('public.localized-home');

Route::get('/admin/{path?}', AdminSpaController::class)
    ->where('path', '.*')
    ->name('admin.spa');

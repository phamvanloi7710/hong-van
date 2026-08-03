<?php

use App\Http\Controllers\AdminSpaController;
use App\Http\Controllers\Seo\RobotsController;
use App\Http\Controllers\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('public.locale')->name('public.home');

Route::get('/{locale}', function () {
    return view('welcome');
})->whereIn('locale', config('localization.supported_locales', ['vi', 'en', 'zh']))
    ->middleware('public.locale')
    ->name('public.localized-home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap.index');
Route::get('/sitemaps/{name}.xml', [SitemapController::class, 'shard'])->where('name', '[a-z_]+-(vi|en|zh)')->name('public.sitemap.shard');
Route::get('/robots.txt', RobotsController::class)->name('public.robots');

Route::get('/admin/{path?}', AdminSpaController::class)
    ->where('path', '.*')
    ->name('admin.spa');

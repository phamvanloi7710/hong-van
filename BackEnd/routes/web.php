<?php

use App\Http\Controllers\AdminSpaController;
use App\Http\Controllers\PublicSite\PublicPageController;
use App\Http\Controllers\Seo\RobotsController;
use App\Http\Controllers\Seo\SitemapController;
use Illuminate\Support\Facades\Route;

Route::middleware('public.locale')->group(function (): void {
    Route::get('/', [PublicPageController::class, 'home'])->name('public.home');
    Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('public.privacy');
    Route::get('/terms', [PublicPageController::class, 'terms'])->name('public.terms');

    Route::get('/{locale}/privacy', [PublicPageController::class, 'privacy'])
        ->whereIn('locale', config('localization.supported_locales', ['vi', 'en', 'zh']))
        ->defaults('canonical_route', 'public.privacy')
        ->name('public.localized-privacy');
    Route::get('/{locale}/terms', [PublicPageController::class, 'terms'])
        ->whereIn('locale', config('localization.supported_locales', ['vi', 'en', 'zh']))
        ->defaults('canonical_route', 'public.terms')
        ->name('public.localized-terms');
    Route::get('/{locale}', [PublicPageController::class, 'home'])
        ->whereIn('locale', config('localization.supported_locales', ['vi', 'en', 'zh']))
        ->defaults('canonical_route', 'public.home')
        ->name('public.localized-home');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap.index');
Route::get('/sitemaps/{name}.xml', [SitemapController::class, 'shard'])->where('name', '[a-z_]+-(vi|en|zh)')->name('public.sitemap.shard');
Route::get('/robots.txt', RobotsController::class)->name('public.robots');

Route::get('/admin/{path?}', AdminSpaController::class)
    ->where('path', '.*')
    ->name('admin.spa');

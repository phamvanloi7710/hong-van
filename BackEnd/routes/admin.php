<?php

use App\Http\Controllers\Api\V1\Audit\AuditLogController;
use App\Http\Controllers\Api\V1\Auth\AuthSessionController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Identity\PermissionController;
use App\Http\Controllers\Api\V1\Identity\RoleController;
use App\Http\Controllers\Api\V1\Identity\UserController;
use App\Http\Controllers\Api\V1\Identity\UserPreferenceController;
use App\Http\Controllers\Api\V1\Localization\LocalizationController;
use App\Http\Controllers\Api\V1\Media\MediaContentController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\Media\MediaFolderController;
use App\Http\Controllers\Api\V1\Settings\CompanyDirectoryController;
use App\Http\Controllers\Api\V1\Settings\CompanySettingsController;
use App\Http\Controllers\Api\V1\SystemPingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/v1')
    ->name('admin.api.v1.')
    ->group(function (): void {
        Route::prefix('auth')->name('auth.')->group(function (): void {
            Route::post('login', [AuthSessionController::class, 'store'])
                ->middleware('throttle:auth.login')
                ->name('login');
            Route::post('forgot-password', ForgotPasswordController::class)
                ->middleware('throttle:auth.password')
                ->name('forgot-password');
            Route::post('reset-password', ResetPasswordController::class)
                ->middleware('throttle:auth.password')
                ->name('reset-password');

            Route::middleware('auth:sanctum')->group(function (): void {
                Route::get('me', [AuthSessionController::class, 'show'])->name('me');
                Route::post('logout', [AuthSessionController::class, 'destroy'])->name('logout');
            });
        });

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('preferences', [UserPreferenceController::class, 'show'])->name('preferences.show');
            Route::put('preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
            Route::delete('preferences', [UserPreferenceController::class, 'destroy'])->name('preferences.destroy');

            Route::get('system/ping', SystemPingController::class)
                ->middleware('can:system_health')
                ->name('system.ping');

            Route::prefix('identity')->name('identity.')->group(function (): void {
                Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view')->name('users.index');
                Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
                Route::get('users/{user:public_id}', [UserController::class, 'show'])->middleware('permission:users.view')->name('users.show');
                Route::put('users/{user:public_id}', [UserController::class, 'update'])->middleware('permission:users.update')->name('users.update');
                Route::delete('users/{user:public_id}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
                Route::post('users/{user:public_id}/activate', [UserController::class, 'activate'])->middleware('permission:users.update')->name('users.activate');
                Route::post('users/{user:public_id}/lock', [UserController::class, 'lock'])->middleware('permission:users.update')->name('users.lock');
                Route::post('users/{user:public_id}/reset-sessions', [UserController::class, 'resetSessions'])->middleware('permission:users.update')->name('users.reset-sessions');

                Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('roles.index');
                Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
                Route::get('roles/{role:public_id}', [RoleController::class, 'show'])->middleware('permission:roles.view')->name('roles.show');
                Route::put('roles/{role:public_id}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('roles.update');
                Route::delete('roles/{role:public_id}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');

                Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view')->name('permissions.index');
                Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create')->name('permissions.store');
                Route::get('permissions/{permission:public_id}', [PermissionController::class, 'show'])->middleware('permission:permissions.view')->name('permissions.show');
                Route::put('permissions/{permission:public_id}', [PermissionController::class, 'update'])->middleware('permission:permissions.update')->name('permissions.update');
                Route::delete('permissions/{permission:public_id}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete')->name('permissions.destroy');
            });

            Route::prefix('settings')->name('settings.')->group(function (): void {
                Route::get('/', [CompanySettingsController::class, 'index'])->middleware('permission:settings.view')->name('index');
                Route::put('groups/{settingGroup:key}', [CompanySettingsController::class, 'update'])->middleware('permission:settings.update')->name('groups.update');

                Route::post('branches', [CompanyDirectoryController::class, 'storeBranch'])->middleware('permission:settings.manage_settings')->name('branches.store');
                Route::put('branches/{branch:public_id}', [CompanyDirectoryController::class, 'updateBranch'])->middleware('permission:settings.manage_settings')->name('branches.update');
                Route::delete('branches/{branch:public_id}', [CompanyDirectoryController::class, 'deleteBranch'])->middleware('permission:settings.manage_settings')->name('branches.destroy');
                Route::put('business-hours/global', [CompanyDirectoryController::class, 'replaceGlobalBusinessHours'])->middleware('permission:settings.manage_settings')->name('business-hours.global');
                Route::put('branches/{branch:public_id}/business-hours', [CompanyDirectoryController::class, 'replaceBranchBusinessHours'])->middleware('permission:settings.manage_settings')->name('business-hours.branch');

                Route::post('social-links', [CompanyDirectoryController::class, 'storeSocialLink'])->middleware('permission:settings.manage_settings')->name('social-links.store');
                Route::put('social-links/{socialLink:public_id}', [CompanyDirectoryController::class, 'updateSocialLink'])->middleware('permission:settings.manage_settings')->name('social-links.update');
                Route::delete('social-links/{socialLink:public_id}', [CompanyDirectoryController::class, 'deleteSocialLink'])->middleware('permission:settings.manage_settings')->name('social-links.destroy');

                Route::post('contact-channels', [CompanyDirectoryController::class, 'storeContactChannel'])->middleware('permission:settings.manage_settings')->name('contact-channels.store');
                Route::put('contact-channels/{contactChannel:public_id}', [CompanyDirectoryController::class, 'updateContactChannel'])->middleware('permission:settings.manage_settings')->name('contact-channels.update');
                Route::delete('contact-channels/{contactChannel:public_id}', [CompanyDirectoryController::class, 'deleteContactChannel'])->middleware('permission:settings.manage_settings')->name('contact-channels.destroy');
            });

            Route::prefix('localization')->name('localization.')->group(function (): void {
                Route::get('/', [LocalizationController::class, 'index'])->middleware('permission:localization.view')->name('index');
                Route::put('languages/{language:public_id}', [LocalizationController::class, 'update'])->middleware('permission:localization.update')->name('languages.update');
            });

            Route::get('audit-logs', [AuditLogController::class, 'index'])
                ->middleware('permission:audit.view')
                ->name('audit-logs.index');

            Route::prefix('media')->name('media.')->group(function (): void {
                Route::get('folders', [MediaFolderController::class, 'index'])->middleware('permission:media.view')->name('folders.index');
                Route::post('folders', [MediaFolderController::class, 'store'])->middleware('permission:media.create')->name('folders.store');
                Route::patch('folders/{folder:public_id}', [MediaFolderController::class, 'update'])->middleware('permission:media.update')->name('folders.update');
                Route::patch('folders/{folder:public_id}/lock', [MediaFolderController::class, 'lock'])->middleware('permission:media.update')->name('folders.lock');
                Route::get('/', [MediaController::class, 'index'])->middleware('permission:media.view')->name('index');
                Route::post('/', [MediaController::class, 'store'])->middleware(['permission:media.create', 'throttle:uploads'])->name('store');
                Route::get('{media:public_id}', [MediaController::class, 'show'])->middleware('permission:media.view')->name('show');
                Route::get('{media:public_id}/content', MediaContentController::class)->middleware('permission:media.view')->name('content');
                Route::patch('{media:public_id}', [MediaController::class, 'update'])->middleware('permission:media.update')->name('update');
                Route::patch('{media:public_id}/move', [MediaController::class, 'move'])->middleware('permission:media.update')->name('move');
                Route::patch('{media:public_id}/lock', [MediaController::class, 'lock'])->middleware('permission:media.update')->name('lock');
                Route::patch('{media:public_id}/visibility', [MediaController::class, 'visibility'])->middleware('permission:media.update')->name('visibility');
                Route::post('{media:public_id}/trash', [MediaController::class, 'trash'])->middleware('permission:media.delete')->name('trash');
                Route::post('{media:public_id}/restore', [MediaController::class, 'restore'])->withTrashed()->middleware('permission:media.restore')->name('restore');
                Route::post('{media:public_id}/retry', [MediaController::class, 'retry'])->middleware('permission:media.update')->name('retry');
                Route::delete('{media:public_id}', [MediaController::class, 'destroy'])->withTrashed()->middleware('permission:media.delete')->name('destroy');
            });
        });
    });

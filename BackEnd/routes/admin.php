<?php

use App\Http\Controllers\Api\V1\Audit\AuditLogController;
use App\Http\Controllers\Api\V1\Auth\AuthSessionController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\CropSolutions\CropReferenceController;
use App\Http\Controllers\Api\V1\CropSolutions\CropSolutionController;
use App\Http\Controllers\Api\V1\Identity\PermissionController;
use App\Http\Controllers\Api\V1\Identity\RoleController;
use App\Http\Controllers\Api\V1\Identity\UserController;
use App\Http\Controllers\Api\V1\Identity\UserPreferenceController;
use App\Http\Controllers\Api\V1\Localization\LocalizationController;
use App\Http\Controllers\Api\V1\Media\MediaContentController;
use App\Http\Controllers\Api\V1\Media\MediaController;
use App\Http\Controllers\Api\V1\Media\MediaFolderController;
use App\Http\Controllers\Api\V1\Products\BrandController;
use App\Http\Controllers\Api\V1\Products\ProductAttributeController;
use App\Http\Controllers\Api\V1\Products\ProductCategoryController;
use App\Http\Controllers\Api\V1\Products\ProductController;
use App\Http\Controllers\Api\V1\Products\ProductTagController;
use App\Http\Controllers\Api\V1\Services\ServiceCategoryController;
use App\Http\Controllers\Api\V1\Services\ServiceController;
use App\Http\Controllers\Api\V1\Settings\CompanyDirectoryController;
use App\Http\Controllers\Api\V1\Settings\CompanySettingsController;
use App\Http\Controllers\Api\V1\SystemPingController;
use App\Http\Controllers\Api\V1\Transportation\TransportationController;
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

            Route::prefix('products')->name('products.')->group(function (): void {
                Route::get('/', [ProductController::class, 'index'])->middleware('permission:products.view')->name('index');
                Route::post('/', [ProductController::class, 'store'])->middleware('permission:products.create')->name('store');
                Route::post('bulk', [ProductController::class, 'bulk'])->middleware('permission:products.view')->name('bulk');
                Route::get('categories', [ProductCategoryController::class, 'index'])->middleware('permission:products.view')->name('categories.index');
                Route::post('categories', [ProductCategoryController::class, 'store'])->middleware('permission:products.create')->name('categories.store');
                Route::get('categories/{category:public_id}', [ProductCategoryController::class, 'show'])->middleware('permission:products.view')->name('categories.show');
                Route::put('categories/{category:public_id}', [ProductCategoryController::class, 'update'])->middleware('permission:products.update')->name('categories.update');
                Route::delete('categories/{category:public_id}', [ProductCategoryController::class, 'destroy'])->middleware('permission:products.delete')->name('categories.destroy');
                Route::post('categories/{category:public_id}/restore', [ProductCategoryController::class, 'restore'])->withTrashed()->middleware('permission:products.restore')->name('categories.restore');
                Route::get('brands', [BrandController::class, 'index'])->middleware('permission:products.view')->name('brands.index');
                Route::post('brands', [BrandController::class, 'store'])->middleware('permission:products.create')->name('brands.store');
                Route::put('brands/{brand:public_id}', [BrandController::class, 'update'])->middleware('permission:products.update')->name('brands.update');
                Route::delete('brands/{brand:public_id}', [BrandController::class, 'destroy'])->middleware('permission:products.delete')->name('brands.destroy');
                Route::post('brands/{brand:public_id}/restore', [BrandController::class, 'restore'])->withTrashed()->middleware('permission:products.restore')->name('brands.restore');
                Route::get('tags', [ProductTagController::class, 'index'])->middleware('permission:products.view')->name('tags.index');
                Route::post('tags', [ProductTagController::class, 'store'])->middleware('permission:products.create')->name('tags.store');
                Route::put('tags/{tag:public_id}', [ProductTagController::class, 'update'])->middleware('permission:products.update')->name('tags.update');
                Route::delete('tags/{tag:public_id}', [ProductTagController::class, 'destroy'])->middleware('permission:products.delete')->name('tags.destroy');
                Route::get('attributes', [ProductAttributeController::class, 'index'])->middleware('permission:products.view')->name('attributes.index');
                Route::post('attributes', [ProductAttributeController::class, 'store'])->middleware('permission:products.create')->name('attributes.store');
                Route::put('attributes/{attribute:public_id}', [ProductAttributeController::class, 'update'])->middleware('permission:products.update')->name('attributes.update');
                Route::delete('attributes/{attribute:public_id}', [ProductAttributeController::class, 'destroy'])->middleware('permission:products.delete')->name('attributes.destroy');
                Route::get('{product:public_id}', [ProductController::class, 'show'])->middleware('permission:products.view')->name('show');
                Route::put('{product:public_id}', [ProductController::class, 'update'])->middleware('permission:products.update')->name('update');
                Route::post('{product:public_id}/publish', [ProductController::class, 'publish'])->middleware('permission:products.publish')->name('publish');
                Route::post('{product:public_id}/archive', [ProductController::class, 'archive'])->middleware('permission:products.update')->name('archive');
                Route::delete('{product:public_id}', [ProductController::class, 'trash'])->middleware('permission:products.delete')->name('trash');
                Route::post('{product:public_id}/restore', [ProductController::class, 'restore'])->withTrashed()->middleware('permission:products.restore')->name('restore');
            });

            Route::prefix('crop-solutions')->name('crop-solutions.')->group(function (): void {
                Route::get('/', [CropSolutionController::class, 'index'])->middleware('permission:crop_solutions.view')->name('index');
                Route::post('/', [CropSolutionController::class, 'store'])->middleware('permission:crop_solutions.create')->name('store');
                Route::get('categories', [CropReferenceController::class, 'categories'])->middleware('permission:crops.view')->name('categories.index');
                Route::post('categories', [CropReferenceController::class, 'storeCategory'])->middleware('permission:crops.create')->name('categories.store');
                Route::put('categories/{category:public_id}', [CropReferenceController::class, 'updateCategory'])->middleware('permission:crops.update')->name('categories.update');
                Route::delete('categories/{category:public_id}', [CropReferenceController::class, 'deleteCategory'])->middleware('permission:crops.delete')->name('categories.destroy');
                Route::get('crops', [CropReferenceController::class, 'crops'])->middleware('permission:crops.view')->name('crops.index');
                Route::post('crops', [CropReferenceController::class, 'storeCrop'])->middleware('permission:crops.create')->name('crops.store');
                Route::put('crops/{crop:public_id}', [CropReferenceController::class, 'updateCrop'])->middleware('permission:crops.update')->name('crops.update');
                Route::delete('crops/{crop:public_id}', [CropReferenceController::class, 'deleteCrop'])->middleware('permission:crops.delete')->name('crops.destroy');
                Route::get('stages', [CropReferenceController::class, 'stages'])->middleware('permission:crops.view')->name('stages.index');
                Route::post('stages', [CropReferenceController::class, 'storeStage'])->middleware('permission:crops.create')->name('stages.store');
                Route::put('stages/{stage:public_id}', [CropReferenceController::class, 'updateStage'])->middleware('permission:crops.update')->name('stages.update');
                Route::delete('stages/{stage:public_id}', [CropReferenceController::class, 'deleteStage'])->middleware('permission:crops.delete')->name('stages.destroy');
                Route::get('{solution:public_id}', [CropSolutionController::class, 'show'])->middleware('permission:crop_solutions.view')->name('show');
                Route::put('{solution:public_id}', [CropSolutionController::class, 'update'])->middleware('permission:crop_solutions.update')->name('update');
                Route::post('{solution:public_id}/publish', [CropSolutionController::class, 'publish'])->middleware('permission:crop_solutions.publish')->name('publish');
                Route::post('{solution:public_id}/archive', [CropSolutionController::class, 'archive'])->middleware('permission:crop_solutions.update')->name('archive');
                Route::delete('{solution:public_id}', [CropSolutionController::class, 'trash'])->middleware('permission:crop_solutions.delete')->name('trash');
            });

            Route::prefix('services')->name('services.')->group(function (): void {
                Route::get('/', [ServiceController::class, 'index'])->middleware('permission:services.view')->name('index');
                Route::post('/', [ServiceController::class, 'store'])->middleware('permission:services.create')->name('store');
                Route::get('categories', [ServiceCategoryController::class, 'index'])->middleware('permission:services.view')->name('categories.index');
                Route::post('categories', [ServiceCategoryController::class, 'store'])->middleware('permission:services.create')->name('categories.store');
                Route::put('categories/{category:public_id}', [ServiceCategoryController::class, 'update'])->middleware('permission:services.update')->name('categories.update');
                Route::delete('categories/{category:public_id}', [ServiceCategoryController::class, 'destroy'])->middleware('permission:services.delete')->name('categories.destroy');
                Route::get('{service:public_id}', [ServiceController::class, 'show'])->middleware('permission:services.view')->name('show');
                Route::put('{service:public_id}', [ServiceController::class, 'update'])->middleware('permission:services.update')->name('update');
                Route::post('{service:public_id}/publish', [ServiceController::class, 'publish'])->middleware('permission:services.publish')->name('publish');
                Route::post('{service:public_id}/archive', [ServiceController::class, 'archive'])->middleware('permission:services.update')->name('archive');
                Route::delete('{service:public_id}', [ServiceController::class, 'destroy'])->middleware('permission:services.delete')->name('destroy');
                Route::post('{service}/restore', [ServiceController::class, 'restore'])->middleware('permission:services.restore')->name('restore');
            });

            Route::prefix('transportation')->name('transportation.')->group(function (): void {
                Route::get('types', [TransportationController::class, 'types'])->middleware('permission:transportation.view')->name('types.index');
                Route::post('types', [TransportationController::class, 'saveType'])->middleware('permission:transportation.create')->name('types.store');
                Route::put('types/{type:public_id}', [TransportationController::class, 'saveType'])->middleware('permission:transportation.update')->name('types.update');
                Route::get('vehicles', [TransportationController::class, 'vehicles'])->middleware('permission:transportation.view')->name('vehicles.index');
                Route::post('vehicles', [TransportationController::class, 'saveVehicle'])->middleware('permission:transportation.create')->name('vehicles.store');
                Route::put('vehicles/{vehicle:public_id}', [TransportationController::class, 'saveVehicle'])->middleware('permission:transportation.update')->name('vehicles.update');
                Route::get('routes', [TransportationController::class, 'routes'])->middleware('permission:transportation.view')->name('routes.index');
                Route::post('routes', [TransportationController::class, 'saveRoute'])->middleware('permission:transportation.create')->name('routes.store');
                Route::put('routes/{route:public_id}', [TransportationController::class, 'saveRoute'])->middleware('permission:transportation.update')->name('routes.update');
                Route::get('areas', [TransportationController::class, 'areas'])->middleware('permission:transportation.view')->name('areas.index');
                Route::post('areas', [TransportationController::class, 'saveArea'])->middleware('permission:transportation.create')->name('areas.store');
                Route::put('areas/{area:public_id}', [TransportationController::class, 'saveArea'])->middleware('permission:transportation.update')->name('areas.update');
                Route::post('{kind}/{publicId}/publish', [TransportationController::class, 'publish'])->whereIn('kind', ['vehicles', 'routes', 'areas'])->middleware('permission:transportation.publish')->name('publish');
                Route::delete('{kind}/{publicId}', [TransportationController::class, 'destroy'])->whereIn('kind', ['types', 'vehicles', 'routes', 'areas'])->middleware('permission:transportation.delete')->name('destroy');
            });

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

<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Domain\Services\ServiceManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Services\SaveServiceCategoryRequest;
use App\Http\Resources\Api\V1\Services\ServiceCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ServiceCategoryController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', ServiceCategory::class);
        $categories = ServiceCategory::query()
            ->with(['translations', 'parent.translations'])
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $response->success(ServiceCategoryResource::collection($categories)->resolve($request));
    }

    public function store(SaveServiceCategoryRequest $request, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', ServiceCategory::class);
        $category = $manager->saveCategory($this->actor($request), null, $request->validated());

        return $response->success(ServiceCategoryResource::make($category)->resolve($request), __('services.category_created'), 201);
    }

    public function update(SaveServiceCategoryRequest $request, ServiceCategory $category, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $category);
        $category = $manager->saveCategory($this->actor($request), $category, $request->validated());

        return $response->success(ServiceCategoryResource::make($category)->resolve($request), __('services.category_updated'));
    }

    public function destroy(Request $request, ServiceCategory $category, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $category);
        $manager->trashCategory($this->actor($request), $category);

        return $response->success(null, __('services.category_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

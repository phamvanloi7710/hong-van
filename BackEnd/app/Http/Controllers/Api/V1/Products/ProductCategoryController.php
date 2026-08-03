<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Domain\Products\ProductCatalogManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Products\SaveProductCategoryRequest;
use App\Http\Resources\Api\V1\Products\ProductCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ProductCategoryController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', ProductCategory::class);
        $categories = ProductCategory::query()->with(['translations', 'parent'])->withCount('products')->orderBy('sort_order')->orderBy('code')->get();

        return $response->success(ProductCategoryResource::collection($categories)->resolve($request));
    }

    public function store(SaveProductCategoryRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', ProductCategory::class);
        $category = $manager->saveCategory($this->actor($request), null, $request->validated());

        return $response->success(ProductCategoryResource::make($category)->resolve($request), __('products.category_created'), 201);
    }

    public function show(Request $request, ProductCategory $category, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $category);

        return $response->success(ProductCategoryResource::make($category->load(['translations', 'parent'])->loadCount('products'))->resolve($request));
    }

    public function update(SaveProductCategoryRequest $request, ProductCategory $category, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $category);
        $category = $manager->saveCategory($this->actor($request), $category, $request->validated());

        return $response->success(ProductCategoryResource::make($category)->resolve($request), __('products.category_updated'));
    }

    public function destroy(Request $request, ProductCategory $category, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $category);
        $manager->trashCategory($this->actor($request), $category);

        return $response->success(message: __('products.category_trashed'));
    }

    public function restore(Request $request, ProductCategory $category, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('restore', $category);
        $category = $manager->restoreCategory($this->actor($request), $category);

        return $response->success(ProductCategoryResource::make($category)->resolve($request), __('products.category_restored'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

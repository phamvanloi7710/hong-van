<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Domain\Products\ProductCatalogManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Products\SaveBrandRequest;
use App\Http\Resources\Api\V1\Products\BrandResource;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class BrandController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Brand::class);
        $brands = Brand::query()->with(['translations', 'logo'])->withCount('products')->orderBy('sort_order')->orderBy('code')->get();

        return $response->success(BrandResource::collection($brands)->resolve($request));
    }

    public function store(SaveBrandRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Brand::class);
        $brand = $manager->saveBrand($this->actor($request), null, $request->validated());

        return $response->success(BrandResource::make($brand)->resolve($request), __('products.brand_created'), 201);
    }

    public function update(SaveBrandRequest $request, Brand $brand, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $brand);
        $brand = $manager->saveBrand($this->actor($request), $brand, $request->validated());

        return $response->success(BrandResource::make($brand)->resolve($request), __('products.brand_updated'));
    }

    public function destroy(Request $request, Brand $brand, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $brand);
        $manager->trashBrand($this->actor($request), $brand);

        return $response->success(message: __('products.brand_trashed'));
    }

    public function restore(Request $request, Brand $brand, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('restore', $brand);
        $brand = $manager->restoreBrand($this->actor($request), $brand);

        return $response->success(BrandResource::make($brand)->resolve($request), __('products.brand_restored'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Domain\Products\ProductCatalogManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Products\SaveProductAttributeRequest;
use App\Http\Resources\Api\V1\Products\ProductAttributeResource;
use App\Http\Responses\ApiResponse;
use App\Models\ProductAttributeDefinition;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ProductAttributeController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', ProductAttributeDefinition::class);
        $attributes = ProductAttributeDefinition::query()->withCount('values')->orderBy('sort_order')->orderBy('name')->get();

        return $response->success(ProductAttributeResource::collection($attributes)->resolve($request));
    }

    public function store(SaveProductAttributeRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', ProductAttributeDefinition::class);
        $attribute = $manager->saveAttribute($this->actor($request), null, $request->validated());

        return $response->success(ProductAttributeResource::make($attribute)->resolve($request), __('products.attribute_created'), 201);
    }

    public function update(SaveProductAttributeRequest $request, ProductAttributeDefinition $attribute, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $attribute);
        $attribute = $manager->saveAttribute($this->actor($request), $attribute, $request->validated());

        return $response->success(ProductAttributeResource::make($attribute)->resolve($request), __('products.attribute_updated'));
    }

    public function destroy(Request $request, ProductAttributeDefinition $attribute, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $attribute);
        $manager->deleteAttribute($this->actor($request), $attribute);

        return $response->success(message: __('products.attribute_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

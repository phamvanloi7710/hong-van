<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Domain\Products\ProductCatalogManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Products\SaveProductTagRequest;
use App\Http\Resources\Api\V1\Products\ProductTagResource;
use App\Http\Responses\ApiResponse;
use App\Models\ProductTag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ProductTagController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', ProductTag::class);
        $tags = ProductTag::query()->withCount('products')->orderBy('name')->get();

        return $response->success(ProductTagResource::collection($tags)->resolve($request));
    }

    public function store(SaveProductTagRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', ProductTag::class);
        $tag = $manager->saveTag($this->actor($request), null, $request->validated());

        return $response->success(ProductTagResource::make($tag)->resolve($request), __('products.tag_created'), 201);
    }

    public function update(SaveProductTagRequest $request, ProductTag $tag, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $tag);
        $tag = $manager->saveTag($this->actor($request), $tag, $request->validated());

        return $response->success(ProductTagResource::make($tag)->resolve($request), __('products.tag_updated'));
    }

    public function destroy(Request $request, ProductTag $tag, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $tag);
        $manager->deleteTag($this->actor($request), $tag);

        return $response->success(message: __('products.tag_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

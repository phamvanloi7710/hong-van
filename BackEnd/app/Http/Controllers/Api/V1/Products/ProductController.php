<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Domain\Products\ProductCatalogManager;
use App\Domain\Products\ProductCatalogQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Products\BulkProductsRequest;
use App\Http\Requests\Api\V1\Products\IndexProductsRequest;
use App\Http\Requests\Api\V1\Products\SaveProductRequest;
use App\Http\Resources\Api\V1\Products\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ProductController extends Controller
{
    public function index(IndexProductsRequest $request, ProductCatalogQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(ProductResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(SaveProductRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Product::class);
        $product = $manager->createProduct($this->actor($request), $request->validated());

        return $response->success(ProductResource::make($product)->resolve($request), __('products.created'), 201);
    }

    public function show(Request $request, Product $product, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $product);
        $product->load([
            'translations', 'category.translations', 'category.parent:id,public_id',
            'brand.translations', 'brand.logo:id,public_id', 'tags', 'media',
            'attributeValues.definition', 'specifications', 'relatedProducts.translations',
        ]);

        return $response->success(ProductResource::make($product)->resolve($request));
    }

    public function update(SaveProductRequest $request, Product $product, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $product);
        $product = $manager->updateProduct($this->actor($request), $product, $request->validated());

        return $response->success(ProductResource::make($product)->resolve($request), __('products.updated'));
    }

    public function trash(Request $request, Product $product, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $product);

        return $response->success(ProductResource::make($manager->trashProduct($this->actor($request), $product))->resolve($request), __('products.trashed'));
    }

    public function restore(Request $request, Product $product, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('restore', $product);

        return $response->success(ProductResource::make($manager->restoreProduct($this->actor($request), $product))->resolve($request), __('products.restored'));
    }

    public function publish(Request $request, Product $product, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $product);

        return $response->success(ProductResource::make($manager->publishProduct($this->actor($request), $product))->resolve($request), __('products.published'));
    }

    public function archive(Request $request, Product $product, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $product);

        return $response->success(ProductResource::make($manager->archiveProduct($this->actor($request), $product))->resolve($request), __('products.archived'));
    }

    public function bulk(BulkProductsRequest $request, ProductCatalogManager $manager, ApiResponse $response): JsonResponse
    {
        $data = $request->validated();
        $products = Product::query()->whereIn('public_id', $data['product_ids'])->get()->all();

        foreach ($products as $product) {
            Gate::authorize($data['action'] === 'publish' ? 'publish' : 'update', $product);
        }

        $manager->bulkStatus($this->actor($request), $products, $data['action']);

        return $response->success(['updated' => count($products)], __('products.bulk_updated'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Posts;

use App\Domain\Posts\PostManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Posts\SavePostCategoryRequest;
use App\Http\Requests\Api\V1\Posts\SavePostTagRequest;
use App\Http\Resources\Api\V1\Posts\PostTaxonomyResource;
use App\Http\Responses\ApiResponse;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PostTaxonomyController extends Controller
{
    public function categories(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', PostCategory::class);
        $items = PostCategory::query()->with(['translations', 'parent.translations'])->withCount('posts')->orderBy('sort_order')->orderBy('id')->get();

        return $response->success(PostTaxonomyResource::collection($items)->resolve($request));
    }

    public function storeCategory(SavePostCategoryRequest $request, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', PostCategory::class);
        $item = $manager->saveCategory($this->actor($request), null, $request->validated());

        return $response->success(PostTaxonomyResource::make($item)->resolve($request), __('posts.category_created'), 201);
    }

    public function updateCategory(SavePostCategoryRequest $request, PostCategory $category, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $category);
        $item = $manager->saveCategory($this->actor($request), $category, $request->validated());

        return $response->success(PostTaxonomyResource::make($item)->resolve($request), __('posts.category_updated'));
    }

    public function deleteCategory(Request $request, PostCategory $category, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $category);
        $manager->trashCategory($this->actor($request), $category);

        return $response->success(null, __('posts.category_deleted'));
    }

    public function tags(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', PostTag::class);
        $items = PostTag::query()->with('translations')->withCount('posts')->orderBy('sort_order')->orderBy('id')->get();

        return $response->success(PostTaxonomyResource::collection($items)->resolve($request));
    }

    public function storeTag(SavePostTagRequest $request, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', PostTag::class);
        $item = $manager->saveTag($this->actor($request), null, $request->validated());

        return $response->success(PostTaxonomyResource::make($item)->resolve($request), __('posts.tag_created'), 201);
    }

    public function updateTag(SavePostTagRequest $request, PostTag $tag, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $tag);
        $item = $manager->saveTag($this->actor($request), $tag, $request->validated());

        return $response->success(PostTaxonomyResource::make($item)->resolve($request), __('posts.tag_updated'));
    }

    public function deleteTag(Request $request, PostTag $tag, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $tag);
        $manager->trashTag($this->actor($request), $tag);

        return $response->success(null, __('posts.tag_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

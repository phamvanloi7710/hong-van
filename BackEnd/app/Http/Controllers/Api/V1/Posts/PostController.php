<?php

namespace App\Http\Controllers\Api\V1\Posts;

use App\Domain\Posts\PostManager;
use App\Domain\Posts\PostQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Posts\IndexPostsRequest;
use App\Http\Requests\Api\V1\Posts\SavePostRequest;
use App\Http\Resources\Api\V1\Posts\PostResource;
use App\Http\Responses\ApiResponse;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PostController extends Controller
{
    public function index(IndexPostsRequest $request, PostQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Post::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(PostResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function authors(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Post::class);
        $authors = User::query()->where('is_active', true)->whereNull('locked_at')
            ->orderBy('name')->orderBy('id')->get(['public_id', 'name', 'email'])
            ->map(static fn (User $user): array => [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values()->all();

        return $response->success($authors);
    }

    public function store(SavePostRequest $request, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Post::class);
        $post = $manager->savePost($this->actor($request), null, $request->validated());

        return $response->success(PostResource::make($post)->resolve($request), __('posts.created'), 201);
    }

    public function show(Request $request, Post $post, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $post);
        $post->load(['translations', 'category.translations', 'tags.translations', 'author', 'featuredMedia']);

        return $response->success(PostResource::make($post)->resolve($request));
    }

    public function update(SavePostRequest $request, Post $post, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $post);
        $post = $manager->savePost($this->actor($request), $post, $request->validated());

        return $response->success(PostResource::make($post)->resolve($request), __('posts.updated'));
    }

    public function publish(Request $request, Post $post, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $post);

        return $response->success(PostResource::make($manager->publish($this->actor($request), $post))->resolve($request), __('posts.published'));
    }

    public function archive(Request $request, Post $post, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $post);

        return $response->success(PostResource::make($manager->archive($this->actor($request), $post))->resolve($request), __('posts.archived'));
    }

    public function destroy(Request $request, Post $post, PostManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $post);
        $manager->trashPost($this->actor($request), $post->load('featuredMedia'));

        return $response->success(null, __('posts.deleted'));
    }

    public function restore(Request $request, string $post, PostManager $manager, ApiResponse $response): JsonResponse
    {
        $model = Post::onlyTrashed()->where('public_id', $post)->with('featuredMedia')->firstOrFail();
        Gate::authorize('restore', $model);

        return $response->success(PostResource::make($manager->restorePost($this->actor($request), $model))->resolve($request), __('posts.restored'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

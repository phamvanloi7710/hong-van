<?php

namespace App\Http\Controllers\Api\V1\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\PageBuilderCacheKeys;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageManager;
use App\Domain\PageBuilder\PageQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PageBuilder\IndexPagesRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageDraftRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageRequest;
use App\Http\Resources\Api\V1\PageBuilder\PageResource;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PageBuilderController extends Controller
{
    public function registry(BlockRegistry $registry, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);

        return $response->success([
            'document' => PageDocumentSchema::metadata(),
            'blocks' => $registry->metadata(),
            'cache' => PageBuilderCacheKeys::metadata(),
        ]);
    }

    public function index(IndexPagesRequest $request, PageQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(PageResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(SavePageRequest $request, PageManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Page::class);
        $page = $manager->saveMetadata($this->actor($request), null, $request->validated());

        return $response->success(PageResource::make($page)->resolve($request), __('page_builder.created'), 201);
    }

    public function show(Request $request, Page $page, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $page);
        $page->load(['translations', 'draftVersion', 'publishedVersion']);

        return $response->success(PageResource::make($page)->resolve($request));
    }

    public function update(SavePageRequest $request, Page $page, PageManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $page = $manager->saveMetadata($this->actor($request), $page, $request->validated());

        return $response->success(PageResource::make($page)->resolve($request), __('page_builder.updated'));
    }

    public function saveDraft(SavePageDraftRequest $request, Page $page, PageManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $manager->saveDraft($this->actor($request), $page, (array) $request->validated('document'));
        $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);

        return $response->success(PageResource::make($page)->resolve($request), __('page_builder.draft_saved'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

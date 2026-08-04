<?php

namespace App\Http\Controllers\Api\V1\PageBuilder;

use App\Domain\PageBuilder\BlockRegistry;
use App\Domain\PageBuilder\DataSourceRegistry;
use App\Domain\PageBuilder\FormRegistry;
use App\Domain\PageBuilder\PageBuilderCacheKeys;
use App\Domain\PageBuilder\PageDocumentSchema;
use App\Domain\PageBuilder\PageLockManager;
use App\Domain\PageBuilder\PageManager;
use App\Domain\PageBuilder\PagePublishingManager;
use App\Domain\PageBuilder\PageQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PageBuilder\IndexPagesRequest;
use App\Http\Requests\Api\V1\PageBuilder\PublishPageRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageDraftRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageRequest;
use App\Http\Requests\Api\V1\PageBuilder\SchedulePagePublishRequest;
use App\Http\Resources\Api\V1\PageBuilder\PageResource;
use App\Http\Resources\Api\V1\PageBuilder\PageVersionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\PageVersion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PageBuilderController extends Controller
{
    public function registry(BlockRegistry $registry, DataSourceRegistry $dataSources, FormRegistry $forms, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);

        return $response->success([
            'document' => PageDocumentSchema::metadata(),
            'blocks' => $registry->metadata(),
            'dataSources' => $dataSources->metadata(),
            'forms' => $forms->metadata(),
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

    public function saveDraft(SavePageDraftRequest $request, Page $page, PageManager $manager, PageLockManager $locks, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $locks->assertCanEdit($this->actor($request), $page, $request->validated('lock_token'));
        $manager->saveDraft(
            $this->actor($request),
            $page,
            (array) $request->validated('document'),
            $request->validated('expected_checksum'),
            $request->validated('expected_version_id'),
        );
        $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);

        return $response->success(PageResource::make($page)->resolve($request), __('page_builder.draft_saved'));
    }

    public function versions(Request $request, Page $page, PagePublishingManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $page);

        return $response->success(PageVersionResource::collection($manager->versions($page))->resolve($request));
    }

    public function saveVersion(PublishPageRequest $request, Page $page, PagePublishingManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $manager->saveMilestone($this->actor($request), $page, (string) $request->validated('expected_checksum'), (string) $request->validated('expected_version_id'), $request->validated('note'));

        return $this->freshPage($request, $page, $response, 'Version milestone saved.');
    }

    public function publish(PublishPageRequest $request, Page $page, PagePublishingManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $page);
        $manager->publish($this->actor($request), $page, (string) $request->validated('expected_checksum'), (string) $request->validated('expected_version_id'), $request->validated('note'));

        return $this->freshPage($request, $page, $response, 'Page published.');
    }

    public function schedule(SchedulePagePublishRequest $request, Page $page, PagePublishingManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $page);
        $scheduledAt = CarbonImmutable::parse((string) $request->validated('scheduled_at'), (string) $request->validated('timezone'))->utc();
        $schedule = $manager->schedule($this->actor($request), $page, (string) $request->validated('expected_checksum'), (string) $request->validated('expected_version_id'), $scheduledAt, $request->validated('note'));

        return $response->success(['public_id' => $schedule->public_id, 'status' => $schedule->status, 'scheduled_at' => $schedule->scheduled_at->utc()->toISOString()], 'Publication scheduled.', 201);
    }

    public function rollback(Request $request, Page $page, PageVersion $pageVersion, PagePublishingManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $page);
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:500']]);
        $manager->rollback($this->actor($request), $page, $pageVersion, $validated['note'] ?? null);

        return $this->freshPage($request, $page, $response, 'Version restored and published as a new version.');
    }

    private function freshPage(Request $request, Page $page, ApiResponse $response, string $message): JsonResponse
    {
        $page->refresh()->load(['translations', 'draftVersion', 'publishedVersion']);

        return $response->success(PageResource::make($page)->resolve($request), $message);
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

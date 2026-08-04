<?php

namespace App\Http\Controllers\Api\V1\PageBuilder;

use App\Domain\PageBuilder\PageImportExportManager;
use App\Domain\PageBuilder\PageTemplateManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PageBuilder\ImportPageRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageRequest;
use App\Http\Requests\Api\V1\PageBuilder\SavePageTemplateRequest;
use App\Http\Requests\Api\V1\PageBuilder\ValidatePageImportRequest;
use App\Http\Resources\Api\V1\PageBuilder\PageResource;
use App\Http\Resources\Api\V1\PageBuilder\PageTemplateResource;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PageTemplateController extends Controller
{
    public function index(PageTemplateManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Page::class);

        return $response->success(PageTemplateResource::collection($manager->library())->resolve(request()));
    }

    public function store(SavePageTemplateRequest $request, Page $page, PageTemplateManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $template = $manager->saveFromPage($this->actor($request), $page->load('draftVersion'), $request->validated());

        return $response->success(PageTemplateResource::make($template)->resolve($request), 'Template created.', 201);
    }

    public function createPage(SavePageRequest $request, PageTemplate $template, PageTemplateManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Page::class);
        $page = $manager->createPage($this->actor($request), $template->load('publishedVersion'), $request->validated());

        return $response->success(PageResource::make($page)->resolve($request), 'Page created from template.', 201);
    }

    public function duplicate(SavePageRequest $request, Page $page, PageTemplateManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Page::class);
        $copy = $manager->duplicatePage($this->actor($request), $page->load('draftVersion'), $request->validated());

        return $response->success(PageResource::make($copy)->resolve($request), 'Page duplicated.', 201);
    }

    public function export(Page $page, PageImportExportManager $manager): JsonResponse
    {
        Gate::authorize('export', $page);
        $payload = $manager->export($page->load(['translations', 'draftVersion']));

        return response()->json($payload, 200, ['Content-Disposition' => 'attachment; filename="page-'.$page->public_id.'.json"'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function validateImport(ValidatePageImportRequest $request, PageImportExportManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('import', Page::class);
        $validated = $manager->validate((array) $request->validated('payload'), (array) $request->validated('media_map', []));

        return $response->success($validated['report'], 'Import payload is valid.');
    }

    public function import(ImportPageRequest $request, PageImportExportManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('import', Page::class);
        $metadata = collect($request->validated())->only(['code', 'type', 'is_home', 'translations'])->all();
        $page = $manager->import($this->actor($request), (array) $request->validated('payload'), $metadata, (array) $request->validated('media_map', []));

        return $response->success(PageResource::make($page)->resolve($request), 'Page imported.', 201);
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

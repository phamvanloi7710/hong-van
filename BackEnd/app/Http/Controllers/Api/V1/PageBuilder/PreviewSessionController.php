<?php

namespace App\Http\Controllers\Api\V1\PageBuilder;

use App\Domain\PageBuilder\PagePreviewSessionManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PageBuilder\CreatePreviewSessionRequest;
use App\Http\Requests\Api\V1\PageBuilder\PreviewSessionTokenRequest;
use App\Http\Requests\Api\V1\PageBuilder\UpdatePreviewSessionRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\PagePreviewSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PreviewSessionController extends Controller
{
    public function store(CreatePreviewSessionRequest $request, Page $page, PagePreviewSessionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $page);
        $payload = $manager->create(
            $this->actor($request),
            $page,
            (array) $request->validated('document'),
            (string) $request->validated('locale'),
        );

        return $response->success($payload, __('page_builder.preview_session.created'), 201);
    }

    public function update(UpdatePreviewSessionRequest $request, PagePreviewSession $pagePreviewSession, PagePreviewSessionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $pagePreviewSession->page);
        $payload = $manager->update(
            $this->actor($request),
            $pagePreviewSession,
            (string) $request->validated('token'),
            (array) $request->validated('document'),
        );

        return $response->success($payload, __('page_builder.preview_session.updated'));
    }

    public function refresh(PreviewSessionTokenRequest $request, PagePreviewSession $pagePreviewSession, PagePreviewSessionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $pagePreviewSession->page);
        $payload = $manager->refresh($this->actor($request), $pagePreviewSession, (string) $request->validated('token'));

        return $response->success($payload, __('page_builder.preview_session.refreshed'));
    }

    public function destroy(PreviewSessionTokenRequest $request, PagePreviewSession $pagePreviewSession, PagePreviewSessionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $pagePreviewSession->page);
        $manager->close($this->actor($request), $pagePreviewSession, (string) $request->validated('token'));

        return $response->success(null, __('page_builder.preview_session.closed'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

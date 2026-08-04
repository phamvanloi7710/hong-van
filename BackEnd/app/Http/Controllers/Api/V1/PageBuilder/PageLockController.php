<?php

namespace App\Http\Controllers\Api\V1\PageBuilder;

use App\Domain\PageBuilder\PageLockManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PageBuilder\PageLockRequest;
use App\Http\Resources\Api\V1\PageBuilder\PageLockResource;
use App\Http\Responses\ApiResponse;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PageLockController extends Controller
{
    public function show(Page $page, PageLockManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $page);

        return $response->success(PageLockResource::make($manager->current($page))->resolve(request()));
    }

    public function acquire(Request $request, Page $page, PageLockManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $result = $manager->acquire($this->actor($request), $page);

        return $response->success([
            'lock' => PageLockResource::make($result['lock'])->resolve($request),
            'token' => $result['token'],
        ], 'Edit lock acquired.', 201);
    }

    public function heartbeat(PageLockRequest $request, Page $page, PageLockManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $lock = $manager->heartbeat($this->actor($request), $page, (string) $request->validated('token'));

        return $response->success(PageLockResource::make($lock)->resolve($request));
    }

    public function release(PageLockRequest $request, Page $page, PageLockManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $page);
        $manager->release($this->actor($request), $page, (string) $request->validated('token'));

        return $response->success(null, 'Edit lock released.');
    }

    public function forceRelease(Request $request, Page $page, PageLockManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('forceUnlock', $page);
        $manager->forceRelease($this->actor($request), $page);

        return $response->success(null, 'Edit lock force released.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Domain\Media\MediaFolderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Media\SetMediaLockRequest;
use App\Http\Requests\Api\V1\Media\StoreMediaFolderRequest;
use App\Http\Requests\Api\V1\Media\UpdateMediaFolderRequest;
use App\Http\Resources\Api\V1\Media\MediaFolderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class MediaFolderController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);
        $folders = MediaFolder::query()
            ->with('parent:id,public_id')
            ->withCount(['media', 'children'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $response->success(MediaFolderResource::collection($folders)->resolve($request));
    }

    public function store(StoreMediaFolderRequest $request, MediaFolderService $folders, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Media::class);
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);
        $folder = $folders->create($request->validated(), $actor, $request)
            ->load('parent:id,public_id')
            ->loadCount(['media', 'children']);

        return $response->success((new MediaFolderResource($folder))->resolve($request), __('media.folder_created'), 201);
    }

    public function update(UpdateMediaFolderRequest $request, MediaFolder $folder, MediaFolderService $folders, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', new Media);
        $folder = $folders->rename($folder, $request->validated(), $this->actor($request), $request)
            ->load('parent:id,public_id')
            ->loadCount(['media', 'children']);

        return $response->success((new MediaFolderResource($folder))->resolve($request), __('media.folder_updated'));
    }

    public function lock(SetMediaLockRequest $request, MediaFolder $folder, MediaFolderService $folders, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', new Media);
        $folder = $folders->setLock($folder, $request->boolean('locked'), $this->actor($request), $request)
            ->load('parent:id,public_id')
            ->loadCount(['media', 'children']);

        return $response->success((new MediaFolderResource($folder))->resolve($request), __('media.lock_updated'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

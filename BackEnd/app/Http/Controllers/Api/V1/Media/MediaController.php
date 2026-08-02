<?php

namespace App\Http\Controllers\Api\V1\Media;

use App\Domain\Media\MediaLibraryQuery;
use App\Domain\Media\MediaLibraryService;
use App\Domain\Media\MediaUploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Media\IndexMediaRequest;
use App\Http\Requests\Api\V1\Media\MoveMediaRequest;
use App\Http\Requests\Api\V1\Media\SetMediaLockRequest;
use App\Http\Requests\Api\V1\Media\SetMediaVisibilityRequest;
use App\Http\Requests\Api\V1\Media\StoreMediaRequest;
use App\Http\Requests\Api\V1\Media\UpdateMediaRequest;
use App\Http\Resources\Api\V1\Media\MediaResource;
use App\Http\Responses\ApiResponse;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

final class MediaController extends Controller
{
    public function index(IndexMediaRequest $request, MediaLibraryQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Media::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(MediaResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreMediaRequest $request, MediaUploadService $uploads, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Media::class);
        $file = $request->file('file');
        abort_unless($file instanceof UploadedFile, 422);
        $media = $uploads->upload($file, $request->safe()->except('file'), $this->actor($request), $request);

        return $response->success((new MediaResource($media))->resolve($request), __('media.uploaded'), 201);
    }

    public function show(Request $request, Media $media, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $media);

        return $response->success((new MediaResource($media->load(['folder', 'variants', 'tags', 'usages'])->loadCount('usages')))->resolve($request));
    }

    public function update(UpdateMediaRequest $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $media);

        return $response->success((new MediaResource($library->updateMetadata($media, $request->validated(), $this->actor($request), $request)))->resolve($request), __('media.updated'));
    }

    public function move(MoveMediaRequest $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $media);
        $folderId = $request->validated('folder_id');
        $folder = is_string($folderId) ? MediaFolder::query()->where('public_id', $folderId)->firstOrFail() : null;

        return $response->success((new MediaResource($library->move($media, $folder, $this->actor($request), $request)))->resolve($request), __('media.moved'));
    }

    public function trash(Request $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $media);

        return $response->success((new MediaResource($library->trash($media, $this->actor($request), $request)))->resolve($request), __('media.trashed'));
    }

    public function restore(Request $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('restore', $media);

        return $response->success((new MediaResource($library->restore($media, $this->actor($request), $request)))->resolve($request), __('media.restored'));
    }

    public function destroy(Request $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $media);
        $library->deletePermanently($media, $this->actor($request), $request);

        return $response->success(message: __('media.deleted'));
    }

    public function retry(Request $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $media);

        return $response->success((new MediaResource($library->retry($media, $this->actor($request), $request)))->resolve($request), __('media.retry_queued'));
    }

    public function lock(SetMediaLockRequest $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $media);

        return $response->success((new MediaResource($library->setLock($media, $request->boolean('locked'), $this->actor($request), $request)))->resolve($request), __('media.lock_updated'));
    }

    public function visibility(SetMediaVisibilityRequest $request, Media $media, MediaLibraryService $library, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $media);

        return $response->success((new MediaResource($library->setVisibility($media, $request->validated('visibility'), $this->actor($request), $request)))->resolve($request), __('media.visibility_updated'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Showcase;

use App\Domain\Localization\TranslatableModel;
use App\Domain\Showcase\ShowcaseManager;
use App\Domain\Showcase\ShowcaseQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Showcase\IndexShowcaseRequest;
use App\Http\Requests\Api\V1\Showcase\SaveShowcaseRequest;
use App\Http\Resources\Api\V1\Showcase\ShowcaseResource;
use App\Http\Responses\ApiResponse;
use App\Models\Certification;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ShowcaseController extends Controller
{
    public function index(IndexShowcaseRequest $request, string $kind, ShowcaseQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', $this->class($kind));
        $paginator = $query->paginate($kind, $request->validated());

        return $response->paginated(ShowcaseResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(SaveShowcaseRequest $request, string $kind, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', $this->class($kind));
        $model = $manager->save($kind, $this->actor($request), null, $request->validated());

        return $response->success(ShowcaseResource::make($model)->resolve($request), __('showcase.created'), 201);
    }

    public function show(Request $request, string $kind, string $publicId, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('view', $model);

        return $response->success(ShowcaseResource::make($this->load($kind, $model))->resolve($request));
    }

    public function update(SaveShowcaseRequest $request, string $kind, string $publicId, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('update', $model);

        return $response->success(ShowcaseResource::make($manager->save($kind, $this->actor($request), $model, $request->validated()))->resolve($request), __('showcase.updated'));
    }

    public function publish(Request $request, string $kind, string $publicId, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('publish', $model);

        return $response->success(ShowcaseResource::make($manager->publish($kind, $this->actor($request), $model))->resolve($request), __('showcase.published'));
    }

    public function archive(Request $request, string $kind, string $publicId, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('update', $model);

        return $response->success(ShowcaseResource::make($manager->archive($kind, $this->actor($request), $model))->resolve($request), __('showcase.archived'));
    }

    public function destroy(Request $request, string $kind, string $publicId, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('delete', $model);
        $manager->trash($kind, $this->actor($request), $model);

        return $response->success(null, __('showcase.deleted'));
    }

    public function restore(Request $request, string $kind, string $publicId, ShowcaseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId, true);
        Gate::authorize('restore', $model);

        return $response->success(ShowcaseResource::make($manager->restore($kind, $this->actor($request), $model))->resolve($request), __('showcase.restored'));
    }

    /** @return class-string<TranslatableModel> */
    private function class(string $kind): string
    {
        return match ($kind) {
            'galleries' => Gallery::class, 'gallery-items' => GalleryItem::class, 'partners' => Partner::class, 'certifications' => Certification::class, 'projects' => Project::class, default => abort(404)
        };
    }

    private function find(string $kind, string $publicId, bool $withTrashed = false): TranslatableModel
    {
        $query = $this->class($kind)::query();
        if ($withTrashed) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where('public_id', $publicId)->firstOrFail();
    }

    private function load(string $kind, Model $model): Model
    {
        return $model->load(match ($kind) {
            'gallery-items' => ['translations', 'gallery.translations', 'media'], 'partners' => ['translations', 'logo'], 'certifications' => ['translations', 'image', 'document'], 'projects' => ['translations', 'mediaItems.translations', 'mediaItems.media'], default => ['translations', 'items.translations', 'items.media']
        });
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Services;

use App\Domain\Services\ServiceManager;
use App\Domain\Services\ServiceQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Services\IndexServicesRequest;
use App\Http\Requests\Api\V1\Services\SaveServiceRequest;
use App\Http\Resources\Api\V1\Services\ServiceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ServiceController extends Controller
{
    public function index(IndexServicesRequest $request, ServiceQuery $query, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Service::class);
        $paginator = $query->paginate($request->validated());

        return $response->paginated(ServiceResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(SaveServiceRequest $request, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Service::class);
        $service = $manager->createService($this->actor($request), $request->validated());

        return $response->success(ServiceResource::make($service)->resolve($request), __('services.created'), 201);
    }

    public function show(Request $request, Service $service, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $service);
        $service->load(['translations', 'category.translations', 'media']);

        return $response->success(ServiceResource::make($service)->resolve($request));
    }

    public function update(SaveServiceRequest $request, Service $service, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $service);
        $service = $manager->saveService($this->actor($request), $service, $request->validated());

        return $response->success(ServiceResource::make($service)->resolve($request), __('services.updated'));
    }

    public function publish(Request $request, Service $service, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $service);

        return $response->success(ServiceResource::make($manager->publishService($this->actor($request), $service))->resolve($request), __('services.published'));
    }

    public function archive(Request $request, Service $service, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $service);

        return $response->success(ServiceResource::make($manager->archiveService($this->actor($request), $service))->resolve($request), __('services.archived'));
    }

    public function destroy(Request $request, Service $service, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $service);
        $manager->trashService($this->actor($request), $service->load('media'));

        return $response->success(null, __('services.deleted'));
    }

    public function restore(Request $request, string $service, ServiceManager $manager, ApiResponse $response): JsonResponse
    {
        $model = Service::onlyTrashed()->where('public_id', $service)->with('media')->firstOrFail();
        Gate::authorize('restore', $model);

        return $response->success(ServiceResource::make($manager->restoreService($this->actor($request), $model))->resolve($request), __('services.restored'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

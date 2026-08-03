<?php

namespace App\Http\Controllers\Api\V1\Warehouses;

use App\Domain\Warehouses\WarehouseManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Warehouses\SaveWarehouseFacilityRequest;
use App\Http\Requests\Api\V1\Warehouses\SaveWarehouseRequest;
use App\Http\Requests\Api\V1\Warehouses\SaveWarehouseServiceRequest;
use App\Http\Resources\Api\V1\Warehouses\WarehouseResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseFacility;
use App\Models\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class WarehouseController extends Controller
{
    public function index(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Warehouse::class);
        $items = Warehouse::query()->with(['translations', 'media', 'facilities.translations', 'services.translations'])->orderBy('sort_order')->get();

        return $response->success(WarehouseResource::collection($items)->resolve($request));
    }

    public function facilities(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', WarehouseFacility::class);

        return $response->success(WarehouseResource::collection(WarehouseFacility::query()->with('translations')->withCount('warehouses')->orderBy('sort_order')->get())->resolve($request));
    }

    public function services(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', WarehouseService::class);

        return $response->success(WarehouseResource::collection(WarehouseService::query()->with('translations')->withCount('warehouses')->orderBy('sort_order')->get())->resolve($request));
    }

    public function saveWarehouse(SaveWarehouseRequest $request, WarehouseManager $manager, ApiResponse $response, ?Warehouse $warehouse = null): JsonResponse
    {
        Gate::authorize($warehouse ? 'update' : 'create', $warehouse ?? Warehouse::class);

        return $response->success(WarehouseResource::make($manager->saveWarehouse($this->actor($request), $warehouse, $request->validated()))->resolve($request), __('warehouses.saved'), $warehouse ? 200 : 201);
    }

    public function saveFacility(SaveWarehouseFacilityRequest $request, WarehouseManager $manager, ApiResponse $response, ?WarehouseFacility $facility = null): JsonResponse
    {
        Gate::authorize($facility ? 'update' : 'create', $facility ?? WarehouseFacility::class);

        return $response->success(WarehouseResource::make($manager->saveFacility($this->actor($request), $facility, $request->validated()))->resolve($request), __('warehouses.saved'), $facility ? 200 : 201);
    }

    public function saveService(SaveWarehouseServiceRequest $request, WarehouseManager $manager, ApiResponse $response, ?WarehouseService $service = null): JsonResponse
    {
        Gate::authorize($service ? 'update' : 'create', $service ?? WarehouseService::class);

        return $response->success(WarehouseResource::make($manager->saveService($this->actor($request), $service, $request->validated()))->resolve($request), __('warehouses.saved'), $service ? 200 : 201);
    }

    public function publish(Request $request, Warehouse $warehouse, WarehouseManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('publish', $warehouse);

        return $response->success(WarehouseResource::make($manager->publish($this->actor($request), $warehouse))->resolve($request), __('warehouses.published'));
    }

    public function destroy(Request $request, string $kind, string $publicId, WarehouseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('delete', $model);
        if ($model instanceof Warehouse) {
            $manager->deleteWarehouse($this->actor($request), $model);
        } else {
            $manager->deleteReference($this->actor($request), $model);
        }

        return $response->success(null, __('warehouses.deleted'));
    }

    private function find(string $kind, string $publicId): Warehouse|WarehouseFacility|WarehouseService
    {
        $class = match ($kind) {
            'warehouses' => Warehouse::class, 'facilities' => WarehouseFacility::class, 'services' => WarehouseService::class, default => abort(404)
        };

        return $class::query()->where('public_id', $publicId)->firstOrFail();
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

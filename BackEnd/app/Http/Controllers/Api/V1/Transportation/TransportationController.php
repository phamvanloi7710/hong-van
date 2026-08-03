<?php

namespace App\Http\Controllers\Api\V1\Transportation;

use App\Domain\Transportation\TransportationManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transportation\SaveTransportAreaRequest;
use App\Http\Requests\Api\V1\Transportation\SaveTransportRouteRequest;
use App\Http\Requests\Api\V1\Transportation\SaveVehicleRequest;
use App\Http\Requests\Api\V1\Transportation\SaveVehicleTypeRequest;
use App\Http\Resources\Api\V1\Transportation\TransportationResource;
use App\Http\Responses\ApiResponse;
use App\Models\TransportRoute;
use App\Models\TransportServiceArea;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class TransportationController extends Controller
{
    public function types(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', VehicleType::class);
        $items = VehicleType::query()->with('translations')->withCount('vehicles')->orderBy('sort_order')->get();

        return $response->success(TransportationResource::collection($items)->resolve($request));
    }

    public function vehicles(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Vehicle::class);
        $items = Vehicle::query()->with(['translations', 'type.translations', 'media'])->orderBy('sort_order')->get();

        return $response->success(TransportationResource::collection($items)->resolve($request));
    }

    public function routes(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', TransportRoute::class);

        return $response->success(TransportationResource::collection(TransportRoute::query()->with('translations')->orderBy('sort_order')->get())->resolve($request));
    }

    public function areas(Request $request, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', TransportServiceArea::class);

        return $response->success(TransportationResource::collection(TransportServiceArea::query()->with('translations')->orderBy('sort_order')->get())->resolve($request));
    }

    public function saveType(SaveVehicleTypeRequest $request, TransportationManager $manager, ApiResponse $response, ?VehicleType $type = null): JsonResponse
    {
        Gate::authorize($type ? 'update' : 'create', $type ?? VehicleType::class);

        return $response->success(TransportationResource::make($manager->saveVehicleType($this->actor($request), $type, $request->validated()))->resolve($request), __('transportation.saved'), $type ? 200 : 201);
    }

    public function saveVehicle(SaveVehicleRequest $request, TransportationManager $manager, ApiResponse $response, ?Vehicle $vehicle = null): JsonResponse
    {
        Gate::authorize($vehicle ? 'update' : 'create', $vehicle ?? Vehicle::class);

        return $response->success(TransportationResource::make($manager->saveVehicle($this->actor($request), $vehicle, $request->validated()))->resolve($request), __('transportation.saved'), $vehicle ? 200 : 201);
    }

    public function saveRoute(SaveTransportRouteRequest $request, TransportationManager $manager, ApiResponse $response, ?TransportRoute $route = null): JsonResponse
    {
        Gate::authorize($route ? 'update' : 'create', $route ?? TransportRoute::class);

        return $response->success(TransportationResource::make($manager->saveRoute($this->actor($request), $route, $request->validated()))->resolve($request), __('transportation.saved'), $route ? 200 : 201);
    }

    public function saveArea(SaveTransportAreaRequest $request, TransportationManager $manager, ApiResponse $response, ?TransportServiceArea $area = null): JsonResponse
    {
        Gate::authorize($area ? 'update' : 'create', $area ?? TransportServiceArea::class);

        return $response->success(TransportationResource::make($manager->saveArea($this->actor($request), $area, $request->validated()))->resolve($request), __('transportation.saved'), $area ? 200 : 201);
    }

    public function publish(Request $request, string $kind, string $publicId, TransportationManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('publish', $model);

        return $response->success(TransportationResource::make($manager->publish($this->actor($request), $model))->resolve($request), __('transportation.published'));
    }

    public function destroy(Request $request, string $kind, string $publicId, TransportationManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $this->find($kind, $publicId);
        Gate::authorize('delete', $model);
        if ($model instanceof VehicleType) {
            $manager->deleteVehicleType($this->actor($request), $model);
        } else {
            $manager->delete($this->actor($request), $model);
        }

        return $response->success(null, __('transportation.deleted'));
    }

    private function find(string $kind, string $publicId): Model
    {
        $class = match ($kind) {
            'vehicles' => Vehicle::class,'routes' => TransportRoute::class,'areas' => TransportServiceArea::class,'types' => VehicleType::class,default => abort(404)
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

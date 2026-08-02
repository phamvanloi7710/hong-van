<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\IdentityQueryService;
use App\Domain\Identity\PermissionManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\IndexIdentityRequest;
use App\Http\Requests\Api\V1\Identity\StorePermissionRequest;
use App\Http\Requests\Api\V1\Identity\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\Identity\PermissionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PermissionController extends Controller
{
    public function index(IndexIdentityRequest $request, IdentityQueryService $queries, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Permission::class);
        $paginator = $queries->permissions($request->validated());

        return $response->paginated(PermissionResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StorePermissionRequest $request, PermissionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Permission::class);
        $permission = $manager->create($this->actor($request), $request->validated());

        return $response->success(PermissionResource::make($permission)->resolve($request), status: 201);
    }

    public function show(Request $request, Permission $permission, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $permission);
        $permission->loadCount('roles');

        return $response->success(PermissionResource::make($permission)->resolve($request));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission, PermissionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $permission);
        $permission = $manager->update($this->actor($request), $permission, $request->validated());

        return $response->success(PermissionResource::make($permission)->resolve($request));
    }

    public function destroy(Request $request, Permission $permission, PermissionManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $permission);
        $manager->delete($this->actor($request), $permission);

        return $response->success(message: 'Đã xóa quyền.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\IdentityQueryService;
use App\Domain\Identity\RoleManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\IndexIdentityRequest;
use App\Http\Requests\Api\V1\Identity\StoreRoleRequest;
use App\Http\Requests\Api\V1\Identity\UpdateRoleRequest;
use App\Http\Resources\Api\V1\Identity\RoleResource;
use App\Http\Responses\ApiResponse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class RoleController extends Controller
{
    public function index(IndexIdentityRequest $request, IdentityQueryService $queries, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);
        $paginator = $queries->roles($request->validated());

        return $response->paginated(RoleResource::collection($paginator->items())->resolve($request), $paginator);
    }

    public function store(StoreRoleRequest $request, RoleManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', Role::class);
        $role = $manager->create($this->actor($request), $request->validated());

        return $response->success(RoleResource::make($role)->resolve($request), status: 201);
    }

    public function show(Request $request, Role $role, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $role);
        $role->load('permissions')->loadCount('users');

        return $response->success(RoleResource::make($role)->resolve($request));
    }

    public function update(UpdateRoleRequest $request, Role $role, RoleManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $role);
        $role = $manager->update($this->actor($request), $role, $request->validated());

        return $response->success(RoleResource::make($role)->resolve($request));
    }

    public function destroy(Request $request, Role $role, RoleManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $role);
        $manager->delete($this->actor($request), $role);

        return $response->success(message: __('api.identity_role_deleted'));
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

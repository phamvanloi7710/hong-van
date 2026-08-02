<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\IdentityQueryService;
use App\Domain\Identity\UserManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\IndexIdentityRequest;
use App\Http\Requests\Api\V1\Identity\StoreUserRequest;
use App\Http\Requests\Api\V1\Identity\UpdateUserRequest;
use App\Http\Resources\Api\V1\Identity\IdentityUserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class UserController extends Controller
{
    public function index(IndexIdentityRequest $request, IdentityQueryService $queries, ApiResponse $response): JsonResponse
    {
        Gate::authorize('viewAny', User::class);
        $paginator = $queries->users($request->validated());

        return $response->paginated(
            IdentityUserResource::collection($paginator->items())->resolve($request),
            $paginator,
        );
    }

    public function store(StoreUserRequest $request, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('create', User::class);
        if ($request->has('role_ids')) {
            Gate::authorize('manageAssignments', Role::class);
        }
        if ($request->has('permission_overrides')) {
            Gate::authorize('manageOverrides', Permission::class);
        }
        $user = $manager->create($this->actor($request), $request->validated());

        return $response->success(IdentityUserResource::make($user)->resolve($request), status: 201);
    }

    public function show(Request $request, User $user, ApiResponse $response): JsonResponse
    {
        Gate::authorize('view', $user);
        $user->load(['roles.permissions', 'permissionOverrides']);

        return $response->success(IdentityUserResource::make($user)->resolve($request));
    }

    public function update(UpdateUserRequest $request, User $user, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $user);
        if ($request->has('role_ids')) {
            Gate::authorize('manageAssignments', Role::class);
        }
        if ($request->has('permission_overrides')) {
            Gate::authorize('manageOverrides', Permission::class);
        }
        $user = $manager->update($this->actor($request), $user, $request->validated());

        return $response->success(IdentityUserResource::make($user)->resolve($request));
    }

    public function destroy(Request $request, User $user, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('delete', $user);
        $manager->delete($this->actor($request), $user);

        return $response->success(message: 'Đã xóa người dùng.');
    }

    public function activate(Request $request, User $user, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('update', $user);
        $user = $manager->activate($this->actor($request), $user);

        return $response->success(IdentityUserResource::make($user)->resolve($request));
    }

    public function lock(Request $request, User $user, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('manageSessions', $user);
        $user = $manager->lock($this->actor($request), $user);

        return $response->success(IdentityUserResource::make($user)->resolve($request));
    }

    public function resetSessions(Request $request, User $user, UserManager $manager, ApiResponse $response): JsonResponse
    {
        Gate::authorize('manageSessions', $user);
        $manager->resetSessions($this->actor($request), $user);

        return $response->success(message: 'Đã thu hồi toàn bộ phiên đăng nhập.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

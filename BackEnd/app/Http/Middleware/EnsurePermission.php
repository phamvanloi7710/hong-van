<?php

namespace App\Http\Middleware;

use App\Domain\Identity\PermissionService;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsurePermission
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User && $this->permissions->allows($user, $permission, $request), 403);

        return $next($request);
    }
}

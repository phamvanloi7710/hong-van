<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Identity\AdminAuthenticator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\AdminUserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthSessionController extends Controller
{
    public function store(
        LoginRequest $request,
        AdminAuthenticator $authenticator,
        ApiResponse $response,
    ): JsonResponse {
        $user = $authenticator->login(
            $request,
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->boolean('remember'),
        );

        return $response->success(
            AdminUserResource::make($user)->resolve($request),
            __('auth.login_successful'),
        );
    }

    public function show(Request $request, ApiResponse $response): JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return $response->success(AdminUserResource::make($user)->resolve($request));
    }

    public function destroy(
        Request $request,
        AdminAuthenticator $authenticator,
        ApiResponse $response,
    ): JsonResponse {
        $authenticator->logout($request);

        return $response->success(message: __('auth.logout_successful'));
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\UserPreferenceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\UpdateUserPreferencesRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPreferenceController extends Controller
{
    public function show(Request $request, UserPreferenceService $preferences, ApiResponse $response): JsonResponse
    {
        return $response->success($preferences->get($this->actor($request)));
    }

    public function update(UpdateUserPreferencesRequest $request, UserPreferenceService $preferences, ApiResponse $response): JsonResponse
    {
        return $response->success($preferences->update($this->actor($request), $request->validated()));
    }

    public function destroy(Request $request, UserPreferenceService $preferences, ApiResponse $response): JsonResponse
    {
        return $response->success($preferences->reset($this->actor($request)));
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}

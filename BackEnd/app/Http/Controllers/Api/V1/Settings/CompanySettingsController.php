<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Domain\Settings\CompanySettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\UpdateSettingGroupRequest;
use App\Http\Responses\ApiResponse;
use App\Models\SettingGroup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanySettingsController extends Controller
{
    public function index(CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        return $response->success($settings->adminPayload());
    }

    public function update(UpdateSettingGroupRequest $request, SettingGroup $settingGroup, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        return $response->success($settings->updateGroup($settingGroup, $request->validated('values'), $this->actor($request)));
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}

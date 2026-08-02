<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Domain\Settings\CompanyDirectoryService;
use App\Domain\Settings\CompanySettingsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\ReplaceBusinessHoursRequest;
use App\Http\Requests\Api\V1\Settings\SaveBranchRequest;
use App\Http\Requests\Api\V1\Settings\SaveContactChannelRequest;
use App\Http\Requests\Api\V1\Settings\SaveSocialLinkRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Branch;
use App\Models\ContactChannel;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanyDirectoryController extends Controller
{
    public function storeBranch(SaveBranchRequest $request, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveBranch(null, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload(), status: 201);
    }

    public function updateBranch(SaveBranchRequest $request, Branch $branch, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveBranch($branch, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function deleteBranch(Request $request, Branch $branch, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->delete($branch, $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function storeSocialLink(SaveSocialLinkRequest $request, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveSocialLink(null, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload(), status: 201);
    }

    public function updateSocialLink(SaveSocialLinkRequest $request, SocialLink $socialLink, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveSocialLink($socialLink, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function deleteSocialLink(Request $request, SocialLink $socialLink, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->delete($socialLink, $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function storeContactChannel(SaveContactChannelRequest $request, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveContactChannel(null, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload(), status: 201);
    }

    public function updateContactChannel(SaveContactChannelRequest $request, ContactChannel $contactChannel, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->saveContactChannel($contactChannel, $request->validated(), $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function deleteContactChannel(Request $request, ContactChannel $contactChannel, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->delete($contactChannel, $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function replaceGlobalBusinessHours(ReplaceBusinessHoursRequest $request, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->replaceBusinessHours(null, $request->validated('hours'), $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    public function replaceBranchBusinessHours(ReplaceBusinessHoursRequest $request, Branch $branch, CompanyDirectoryService $directory, CompanySettingsService $settings, ApiResponse $response): JsonResponse
    {
        $directory->replaceBusinessHours($branch, $request->validated('hours'), $this->actor($request));

        return $response->success($settings->adminPayload());
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}

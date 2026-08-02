<?php

namespace App\Http\Controllers\Api\V1\Localization;

use App\Domain\Localization\LocalizationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Localization\UpdateLanguageRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

final class LocalizationController extends Controller
{
    public function index(LocalizationService $localization, ApiResponse $response): JsonResponse
    {
        return $response->success($localization->adminPayload());
    }

    public function update(UpdateLanguageRequest $request, Language $language, LocalizationService $localization, ApiResponse $response): JsonResponse
    {
        return $response->success(
            $localization->updateLanguage($language, $request->validated()),
            __('api.localization_language_updated'),
        );
    }
}

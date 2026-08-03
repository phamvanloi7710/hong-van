<?php

namespace App\Http\Controllers\Api\V1\Analytics;

use App\Domain\Analytics\ConsentManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Analytics\UpdateConsentRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PublicConsentController extends Controller
{
    public function show(Request $request, ConsentManager $consent, ApiResponse $response): JsonResponse
    {
        return $response->success($consent->payload($request));
    }

    public function update(UpdateConsentRequest $request, ConsentManager $consent, ApiResponse $response): JsonResponse
    {
        $cookie = $consent->grantCookie($request, $request->validated());
        $request->cookies->set($cookie->getName(), $cookie->getValue());

        return $response
            ->success($consent->payload($request), __('consent.messages.saved'))
            ->withCookie($cookie);
    }

    public function destroy(Request $request, ConsentManager $consent, ApiResponse $response): JsonResponse
    {
        $request->cookies->remove((string) config('analytics.cookie.name'));

        return $response
            ->success($consent->payload($request), __('consent.messages.revoked'))
            ->withCookie($consent->revokeCookie());
    }
}

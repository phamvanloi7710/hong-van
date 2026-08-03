<?php

namespace App\Http\Controllers\Api\V1\Transportation;

use App\Domain\Transportation\TransportationManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transportation\StoreTransportRequest;
use App\Http\Resources\Api\V1\Transportation\TransportRequestResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PublicTransportRequestController extends Controller
{
    public function __invoke(StoreTransportRequest $request, TransportationManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $manager->createRequest($request->validated(), $request->ip(), $request->userAgent());

        return $response->success(TransportRequestResource::make($model)->resolve($request), __('transportation.request_received'), 201);
    }
}

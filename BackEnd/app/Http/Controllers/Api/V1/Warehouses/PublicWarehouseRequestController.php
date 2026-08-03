<?php

namespace App\Http\Controllers\Api\V1\Warehouses;

use App\Domain\Warehouses\WarehouseManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Warehouses\StoreWarehouseRequest;
use App\Http\Resources\Api\V1\Warehouses\WarehouseRequestResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PublicWarehouseRequestController extends Controller
{
    public function __invoke(StoreWarehouseRequest $request, WarehouseManager $manager, ApiResponse $response): JsonResponse
    {
        $model = $manager->createRequest($request->validated(), $request->ip(), $request->userAgent());

        return $response->success(WarehouseRequestResource::make($model)->resolve($request), __('warehouses.request_received'), 201);
    }
}

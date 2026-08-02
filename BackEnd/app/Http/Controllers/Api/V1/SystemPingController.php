<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class SystemPingController extends Controller
{
    public function __invoke(ApiResponse $response): JsonResponse
    {
        return $response->success(['status' => 'up']);
    }
}

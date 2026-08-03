<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Domain\Dashboard\DashboardAggregateService;
use App\Domain\Dashboard\DashboardRange;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dashboard\DashboardRangeRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __invoke(
        DashboardRangeRequest $request,
        DashboardAggregateService $dashboard,
        ApiResponse $response,
    ): JsonResponse {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $response->success($dashboard->get($actor, DashboardRange::fromValidated($request->validated())));
    }
}

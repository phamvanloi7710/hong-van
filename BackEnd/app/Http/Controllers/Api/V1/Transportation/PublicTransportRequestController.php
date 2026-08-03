<?php

namespace App\Http\Controllers\Api\V1\Transportation;

use App\Domain\Leads\LeadIntakeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transportation\StoreTransportRequest;
use App\Http\Resources\Api\V1\Leads\PublicLeadResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PublicTransportRequestController extends Controller
{
    public function __invoke(StoreTransportRequest $request, LeadIntakeService $intake, ApiResponse $response): JsonResponse
    {
        $submission = $intake->transport($request->validated(), $request);

        return $response->success(PublicLeadResource::make($submission)->resolve($request), __('leads.received'), $submission->created ? 201 : 200);
    }
}

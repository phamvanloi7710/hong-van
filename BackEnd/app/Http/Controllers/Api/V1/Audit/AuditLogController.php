<?php

namespace App\Http\Controllers\Api\V1\Audit;

use App\Domain\Audit\AuditLogQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Audit\IndexAuditLogsRequest;
use App\Http\Resources\Api\V1\Audit\AuditLogResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AuditLogController extends Controller
{
    public function index(IndexAuditLogsRequest $request, AuditLogQuery $query, ApiResponse $response): JsonResponse
    {
        $paginator = $query->paginate($request->validated());

        return $response->paginated(
            AuditLogResource::collection($paginator->items())->resolve($request),
            $paginator,
        );
    }
}

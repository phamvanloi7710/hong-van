<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Domain\Dashboard\NotificationDeepLink;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\DatabaseNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function index(Request $request, NotificationDeepLink $deepLinks, ApiResponse $response): JsonResponse
    {
        $filters = $request->validate([
            'state' => ['nullable', 'in:all,unread,read'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
        ]);
        $actor = $this->actor($request);
        $query = $actor->notifications();
        $query->when(($filters['state'] ?? 'all') === 'unread', fn ($builder) => $builder->whereNull('read_at'));
        $query->when(($filters['state'] ?? 'all') === 'read', fn ($builder) => $builder->whereNotNull('read_at'));
        $items = $query->paginate((int) ($filters['per_page'] ?? 15));

        return $response->success([
            'items' => $items->getCollection()->map(fn (DatabaseNotification $notification): array => $this->serialize($notification, $deepLinks))->all(),
            'unread_count' => $actor->unreadNotifications()->count(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function read(Request $request, string $notification, NotificationDeepLink $deepLinks, ApiResponse $response): JsonResponse
    {
        $item = $this->actor($request)->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return $response->success($this->serialize($item->fresh() ?? $item, $deepLinks));
    }

    public function readAll(Request $request, ApiResponse $response): JsonResponse
    {
        $actor = $this->actor($request);
        $actor->unreadNotifications()->update(['read_at' => now('UTC')]);

        return $response->success(['unread_count' => 0]);
    }

    /** @return array<string, mixed> */
    private function serialize(DatabaseNotification $notification, NotificationDeepLink $deepLinks): array
    {
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'kind' => class_basename($notification->type),
            'data' => [
                'lead_public_id' => $data['lead_public_id'] ?? null,
                'type' => $data['type'] ?? null,
                'status' => $data['status'] ?? null,
            ],
            'deep_link' => $deepLinks->sanitize($data['url'] ?? null),
            'read_at' => $notification->read_at?->utc()->toISOString(),
            'created_at' => $notification->created_at?->utc()->toISOString(),
        ];
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();
        abort_unless($actor instanceof User, 401);

        return $actor;
    }
}

<?php

namespace App\Domain\Media;

use App\Models\Media;
use Illuminate\Http\Request;

final readonly class MediaMaintenanceService
{
    public function __construct(private MediaLibraryService $library) {}

    public function cleanup(): int
    {
        $count = 0;
        $before = now('UTC')->subDays(max(1, (int) config('media.trash_retention_days', 30)));
        $request = Request::create('/internal/media/cleanup', 'DELETE');

        Media::onlyTrashed()->where('deleted_at', '<=', $before)->whereDoesntHave('usages')->eachById(function (Media $media) use (&$count, $request): void {
            $this->library->deletePermanently($media, null, $request);
            $count++;
        });

        return $count;
    }

    public function retryFailed(): int
    {
        $count = 0;
        $request = Request::create('/internal/media/retry', 'POST');

        Media::query()->where('status', 'failed')->eachById(function (Media $media) use (&$count, $request): void {
            $this->library->retry($media, null, $request);
            $count++;
        });

        return $count;
    }
}

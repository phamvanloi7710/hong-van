<?php

namespace App\Jobs\Media;

use App\Domain\Audit\AuditTrail;
use App\Domain\Media\ImageVariantGenerator;
use App\Models\Media;
use App\Models\MediaOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GenerateMediaVariants implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $mediaId, public int $operationId) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(ImageVariantGenerator $generator, AuditTrail $auditTrail): void
    {
        $media = Media::withTrashed()->findOrFail($this->mediaId);
        $operation = MediaOperation::query()->findOrFail($this->operationId);
        if ($media->trashed()) {
            $operation->forceFill([
                'status' => 'completed',
                'attempts' => $operation->attempts + 1,
                'metadata' => ['skipped' => 'media_trashed'],
                'started_at' => now('UTC'),
                'finished_at' => now('UTC'),
            ])->save();

            return;
        }

        $operation->forceFill([
            'status' => 'processing',
            'attempts' => $operation->attempts + 1,
            'started_at' => now('UTC'),
            'finished_at' => null,
            'error_message' => null,
        ])->save();

        try {
            $count = $generator->generate($media);
            $media->forceFill(['status' => 'ready'])->save();
            $operation->forceFill([
                'status' => 'completed',
                'metadata' => ['variant_count' => $count],
                'finished_at' => now('UTC'),
            ])->save();

            $auditTrail->record(
                'media.variants.generated',
                subjectType: 'media',
                subjectPublicId: $media->public_id,
                after: ['variant_count' => $count, 'status' => 'ready'],
                request: Request::create('/internal/media/variants', 'POST'),
            );
        } catch (Throwable $exception) {
            $this->markFailed($media, $operation, $exception, $auditTrail);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        $media = Media::withTrashed()->find($this->mediaId);
        $operation = MediaOperation::query()->find($this->operationId);
        if ($media !== null && $operation !== null && $operation->status !== 'failed') {
            $this->markFailed($media, $operation, $exception, app(AuditTrail::class));
        }
    }

    private function markFailed(Media $media, MediaOperation $operation, Throwable $exception, AuditTrail $auditTrail): void
    {
        $message = sprintf('Variant generation failed (%s).', class_basename($exception));
        $media->forceFill(['status' => 'failed'])->save();
        $operation->forceFill([
            'status' => 'failed',
            'error_message' => $message,
            'finished_at' => now('UTC'),
        ])->save();
        $auditTrail->record(
            'media.variants.failed',
            subjectType: 'media',
            subjectPublicId: $media->public_id,
            after: ['status' => 'failed', 'operation_public_id' => $operation->public_id],
            request: Request::create('/internal/media/variants', 'POST'),
        );
    }
}

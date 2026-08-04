<?php

namespace App\Http\Resources\Api\V1\PageBuilder;

use App\Models\PageVersion;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PageResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'code' => $this->resource->code,
            'type' => $this->resource->type,
            'status' => $this->resource->status,
            'is_home' => (bool) $this->resource->is_home,
            'translations' => $this->whenLoaded('translations', fn (): array => $this->resource->translations->map(static fn ($translation): array => [
                'locale' => $translation->locale,
                'title' => $translation->title,
                'navigation_label' => $translation->navigation_label,
                'slug' => $translation->slug,
            ])->values()->all()),
            'draft' => $this->whenLoaded('draftVersion', fn (): ?array => $this->version($this->resource->draftVersion, true)),
            'published' => $this->whenLoaded('publishedVersion', fn (): ?array => $this->version($this->resource->publishedVersion, false)),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function version(?PageVersion $version, bool $withDocument): ?array
    {
        if (! $version instanceof PageVersion) {
            return null;
        }
        $payload = [
            'public_id' => $version->public_id,
            'version_number' => $version->version_number,
            'status' => $version->status,
            'schema_version' => $version->schema_version,
            'checksum' => $version->checksum,
            'note' => $version->note,
            'published_at' => $this->isoDate($version->getAttribute('published_at')),
            'updated_at' => $version->updated_at?->utc()->toISOString(),
        ];
        if ($withDocument) {
            $payload['document'] = $version->document_json;
        }

        return $payload;
    }

    private function isoDate(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DATE_ATOM) : null;
    }
}

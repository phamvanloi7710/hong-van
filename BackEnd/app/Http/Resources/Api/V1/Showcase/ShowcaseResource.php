<?php

namespace App\Http\Resources\Api\V1\Showcase;

use App\Models\Certification;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Project;
use App\Models\ProjectMedia;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShowcaseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $base = [
            'public_id' => $model->public_id,
            'code' => $model->getAttribute('code'),
            'status' => $model->getAttribute('status'),
            'is_featured' => (bool) $model->getAttribute('is_featured'),
            'sort_order' => (int) $model->getAttribute('sort_order'),
            'translations' => $this->translations($model->translations, $this->translationFields($model)),
            'published_at' => $model->getAttribute('published_at')?->utc()->toISOString(),
            'deleted_at' => $model->getAttribute('deleted_at')?->utc()->toISOString(),
            'updated_at' => $model->getAttribute('updated_at')?->utc()->toISOString(),
        ];

        return match (true) {
            $model instanceof Gallery => [...$base, 'items_count' => $model->relationLoaded('items') ? $model->items->count() : 0],
            $model instanceof GalleryItem => [...$base, 'gallery_id' => $model->gallery?->public_id, 'media' => $this->media($model->media)],
            $model instanceof Partner => [...$base, 'website_url' => $model->website_url, 'logo_media' => $this->media($model->logo)],
            $model instanceof Certification => [...$base, 'issued_on' => $this->date($model, 'issued_on'), 'expires_on' => $this->date($model, 'expires_on'), 'document_visibility' => $model->document_visibility, 'image_media' => $this->media($model->image), 'document_media' => $this->media($model->document)],
            $model instanceof Project => [...$base, 'started_on' => $this->date($model, 'started_on'), 'completed_on' => $this->date($model, 'completed_on'), 'media_items' => $model->mediaItems->map(fn (ProjectMedia $item): array => ['public_id' => $item->public_id, 'role' => $item->role, 'sort_order' => $item->sort_order, 'media' => $this->media($item->media), 'translations' => $this->translations($item->translations, ['alt_text', 'caption'])])->values()->all()],
            default => $base,
        };
    }

    /** @return list<string> */
    private function translationFields(Model $model): array
    {
        return match (true) {
            $model instanceof Gallery => ['name', 'slug', 'description', 'meta_title', 'meta_description'],
            $model instanceof GalleryItem => ['title', 'caption', 'alt_text'],
            $model instanceof Partner => ['name', 'description', 'logo_alt'],
            $model instanceof Certification => ['name', 'slug', 'issuer', 'description', 'image_alt', 'document_label'],
            default => ['title', 'slug', 'summary', 'content', 'location', 'meta_title', 'meta_description'],
        };
    }

    /**
     * @param  iterable<Model>  $translations
     * @param  list<string>  $fields
     * @return list<array<string, mixed>>
     */
    private function translations(iterable $translations, array $fields): array
    {
        $result = [];
        foreach ($translations as $translation) {
            $item = ['locale' => $translation->getAttribute('locale')];
            foreach ($fields as $field) {
                $item[$field] = $translation->getAttribute($field);
            } $result[] = $item;
        }

        return $result;
    }

    /** @return array<string, mixed>|null */
    private function media(?Media $media): ?array
    {
        return $media === null ? null : ['public_id' => $media->public_id, 'file_name' => $media->original_filename, 'mime_type' => $media->mime_type];
    }

    private function date(Model $model, string $field): ?string
    {
        $value = $model->getAttribute($field);

        return $value === null ? null : CarbonImmutable::parse((string) $value)->toDateString();
    }
}

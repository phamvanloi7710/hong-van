<?php

namespace App\Http\Resources\Api\V1\Posts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->resource->public_id,
            'code' => $this->resource->code,
            'status' => $this->resource->status,
            'is_featured' => (bool) $this->resource->is_featured,
            'category' => $this->whenLoaded('category', fn (): ?array => $this->resource->category === null ? null : [
                'public_id' => $this->resource->category->public_id,
                'translations' => $this->translations($this->resource->category->translations, ['name', 'slug']),
            ]),
            'author' => $this->whenLoaded('author', fn (): ?array => $this->resource->author === null ? null : [
                'public_id' => $this->resource->author->public_id,
                'name' => $this->resource->author->name,
                'email' => $this->resource->author->email,
            ]),
            'featured_media' => $this->whenLoaded('featuredMedia', fn (): ?array => $this->resource->featuredMedia === null ? null : [
                'public_id' => $this->resource->featuredMedia->public_id,
                'file_name' => $this->resource->featuredMedia->original_filename,
                'mime_type' => $this->resource->featuredMedia->mime_type,
            ]),
            'tags' => $this->whenLoaded('tags', fn (): array => $this->resource->tags->map(fn ($tag): array => [
                'public_id' => $tag->public_id,
                'translations' => $this->translations($tag->translations, ['name', 'slug']),
            ])->values()->all()),
            'translations' => $this->whenLoaded('translations', fn (): array => $this->translations($this->resource->translations, ['title', 'slug', 'excerpt', 'content_html', 'meta_title', 'meta_description'])),
            'scheduled_for' => $this->resource->scheduled_for?->utc()->toISOString(),
            'published_at' => $this->resource->published_at?->utc()->toISOString(),
            'unpublished_at' => $this->resource->unpublished_at?->utc()->toISOString(),
            'deleted_at' => $this->resource->deleted_at?->utc()->toISOString(),
            'created_at' => $this->resource->created_at?->utc()->toISOString(),
            'updated_at' => $this->resource->updated_at?->utc()->toISOString(),
        ];
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
            }
            $result[] = $item;
        }

        return $result;
    }
}

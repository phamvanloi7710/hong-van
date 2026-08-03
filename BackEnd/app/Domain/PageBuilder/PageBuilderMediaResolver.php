<?php

namespace App\Domain\PageBuilder;

use App\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class PageBuilderMediaResolver
{
    /**
     * @param  array<string, mixed>  $document
     * @return array<string, array<string, mixed>>
     */
    public function resolve(array $document): array
    {
        $references = $this->references($document);
        if ($references === []) {
            return [];
        }

        $publicIds = array_values(array_unique(array_column($references, 'publicId')));
        $media = Media::query()
            ->with('variants')
            ->whereIn('public_id', $publicIds)
            ->where('status', 'ready')
            ->where('visibility', 'public')
            ->where('mime_type', 'like', 'image/%')
            ->get()
            ->keyBy('public_id');

        $errors = [];
        foreach ($references as $reference) {
            if (! $media->has($reference['publicId'])) {
                $errors[$reference['path']][] = $this->message('media_unavailable');
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $media->mapWithKeys(fn (Media $item): array => [$item->public_id => $this->payload($item)])->all();
    }

    /**
     * @param  array<string, mixed>  $document
     * @return list<array{publicId: string, field: string, path: string}>
     */
    public function references(array $document): array
    {
        $blocks = is_array($document['blocks'] ?? null) ? $document['blocks'] : [];
        $references = [];
        $this->collect($blocks, 'document.blocks', $references);

        return $references;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @param  list<array{publicId: string, field: string, path: string}>  $references
     */
    private function collect(array $blocks, string $path, array &$references): void
    {
        foreach ($blocks as $index => $block) {
            $blockPath = "{$path}.{$index}";
            $type = (string) ($block['type'] ?? '');
            $id = (string) ($block['id'] ?? 'block');
            $props = is_array($block['props'] ?? null) ? $block['props'] : [];

            if ($type === 'layout.section' && ($props['background'] ?? null) === 'media') {
                $this->add($references, $props['backgroundMediaId'] ?? null, "block.{$id}.background", "{$blockPath}.props.backgroundMediaId");
            }
            if (in_array($type, ['media.image', 'media.image-text'], true)) {
                $this->add($references, $props['mediaId'] ?? null, "block.{$id}.media", "{$blockPath}.props.mediaId");
            }
            if (in_array($type, ['media.gallery', 'media.logo-cloud'], true)) {
                foreach ((array) ($props['items'] ?? []) as $itemIndex => $item) {
                    if (is_array($item)) {
                        $this->add($references, $item['mediaId'] ?? null, "block.{$id}.item.{$itemIndex}", "{$blockPath}.props.items.{$itemIndex}.mediaId");
                    }
                }
            }

            $children = is_array($block['children'] ?? null) ? $block['children'] : [];
            $this->collect($children, "{$blockPath}.children", $references);
        }
    }

    /** @param list<array{publicId: string, field: string, path: string}> $references */
    private function add(array &$references, mixed $publicId, string $field, string $path): void
    {
        if (is_string($publicId) && preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $publicId) === 1) {
            $references[] = ['publicId' => $publicId, 'field' => $field, 'path' => $path];
        }
    }

    /** @return array<string, mixed> */
    private function payload(Media $media): array
    {
        $sources = $media->variants
            ->where('status', 'ready')
            ->sortBy('width')
            ->groupBy('mime_type')
            ->map(fn (Collection $variants, string $mime): array => [
                'mime' => $mime,
                'srcset' => $variants->map(fn ($variant): string => route('public.api.v1.media.content', [
                    'media' => $media->public_id, 'variant' => $variant->variant_key,
                ]).' '.((int) $variant->width).'w')->implode(', '),
            ])->values()->all();

        return [
            'publicId' => $media->public_id,
            'url' => route('public.api.v1.media.content', ['media' => $media->public_id]),
            'width' => (int) ($media->width ?? 0),
            'height' => (int) ($media->height ?? 0),
            'sources' => $sources,
        ];
    }

    private function message(string $key): string
    {
        $message = __("page_builder.validation.{$key}");

        return is_string($message) ? $message : $key;
    }
}

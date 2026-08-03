<?php

namespace App\Domain\PageBuilder\Rendering;

use App\Domain\Seo\StructuredDataBuilder;
use Illuminate\Support\HtmlString;

final readonly class ContentBlockViewData
{
    public function __construct(private LayoutClassResolver $classes, private StructuredDataBuilder $structuredData) {}

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function make(array $block, string $childrenHtml): array
    {
        $props = is_array($block['props'] ?? null) ? $block['props'] : [];
        $mediaMap = is_array($block['_mediaMap'] ?? null) ? $block['_mediaMap'] : [];
        $type = (string) ($block['type'] ?? '');

        return [
            'blockId' => (string) ($block['id'] ?? ''),
            'classes' => $this->classes->classes($block),
            'props' => $props,
            'children' => new HtmlString($childrenHtml),
            'media' => $this->media($mediaMap, $props['mediaId'] ?? null),
            'mediaItems' => $this->mediaItems($mediaMap, $props['items'] ?? []),
            'richHtml' => new HtmlString((string) ($props['html'] ?? '')),
            'faqItems' => $this->faqItems($props['items'] ?? []),
            'faqJson' => $this->faqJson($type, $props),
            'iconClass' => $this->iconClass((string) ($props['name'] ?? '')),
            'videoUrl' => $this->videoUrl($type, $props),
        ];
    }

    /**
     * @param  array<string, mixed>  $mediaMap
     * @return array<string, mixed>|null
     */
    private function media(array $mediaMap, mixed $publicId): ?array
    {
        return is_string($publicId) && is_array($mediaMap[$publicId] ?? null) ? $mediaMap[$publicId] : null;
    }

    /**
     * @param  array<string, mixed>  $mediaMap
     * @return list<array<string, mixed>>
     */
    private function mediaItems(array $mediaMap, mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(function (mixed $item) use ($mediaMap): array {
            $props = is_array($item) ? $item : [];

            return [...$props, 'media' => $this->media($mediaMap, $props['mediaId'] ?? null)];
        }, $items));
    }

    /** @return list<array{question: string, answer: HtmlString}> */
    private function faqItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $item): array => [
            'question' => is_array($item) ? (string) ($item['question'] ?? '') : '',
            'answer' => new HtmlString(is_array($item) ? (string) ($item['answer'] ?? '') : ''),
        ], $items));
    }

    /** @param array<string, mixed> $props */
    private function faqJson(string $type, array $props): ?HtmlString
    {
        if ($type !== 'content.faq' || ($props['verified'] ?? false) !== true || ! is_array($props['items'] ?? null)) {
            return null;
        }
        $items = array_map(static fn (array $item): array => [
            'question' => (string) ($item['question'] ?? ''),
            'answer' => (string) ($item['answer'] ?? ''),
        ], $props['items']);

        return new HtmlString($this->structuredData->encode($this->structuredData->faq($items)));
    }

    private function iconClass(string $name): string
    {
        return match ($name) {
            'truck' => 'fa-solid fa-truck',
            'warehouse' => 'fa-solid fa-warehouse',
            'phone' => 'fa-solid fa-phone',
            'envelope' => 'fa-solid fa-envelope',
            'check' => 'fa-solid fa-check',
            'star' => 'fa-solid fa-star',
            'quote' => 'fa-solid fa-quote-left',
            default => 'fa-solid fa-leaf',
        };
    }

    /** @param array<string, mixed> $props */
    private function videoUrl(string $type, array $props): ?string
    {
        if ($type !== 'media.video-embed') {
            return null;
        }
        $id = (string) ($props['videoId'] ?? '');

        return ($props['provider'] ?? null) === 'vimeo'
            ? "https://player.vimeo.com/video/{$id}?dnt=1"
            : "https://www.youtube-nocookie.com/embed/{$id}?rel=0";
    }
}

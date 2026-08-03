<?php

namespace App\Domain\Seo;

use App\Domain\Localization\TranslatableModel;
use App\Domain\Settings\CompanySettingsService;
use App\Models\Media;
use App\Models\SeoMeta;

final readonly class SeoMetaResolver
{
    public function __construct(private CompanySettingsService $settings) {}

    /**
     * @param  array<string, mixed>  $pageMeta
     * @param  array<string, string>  $alternateUrls
     * @return array<string, mixed>
     */
    public function resolve(
        string $type,
        TranslatableModel $entity,
        string $locale,
        string $generatedCanonical,
        string $context = 'public',
        array $pageMeta = [],
        array $alternateUrls = [],
    ): array {
        $meta = SeoMeta::query()
            ->with(['ogImage.variants'])
            ->where('seoable_type', $type)
            ->where('seoable_id', $entity->getKey())
            ->where('locale', $locale)
            ->first();
        $translation = $entity->translationForLocale($locale);
        $legacyTitle = $translation?->getAttribute('meta_title') ?? $translation?->getAttribute('name') ?? $translation?->getAttribute('title');
        $legacyDescription = $translation?->getAttribute('meta_description') ?? $translation?->getAttribute('short_description') ?? $translation?->getAttribute('summary') ?? $translation?->getAttribute('excerpt');
        $title = $this->first($meta?->meta_title, $pageMeta['meta_title'] ?? null, $legacyTitle, $this->settings->value('seo_defaults', 'site_title'));
        $description = $this->first($meta?->meta_description, $pageMeta['meta_description'] ?? null, $legacyDescription, $this->settings->value('seo_defaults', 'meta_description'));
        $isPublished = $entity->getAttribute('status') === 'published'
            && ($entity->getAttribute('published_at') === null || $entity->getAttribute('published_at')->isPast());
        $allowIndexing = (bool) $this->settings->value('seo_defaults', 'public_indexing_enabled', true);
        $index = $context === 'public' && $isPublished && $allowIndexing && ($meta->robots_index ?? true);
        $follow = $context === 'public' && $isPublished && ($meta->robots_follow ?? true);
        $canonical = $this->safeUrl($meta?->canonical_url) ?? $this->safeUrl($generatedCanonical);
        $image = $meta->ogImage ?? $this->defaultImage();
        $ogTitle = $this->first($meta?->og_title, $title);
        $ogDescription = $this->first($meta?->og_description, $description);

        return [
            'title' => $title,
            'description' => $description,
            'canonical_url' => $canonical,
            'robots' => ($index ? 'index' : 'noindex').', '.($follow ? 'follow' : 'nofollow'),
            'og' => [
                'title' => $ogTitle,
                'description' => $ogDescription,
                'type' => $meta->og_type ?? 'website',
                'image' => $this->imagePayload($image),
            ],
            'twitter' => [
                'card' => $meta->twitter_card ?? 'summary_large_image',
                'title' => $this->first($meta?->twitter_title, $ogTitle),
                'description' => $this->first($meta?->twitter_description, $ogDescription),
            ],
            'alternates' => collect($alternateUrls)
                ->map(fn (string $url, string $alternateLocale): ?array => in_array($alternateLocale, ['vi', 'en', 'zh'], true) && $this->safeUrl($url) !== null ? ['locale' => $alternateLocale, 'url' => $url] : null)
                ->filter()->values()->all(),
        ];
    }

    private function defaultImage(): ?Media
    {
        $publicId = $this->settings->value('branding', 'default_og_media_id');

        return is_string($publicId) ? Media::query()->with('variants')->where('public_id', $publicId)->where('status', 'ready')->where('visibility', 'public')->first() : null;
    }

    /** @return array<string, mixed>|null */
    private function imagePayload(?Media $image): ?array
    {
        if (! $image instanceof Media || ! str_starts_with($image->mime_type, 'image/')) {
            return null;
        }

        $variant = $image->variants->where('status', 'ready')->sortByDesc(fn ($item) => (int) $item->width)->first();

        return [
            'media_public_id' => $image->public_id,
            'variant' => $variant?->variant_key,
            'url' => url('/api/public/v1/media/'.$image->public_id.'/content'.($variant === null ? '' : '?variant='.$variant->variant_key)),
            'width' => $variant->width ?? $image->width,
            'height' => $variant->height ?? $image->height,
            'alt' => $image->alt_text,
        ];
    }

    private function safeUrl(mixed $url): ?string
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) ? $url : null;
    }

    private function first(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}

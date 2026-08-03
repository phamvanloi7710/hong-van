<?php

namespace App\Domain\PageBuilder\Rendering;

final class LayoutClassResolver
{
    private const RESPONSIVE_VALUES = [
        'paddingY' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'],
        'paddingX' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'],
        'gap' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'],
        'size' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'],
        'marginY' => ['none', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'],
        'align' => ['start', 'center', 'end', 'stretch'],
        'justify' => ['start', 'center', 'end', 'between'],
        'direction' => ['vertical', 'horizontal'],
        'columns' => ['1', '2', '3', '4'],
    ];

    /**
     * @param  array<string, mixed>  $block
     * @return list<string>
     */
    public function classes(array $block): array
    {
        $type = (string) $block['type'];
        $classes = ['pb-block', 'pb-'.str_replace('.', '-', $type)];
        $classes = [...$classes, ...$this->visibilityClasses((array) ($block['visibility'] ?? []))];
        $classes = [...$classes, ...$this->responsiveClasses((array) ($block['style'] ?? []))];

        return [...$classes, ...$this->propertyClasses($type, (array) ($block['props'] ?? []))];
    }

    /** @param array<string, mixed> $block */
    public function backgroundImageUrl(array $block): ?string
    {
        if ($block['type'] !== 'layout.section' || data_get($block, 'props.background') !== 'media') {
            return null;
        }
        $mediaId = data_get($block, 'props.backgroundMediaId');
        if (! is_string($mediaId) || preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $mediaId) !== 1) {
            return null;
        }

        return route('public.api.v1.media.content', ['media' => $mediaId]);
    }

    /**
     * @param  array<string, mixed>  $visibility
     * @return list<string>
     */
    private function visibilityClasses(array $visibility): array
    {
        $classes = [];
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            if (($visibility[$breakpoint] ?? true) === false) {
                $classes[] = "pb-hide-{$breakpoint}";
            }
        }

        return $classes;
    }

    /**
     * @param  array<string, mixed>  $style
     * @return list<string>
     */
    private function responsiveClasses(array $style): array
    {
        $classes = [];
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $values = is_array($style[$breakpoint] ?? null) ? $style[$breakpoint] : [];
            foreach (self::RESPONSIVE_VALUES as $property => $allowed) {
                $value = $values[$property] ?? null;
                if (is_string($value) && in_array($value, $allowed, true)) {
                    $classes[] = "pb-{$breakpoint}-".strtolower($property)."-{$value}";
                }
            }
        }

        return $classes;
    }

    /**
     * @param  array<string, mixed>  $props
     * @return list<string>
     */
    private function propertyClasses(string $type, array $props): array
    {
        return match ($type) {
            'layout.section' => ['pb-section--background-'.$this->allowed((string) ($props['background'] ?? ''), ['transparent', 'surface', 'surface-muted', 'brand', 'brand-soft', 'gradient-brand', 'media'], 'transparent')],
            'layout.container' => ['pb-container--'.$this->allowed((string) ($props['width'] ?? ''), ['narrow', 'default', 'wide', 'full'], 'default')],
            'layout.stack' => ($props['wrap'] ?? false) === true ? ['pb-stack--wrap'] : [],
            'layout.columns' => [
                'pb-columns--desktop-'.$this->allowed((string) ($props['desktopPreset'] ?? ''), ['equal-2', 'equal-3', 'equal-4', 'sidebar-left', 'sidebar-right'], 'equal-2'),
                'pb-columns--tablet-'.$this->allowed((string) ($props['tabletPreset'] ?? ''), ['equal-2', 'stack'], 'equal-2'),
                'pb-columns--mobile-stack',
            ],
            'layout.divider' => [
                'pb-divider--'.$this->allowed((string) ($props['variant'] ?? ''), ['solid', 'dashed'], 'solid'),
                'pb-divider--'.$this->allowed((string) ($props['color'] ?? ''), ['border', 'brand', 'muted'], 'border'),
            ],
            default => [],
        };
    }

    /** @param list<string> $allowed */
    private function allowed(string $value, array $allowed, string $fallback): string
    {
        return in_array($value, $allowed, true) ? $value : $fallback;
    }
}

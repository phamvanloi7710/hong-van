<?php

namespace App\Domain\Themes;

final class ThemeCssCompiler
{
    /** @param array<string, mixed> $tokens */
    public function compile(array $tokens): string
    {
        $fonts = (array) config('public_theme.fonts', []);
        $shadows = (array) config('public_theme.shadow_presets', []);
        $animations = (array) config('public_theme.animation_presets', []);
        $radiusKey = (string) data_get($tokens, 'buttons.radius');
        $animation = (array) ($animations[(string) data_get($tokens, 'animation.preset')] ?? []);
        $variables = [
            '--public-brand' => data_get($tokens, 'colors.brand'), '--public-brand-strong' => data_get($tokens, 'colors.brand_strong'),
            '--public-brand-deep' => data_get($tokens, 'colors.brand_deep'), '--public-brand-soft' => data_get($tokens, 'colors.brand_soft'),
            '--public-accent' => data_get($tokens, 'colors.accent'), '--public-surface' => data_get($tokens, 'colors.surface'),
            '--public-surface-muted' => data_get($tokens, 'colors.surface_muted'), '--public-surface-dark' => data_get($tokens, 'colors.surface_dark'),
            '--public-text' => data_get($tokens, 'colors.text'), '--public-text-muted' => data_get($tokens, 'colors.text_muted'),
            '--public-border' => data_get($tokens, 'colors.border'), '--public-focus' => data_get($tokens, 'colors.focus'),
            '--public-font-body' => $fonts[(string) data_get($tokens, 'fonts.body')] ?? '',
            '--public-font-heading' => $fonts[(string) data_get($tokens, 'fonts.heading')] ?? '',
            '--public-font-base' => $this->px($tokens, 'sizes.base'), '--public-font-small' => $this->px($tokens, 'sizes.small'),
            '--public-font-large' => $this->px($tokens, 'sizes.large'), '--public-h1-min' => $this->px($tokens, 'sizes.h1_min'),
            '--public-h1-max' => $this->px($tokens, 'sizes.h1_max'), '--public-h2-min' => $this->px($tokens, 'sizes.h2_min'),
            '--public-h2-max' => $this->px($tokens, 'sizes.h2_max'), '--public-space-xs' => $this->px($tokens, 'spacing.xs'),
            '--public-space-sm' => $this->px($tokens, 'spacing.sm'), '--public-space-md' => $this->px($tokens, 'spacing.md'),
            '--public-space-lg' => $this->px($tokens, 'spacing.lg'), '--public-space-xl' => $this->px($tokens, 'spacing.xl'),
            '--public-space-2xl' => $this->px($tokens, 'spacing.2xl'), '--public-space-3xl' => $this->px($tokens, 'spacing.3xl'),
            '--public-radius-small' => $this->px($tokens, 'radii.small'), '--public-radius-medium' => $this->px($tokens, 'radii.medium'),
            '--public-radius-large' => $this->px($tokens, 'radii.large'), '--public-radius-pill' => $this->px($tokens, 'radii.pill'),
            '--public-shadow' => $shadows[(string) data_get($tokens, 'shadows.preset')] ?? 'none',
            '--public-container-max' => $this->px($tokens, 'containers.max'), '--public-container-narrow' => $this->px($tokens, 'containers.narrow'),
            '--public-gutter-min' => $this->px($tokens, 'containers.gutter_min'), '--public-gutter-max' => $this->px($tokens, 'containers.gutter_max'),
            '--public-button-min-height' => $this->px($tokens, 'buttons.min_height'), '--public-button-padding-x' => $this->px($tokens, 'buttons.horizontal_padding'),
            '--public-button-radius' => $this->px($tokens, 'radii.'.$radiusKey), '--public-button-weight' => data_get($tokens, 'buttons.font_weight'),
            '--public-heading-weight' => data_get($tokens, 'headings.font_weight'), '--public-heading-line-height' => data_get($tokens, 'headings.line_height'),
            '--public-section-gap' => $this->px($tokens, 'sections.gap'), '--public-animation-duration' => $animation['duration'] ?? '0ms',
            '--public-animation-easing' => $animation['easing'] ?? 'linear',
            '--color-brand' => data_get($tokens, 'colors.brand'), '--color-brand-strong' => data_get($tokens, 'colors.brand_strong'),
            '--color-brand-deep' => data_get($tokens, 'colors.brand_deep'), '--color-brand-soft' => data_get($tokens, 'colors.brand_soft'),
            '--color-accent' => data_get($tokens, 'colors.accent'), '--color-surface' => data_get($tokens, 'colors.surface'),
            '--color-surface-muted' => data_get($tokens, 'colors.surface_muted'), '--color-surface-dark' => data_get($tokens, 'colors.surface_dark'),
            '--color-text' => data_get($tokens, 'colors.text'), '--color-text-muted' => data_get($tokens, 'colors.text_muted'),
            '--color-border' => data_get($tokens, 'colors.border'), '--color-focus' => data_get($tokens, 'colors.focus'),
            '--font-sans' => $fonts[(string) data_get($tokens, 'fonts.body')] ?? '', '--font-display' => $fonts[(string) data_get($tokens, 'fonts.heading')] ?? '',
            '--font-size-sm' => $this->px($tokens, 'sizes.small'), '--font-size-base' => $this->px($tokens, 'sizes.base'),
            '--font-size-lg' => $this->px($tokens, 'sizes.large'), '--font-size-h3' => $this->px($tokens, 'sizes.large'),
            '--font-size-h2' => 'clamp('.$this->px($tokens, 'sizes.h2_min').',4vw,'.$this->px($tokens, 'sizes.h2_max').')',
            '--font-size-h1' => 'clamp('.$this->px($tokens, 'sizes.h1_min').',6vw,'.$this->px($tokens, 'sizes.h1_max').')',
            '--line-height-heading' => data_get($tokens, 'headings.line_height'), '--heading-weight' => data_get($tokens, 'headings.font_weight'),
            '--space-1' => $this->px($tokens, 'spacing.xs'), '--space-2' => $this->px($tokens, 'spacing.sm'),
            '--space-3' => $this->px($tokens, 'spacing.md'), '--space-4' => $this->px($tokens, 'spacing.lg'),
            '--space-5' => $this->px($tokens, 'spacing.xl'), '--space-6' => $this->px($tokens, 'spacing.2xl'),
            '--space-7' => $this->px($tokens, 'spacing.3xl'), '--space-8' => $this->px($tokens, 'sections.gap'),
            '--radius-sm' => $this->px($tokens, 'radii.small'), '--radius-md' => $this->px($tokens, 'radii.medium'),
            '--radius-lg' => $this->px($tokens, 'radii.large'), '--radius-pill' => $this->px($tokens, 'radii.pill'),
            '--shadow-sm' => $shadows[(string) data_get($tokens, 'shadows.preset')] ?? 'none', '--shadow-md' => $shadows[(string) data_get($tokens, 'shadows.preset')] ?? 'none',
            '--container-max' => $this->px($tokens, 'containers.max'), '--container-narrow' => $this->px($tokens, 'containers.narrow'),
            '--container-gutter' => 'clamp('.$this->px($tokens, 'containers.gutter_min').',4vw,'.$this->px($tokens, 'containers.gutter_max').')',
            '--button-min-height' => $this->px($tokens, 'buttons.min_height'), '--button-padding-x' => $this->px($tokens, 'buttons.horizontal_padding'),
            '--button-radius' => $this->px($tokens, 'radii.'.$radiusKey), '--button-weight' => data_get($tokens, 'buttons.font_weight'),
            '--transition-fast' => ($animation['duration'] ?? '0ms').' '.($animation['easing'] ?? 'linear'),
        ];

        $declarations = array_map(
            static fn (string $name, string|int|float|null $value): string => $name.':'.(string) $value,
            array_keys($variables),
            array_values($variables),
        );

        return ':root{'.implode(';', $declarations).'}';
    }

    /** @param array<string, mixed> $tokens */
    private function px(array $tokens, string $path): string
    {
        return (string) data_get($tokens, $path).'px';
    }
}

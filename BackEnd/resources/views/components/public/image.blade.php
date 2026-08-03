@props([
    'media' => null,
    'variant' => null,
    'src' => null,
    'alt' => null,
    'width' => null,
    'height' => null,
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
])
@php
    $resolvedSrc = $src;
    $resolvedAlt = $alt;
    $resolvedWidth = $width;
    $resolvedHeight = $height;

    if ($media instanceof App\Models\Media) {
        $resolvedSrc = route('public.api.v1.media.content', array_filter([
            'media' => $media->public_id,
            'variant' => $variant,
        ], static fn ($value) => $value !== null && $value !== ''));
        $resolvedAlt ??= $media->alt_text;
        $resolvedWidth ??= $media->width;
        $resolvedHeight ??= $media->height;

        if (is_string($variant) && $media->relationLoaded('variants')) {
            $resolvedVariant = $media->variants->firstWhere('variant_key', $variant);
            $resolvedWidth = $resolvedVariant?->width ?? $resolvedWidth;
            $resolvedHeight = $resolvedVariant?->height ?? $resolvedHeight;
        }
    }
@endphp

@if (is_string($resolvedSrc) && $resolvedSrc !== '')
    <img
        src="{{ $resolvedSrc }}"
        alt="{{ $resolvedAlt ?? '' }}"
        @if ($resolvedWidth) width="{{ (int) $resolvedWidth }}" @endif
        @if ($resolvedHeight) height="{{ (int) $resolvedHeight }}" @endif
        loading="{{ $loading === 'eager' ? 'eager' : 'lazy' }}"
        decoding="async"
        fetchpriority="{{ in_array($fetchpriority, ['high', 'low', 'auto'], true) ? $fetchpriority : 'auto' }}"
        {{ $attributes }}
    >
@endif

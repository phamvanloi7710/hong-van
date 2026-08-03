@php($isStatic = in_array($type, ['business.cta', 'business.breadcrumb'], true))
<section class="{{ implode(' ', $classes) }} pb-business pb-business--{{ str_replace('.', '-', $type) }}" data-block-id="{{ $blockId }}" @if($sample) data-preview-sample="true" @endif>
    @if (($props['title'] ?? '') !== '')<h2>{{ $props['title'] }}</h2>@endif
    @if (($props['description'] ?? '') !== '')<p class="pb-business__description">{{ $props['description'] }}</p>@endif
    @if (! $isStatic && $empty)
        <div class="pb-business__empty" @unless($preview) hidden @endunless>{{ ($props['emptyMessage'] ?? '') !== '' ? $props['emptyMessage'] : $emptyLabel }}</div>
    @elseif (! $isStatic)
        <div class="pb-business__items" role="list">
            @foreach ($items as $item)
                <article class="pb-business__item" role="listitem">
                    @if (array_key_exists('value', $item))
                        <strong class="pb-business__value">{{ $item['value'] }}</strong><span>{{ $statLabels[$item['key'] ?? 'sample'] ?? ($item['key'] ?? '') }}</span>
                    @else
                        <h3>{{ $item['title'] ?? $item['name'] ?? '' }}</h3>
                        @if (($item['summary'] ?? '') !== '')<p>{{ $item['summary'] }}</p>@endif
                    @endif
                </article>
            @endforeach
        </div>
    @endif
    @if (($props['ctaUrl'] ?? '') !== '')<a class="pb-business__cta" href="{{ $props['ctaUrl'] }}">{{ $props['ctaLabel'] }}</a>@endif
</section>

<article class="{{ implode(' ', $classes) }} pb-card pb-card--{{ $props['tone'] }}" data-block-id="{{ $blockId }}">
    <h3>{{ $props['title'] }}</h3>
    <p>{{ $props['body'] }}</p>
    @if ($props['linkUrl'] !== '')<a href="{{ $props['linkUrl'] }}" target="{{ $props['target'] }}" @if ($props['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $props['linkLabel'] }}</a>@endif
</article>

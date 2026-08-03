<article class="{{ implode(' ', $classes) }} pb-image-text pb-image-text--{{ $props['imagePosition'] }}" data-block-id="{{ $blockId }}">
    <div class="pb-image-text__media">@include('components.page-builder.blocks.media-picture', ['media' => $media, 'alt' => $props['alt'], 'decorative' => $props['decorative'], 'loading' => 'lazy'])</div>
    <div class="pb-image-text__content"><h3>{{ $props['heading'] }}</h3><p>{{ $props['text'] }}</p>@if ($props['linkUrl'] !== '')<a href="{{ $props['linkUrl'] }}" target="{{ $props['target'] }}" @if ($props['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $props['linkLabel'] }}</a>@endif</div>
</article>

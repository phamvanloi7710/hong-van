<ul class="{{ implode(' ', $classes) }} pb-logo-cloud" data-block-id="{{ $blockId }}" aria-label="{{ $props['label'] }}">
    @foreach ($mediaItems as $item)
        <li>@if ($item['linkUrl'] !== '')<a href="{{ $item['linkUrl'] }}" target="{{ $item['target'] }}" @if ($item['target'] === '_blank') rel="noopener noreferrer" @endif>@endif@include('components.page-builder.blocks.media-picture', ['media' => $item['media'], 'alt' => $item['alt'], 'decorative' => false, 'loading' => 'lazy'])@if ($item['linkUrl'] !== '')</a>@endif</li>
    @endforeach
</ul>

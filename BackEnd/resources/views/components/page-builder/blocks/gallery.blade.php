<div class="{{ implode(' ', $classes) }} pb-gallery pb-gallery--columns-{{ $props['columns'] }}" data-block-id="{{ $blockId }}" role="list" aria-label="{{ $props['label'] }}">
    @foreach ($mediaItems as $item)
        <figure role="listitem">
            @include('components.page-builder.blocks.media-picture', ['media' => $item['media'], 'alt' => $item['alt'], 'decorative' => $item['decorative'], 'loading' => 'lazy'])
            @if ($item['caption'] !== '')<figcaption>{{ $item['caption'] }}</figcaption>@endif
        </figure>
    @endforeach
</div>

<figure class="{{ implode(' ', $classes) }} pb-image pb-image--{{ $props['width'] }}" data-block-id="{{ $blockId }}">
    @include('components.page-builder.blocks.media-picture', ['media' => $media, 'alt' => $props['alt'], 'decorative' => $props['decorative'], 'loading' => $props['loading']])
    @if ($props['caption'] !== '')<figcaption>{{ $props['caption'] }}</figcaption>@endif
</figure>

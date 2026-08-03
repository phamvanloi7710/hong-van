@if ($media)
    <picture>
        @foreach (($media['sources'] ?? []) as $source)
            <source type="{{ $source['mime'] }}" srcset="{{ $source['srcset'] }}" sizes="100vw">
        @endforeach
        <img
            src="{{ $media['url'] }}"
            alt="{{ $alt }}"
            width="{{ $media['width'] }}"
            height="{{ $media['height'] }}"
            loading="{{ $loading ?? 'lazy' }}"
            decoding="async"
            @if ($decorative ?? false) aria-hidden="true" @endif
        >
    </picture>
@endif

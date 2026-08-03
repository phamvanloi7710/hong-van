<section
    class="{{ implode(' ', $classes) }}"
    data-block-id="{{ $blockId }}"
    @if (filled($props['ariaLabel'] ?? null)) aria-label="{{ $props['ariaLabel'] }}" @endif
    @if ($backgroundImageUrl) style="background-image: url('{{ $backgroundImageUrl }}')" @endif
>
    {{ $children }}
</section>

<div class="{{ implode(' ', $classes) }}" data-block-id="{{ $blockId }}">
    <a class="pb-button pb-button--{{ $props['variant'] }}" href="{{ $props['url'] }}" target="{{ $props['target'] }}" @if ($props['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $props['label'] }}</a>
</div>

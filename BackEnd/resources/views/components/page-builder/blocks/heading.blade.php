@php($tag = 'h'.(int) $props['level'])
<{{ $tag }} class="{{ implode(' ', $classes) }}" data-block-id="{{ $blockId }}" @if ($props['anchorId'] !== '') id="{{ $props['anchorId'] }}" @endif>{{ $props['text'] }}</{{ $tag }}>

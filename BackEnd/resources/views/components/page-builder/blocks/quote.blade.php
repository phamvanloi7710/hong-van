<blockquote class="{{ implode(' ', $classes) }} pb-quote" data-block-id="{{ $blockId }}" @if ($props['citeUrl'] !== '') cite="{{ $props['citeUrl'] }}" @endif>
    <p>{{ $props['text'] }}</p>
    @if ($props['attribution'] !== '')<footer>— <cite>{{ $props['attribution'] }}</cite></footer>@endif
</blockquote>

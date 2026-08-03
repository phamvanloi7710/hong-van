@if ($props['ordered'])<ol class="{{ implode(' ', $classes) }} pb-content-list" data-block-id="{{ $blockId }}">@else<ul class="{{ implode(' ', $classes) }} pb-content-list" data-block-id="{{ $blockId }}">@endif
    @foreach ($props['items'] as $item)<li>{{ $item }}</li>@endforeach
@if ($props['ordered'])</ol>@else</ul>@endif

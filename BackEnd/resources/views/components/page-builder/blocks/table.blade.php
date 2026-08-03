<div class="{{ implode(' ', $classes) }} pb-table-scroll" data-block-id="{{ $blockId }}" tabindex="0" role="region" aria-label="{{ $props['caption'] }}">
    <table class="pb-content-table">
        <caption>{{ $props['caption'] }}</caption>
        <thead><tr>@foreach ($props['headers'] as $header)<th scope="col">{{ $header }}</th>@endforeach</tr></thead>
        <tbody>@foreach ($props['rows'] as $row)<tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@endforeach</tbody>
    </table>
</div>

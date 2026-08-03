@props(['tone' => 'info', 'title' => null])
@php
    $resolvedTone = in_array($tone, ['info', 'success', 'warning', 'danger'], true) ? $tone : 'info';
@endphp

<div role="{{ $resolvedTone === 'danger' ? 'alert' : 'status' }}" {{ $attributes->class(['alert', "alert--{$resolvedTone}"]) }}>
    @if ($title)<p class="alert__title">{{ $title }}</p>@endif
    <div class="alert__content">{{ $slot }}</div>
</div>

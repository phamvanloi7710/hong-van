@props(['href' => null, 'type' => 'button', 'variant' => 'primary'])
@php
    $variantClass = in_array($variant, ['primary', 'secondary', 'ghost'], true) ? "button--{$variant}" : 'button--primary';
@endphp

@if (is_string($href) && $href !== '')
    <a href="{{ $href }}" {{ $attributes->class(['button', $variantClass]) }}>{{ $slot }}</a>
@else
    <button type="{{ in_array($type, ['button', 'submit', 'reset'], true) ? $type : 'button' }}" {{ $attributes->class(['button', $variantClass]) }}>{{ $slot }}</button>
@endif

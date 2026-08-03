@props(['href', 'variant' => 'default'])
@php
    $variantClass = $variant === 'subtle' ? 'text-link--subtle' : 'text-link--default';
@endphp

<a href="{{ $href }}" {{ $attributes->class(['text-link', $variantClass]) }}>{{ $slot }}</a>

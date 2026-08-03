@props(['level' => 2])
@php
    $resolvedLevel = in_array((int) $level, [1, 2, 3, 4, 5, 6], true) ? (int) $level : 2;
    $tag = "h{$resolvedLevel}";
@endphp

<{{ $tag }} {{ $attributes->class(['heading', "heading--{$resolvedLevel}"]) }}>{{ $slot }}</{{ $tag }}>

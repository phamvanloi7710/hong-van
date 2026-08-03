@props(['size' => 'default', 'tag' => 'div'])
@php
    $resolvedTag = in_array($tag, ['div', 'section', 'article'], true) ? $tag : 'div';
    $sizeClass = $size === 'narrow' ? 'container--narrow' : 'container--default';
@endphp

<{{ $resolvedTag }} {{ $attributes->class(['container', $sizeClass]) }}>{{ $slot }}</{{ $resolvedTag }}>

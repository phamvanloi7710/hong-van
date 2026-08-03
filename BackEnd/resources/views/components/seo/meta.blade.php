@once
    @if (!empty($meta['title']))
        <title>{{ $meta['title'] }}</title>
        <meta property="og:title" content="{{ $meta['og']['title'] ?? $meta['title'] }}">
        <meta name="twitter:title" content="{{ $meta['twitter']['title'] ?? $meta['title'] }}">
    @endif
    @if (!empty($meta['description']))
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:description" content="{{ $meta['og']['description'] ?? $meta['description'] }}">
        <meta name="twitter:description" content="{{ $meta['twitter']['description'] ?? $meta['description'] }}">
    @endif
    @if (!empty($meta['canonical_url']))
        <link rel="canonical" href="{{ $meta['canonical_url'] }}">
    @endif
    <meta name="robots" content="{{ $meta['robots'] ?? 'noindex, nofollow' }}">
    <meta property="og:type" content="{{ $meta['og']['type'] ?? 'website' }}">
    <meta name="twitter:card" content="{{ $meta['twitter']['card'] ?? 'summary_large_image' }}">
    @if (!empty($meta['og']['image']['url']))
        <meta property="og:image" content="{{ $meta['og']['image']['url'] }}">
        <meta name="twitter:image" content="{{ $meta['og']['image']['url'] }}">
        @if (!empty($meta['og']['image']['width']))<meta property="og:image:width" content="{{ $meta['og']['image']['width'] }}">@endif
        @if (!empty($meta['og']['image']['height']))<meta property="og:image:height" content="{{ $meta['og']['image']['height'] }}">@endif
        @if (!empty($meta['og']['image']['alt']))<meta property="og:image:alt" content="{{ $meta['og']['image']['alt'] }}">@endif
    @endif
    @foreach (($meta['alternates'] ?? []) as $alternate)
        <link rel="alternate" hreflang="{{ $alternate['locale'] }}" href="{{ $alternate['url'] }}">
    @endforeach
@endonce

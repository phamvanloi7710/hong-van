<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>{{ $metaTitle ?? config('app.name') }}</title>
        @if (! empty($metaDescription))
            <meta name="description" content="{{ $metaDescription }}">
        @endif
        @php
            $publicViteReady = Illuminate\Support\Facades\Vite::isRunningHot();
            $viteManifestPath = public_path('build/manifest.json');

            if (! $publicViteReady && file_exists($viteManifestPath)) {
                $viteManifest = json_decode((string) file_get_contents($viteManifestPath), true);
                $publicViteReady = is_array($viteManifest)
                    && isset($viteManifest['resources/css/public/app.css'], $viteManifest['resources/js/public/app.js']);
            }
        @endphp
        @if ($publicViteReady)
            @vite(['resources/css/public/app.css', 'resources/js/public/app.js'])
        @endif
        @stack('head')
    </head>
    <body class="public-page public-page--{{ $currentPage ?? 'default' }}">
        <a class="skip-link" href="#main-content">{{ __('public.skip_to_content') }}</a>

        <header class="site-header">
            <x-public.container class="site-header__inner">
                <a class="site-brand" href="{{ $navigation[0]['url'] ?? route('public.home') }}">
                    <span class="site-brand__mark" aria-hidden="true">HV</span>
                    <span>{{ data_get($site ?? [], 'short_name', config('app.name')) }}</span>
                </a>

                <nav class="site-navigation" aria-label="{{ __('public.navigation_label') }}">
                    <ul class="site-navigation__list">
                        @foreach (($navigation ?? []) as $item)
                            <li>
                                <x-public.link :href="$item['url']" :aria-current="$item['active'] ? 'page' : null">
                                    {{ $item['label'] }}
                                </x-public.link>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <nav class="locale-switcher" aria-label="{{ __('public.language_label') }}">
                    @foreach (($localeLinks ?? []) as $localeLink)
                        <x-public.link
                            :href="$localeLink['url']"
                            :lang="$localeLink['locale']"
                            :hreflang="$localeLink['locale']"
                            :aria-current="$localeLink['active'] ? 'page' : null"
                            variant="subtle"
                        >
                            {{ strtoupper($localeLink['locale']) }}
                            <span class="visually-hidden"> — {{ $localeLink['label'] }}</span>
                        </x-public.link>
                    @endforeach
                </nav>
            </x-public.container>
        </header>

        <main id="main-content" tabindex="-1">
            @yield('content')
        </main>

        <footer class="site-footer">
            <x-public.container>
                <p>{{ __('public.footer.copyright', ['company' => data_get($site ?? [], 'company_name', config('app.name'))]) }}</p>
            </x-public.container>
        </footer>
    </body>
</html>

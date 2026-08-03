<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <meta name="theme-color" content="#63b82e">
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

        <header class="site-header" data-site-header>
            <div class="site-utility">
                <x-public.container class="site-utility__inner">
                    <p>{{ __('public.header.utility') }}</p>
                    <div class="site-utility__contacts">
                        @if (data_get($site ?? [], 'phone'))
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($site, 'phone')) }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ data_get($site, 'phone') }}</a>
                        @endif
                        @if (data_get($site ?? [], 'email'))
                            <a href="mailto:{{ data_get($site, 'email') }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ data_get($site, 'email') }}</a>
                        @endif
                    </div>
                </x-public.container>
            </div>

            <x-public.container class="site-header__main">
                <a class="site-brand" href="{{ $homeUrl ?? route('public.home') }}" aria-label="{{ data_get($site ?? [], 'company_name', config('app.name')) }}">
                    <span class="site-brand__mark" aria-hidden="true"><i class="fa-solid fa-leaf"></i></span>
                    <span class="site-brand__copy">
                        <strong>{{ data_get($site ?? [], 'short_name', config('app.name')) }}</strong>
                        <small>{{ __('public.header.brand_caption') }}</small>
                    </span>
                </a>

                <p class="site-header__scope">{{ __('public.header.scope') }}</p>

                <div class="site-header__actions">
                    <details class="locale-switcher">
                        <summary aria-label="{{ __('public.language_label') }}"><i class="fa-solid fa-globe" aria-hidden="true"></i> {{ strtoupper(app()->getLocale()) }}</summary>
                        <div class="locale-switcher__menu">
                            @foreach (($localeLinks ?? []) as $localeLink)
                                <x-public.link
                                    :href="$localeLink['url']"
                                    :lang="$localeLink['locale']"
                                    :hreflang="$localeLink['locale']"
                                    :aria-current="$localeLink['active'] ? 'page' : null"
                                    variant="subtle"
                                >
                                    {{ $localeLink['label'] }}
                                </x-public.link>
                            @endforeach
                        </div>
                    </details>
                    <x-public.button :href="$quoteUrl ?? '#contact'" class="site-header__quote">
                        {{ __('public.actions.request_quote') }}
                    </x-public.button>
                    <button class="site-menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ __('public.header.toggle_menu') }}</span>
                    </button>
                </div>
            </x-public.container>

            <nav id="primary-navigation" class="site-navigation" aria-label="{{ __('public.navigation_label') }}" data-primary-navigation>
                <x-public.container tag="div">
                    <ul class="site-navigation__list">
                        @foreach (($navigation ?? []) as $item)
                            <li>
                                <x-public.link :href="$item['url']" :aria-current="$item['active'] ? 'page' : null">
                                    {{ $item['label'] }}
                                </x-public.link>
                            </li>
                        @endforeach
                    </ul>
                </x-public.container>
            </nav>
        </header>

        <main id="main-content" tabindex="-1">
            @yield('content')
        </main>

        <footer class="site-footer">
            <x-public.container class="site-footer__grid">
                <div class="site-footer__about">
                    <a class="site-brand site-brand--footer" href="{{ $homeUrl ?? route('public.home') }}">
                        <span class="site-brand__mark" aria-hidden="true"><i class="fa-solid fa-leaf"></i></span>
                        <span class="site-brand__copy"><strong>{{ data_get($site ?? [], 'short_name', config('app.name')) }}</strong></span>
                    </a>
                    <p>{{ __('public.footer.description') }}</p>
                </div>
                <div>
                    <h2>{{ __('public.footer.explore') }}</h2>
                    <ul>
                        @foreach (array_slice($navigation ?? [], 0, 5) as $item)
                            <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2>{{ __('public.footer.policies') }}</h2>
                    <ul>
                        @foreach (($legalNavigation ?? []) as $item)
                            <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2>{{ __('public.footer.contact') }}</h2>
                    <address>
                        @if (data_get($site ?? [], 'address'))
                            <span>{{ data_get($site, 'address') }}</span>
                        @endif
                        @if (data_get($site ?? [], 'phone'))
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($site, 'phone')) }}">{{ data_get($site, 'phone') }}</a>
                        @endif
                        @if (data_get($site ?? [], 'email'))
                            <a href="mailto:{{ data_get($site, 'email') }}">{{ data_get($site, 'email') }}</a>
                        @endif
                        @if (! data_get($site ?? [], 'address') && ! data_get($site ?? [], 'phone') && ! data_get($site ?? [], 'email'))
                            <span>{{ __('public.footer.contact_pending') }}</span>
                        @endif
                    </address>
                </div>
            </x-public.container>
            <div class="site-footer__bottom">
                <x-public.container>
                    <p>{{ __('public.footer.copyright', ['company' => data_get($site ?? [], 'company_name', config('app.name'))]) }}</p>
                </x-public.container>
            </div>
        </footer>
    </body>
</html>

@extends('layouts.public')

@section('content')
    <section class="home-showcase" aria-labelledby="home-heading">
        <x-public.container class="home-showcase__grid">
            <nav class="category-panel" aria-label="{{ __('public.home.categories.label') }}">
                <h2>{{ __('public.home.categories.title') }}</h2>
                <ul>
                    @foreach (['products', 'solutions', 'transportation', 'warehouses', 'news'] as $category)
                        <li>
                            <a href="#{{ $category === 'solutions' ? 'services' : $category }}">
                                @php
                                    $categoryIcons = [
                                        'products' => 'fa-seedling',
                                        'solutions' => 'fa-leaf',
                                        'transportation' => 'fa-truck-fast',
                                        'warehouses' => 'fa-warehouse',
                                        'news' => 'fa-newspaper',
                                    ];
                                @endphp
                                <i class="fa-solid {{ $categoryIcons[$category] }}" aria-hidden="true"></i>
                                <span>{{ __("public.home.categories.{$category}") }}</span>
                                <span aria-hidden="true">›</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="hero-card">
                <div class="hero-card__content">
                    <p class="eyebrow">{{ __('public.pages.home.eyebrow') }}</p>
                    <x-public.heading level="1" id="home-heading">
                        {{ __('public.pages.home.heading') }}
                    </x-public.heading>
                    <p class="hero-card__lead">{{ __('public.pages.home.intro') }}</p>
                    <div class="hero-card__actions">
                        <x-public.button href="#products">{{ __('public.actions.explore_catalog') }}</x-public.button>
                        <x-public.button href="#contact" variant="secondary">{{ __('public.actions.request_quote') }}</x-public.button>
                    </div>
                </div>
                <div class="hero-card__art" aria-hidden="true">
                    <span class="hero-card__sun"></span>
                    <span class="hero-card__field hero-card__field--back"></span>
                    <span class="hero-card__field hero-card__field--front"></span>
                    <span class="hero-card__leaf hero-card__leaf--one"></span>
                    <span class="hero-card__leaf hero-card__leaf--two"></span>
                    <span class="hero-card__leaf hero-card__leaf--three"></span>
                </div>
            </div>

            <div class="showcase-aside">
                <a class="showcase-aside__card showcase-aside__card--transport" href="#transportation">
                    <span>{{ __('public.home.promo.transportation_kicker') }}</span>
                    <strong>{{ __('public.home.promo.transportation') }}</strong>
                    <small>{{ __('public.actions.learn_more') }} →</small>
                </a>
                <a class="showcase-aside__card showcase-aside__card--warehouse" href="#warehouses">
                    <span>{{ __('public.home.promo.warehouse_kicker') }}</span>
                    <strong>{{ __('public.home.promo.warehouse') }}</strong>
                    <small>{{ __('public.actions.learn_more') }} →</small>
                </a>
            </div>
        </x-public.container>
    </section>

    <x-public.container class="benefit-strip" aria-label="{{ __('public.home.benefits.label') }}">
        @foreach (['catalog', 'support', 'quote'] as $benefit)
            <article class="benefit-card">
                @php
                    $benefitIcons = ['catalog' => 'fa-table-cells-large', 'support' => 'fa-comments', 'quote' => 'fa-file-signature'];
                @endphp
                <span class="benefit-card__icon benefit-card__icon--{{ $benefit }}" aria-hidden="true"><i class="fa-solid {{ $benefitIcons[$benefit] }}"></i></span>
                <div>
                    <h2>{{ __("public.home.benefits.{$benefit}.title") }}</h2>
                    <p>{{ __("public.home.benefits.{$benefit}.description") }}</p>
                </div>
            </article>
        @endforeach
    </x-public.container>

    <section id="products" class="home-section home-section--products" aria-labelledby="products-heading">
        <x-public.container>
            <div class="section-heading">
                <div>
                    <p class="eyebrow">{{ __('public.home.products.eyebrow') }}</p>
                    <x-public.heading level="2" id="products-heading">{{ __('public.home.products.title') }}</x-public.heading>
                    <p>{{ __('public.home.products.description') }}</p>
                </div>
                <span class="section-heading__rule" aria-hidden="true"></span>
            </div>

            <div class="catalog-grid">
                @foreach (['nutrition', 'soil', 'crop', 'specialty'] as $catalog)
                    <article class="catalog-card">
                        <div class="catalog-card__visual catalog-card__visual--{{ $catalog }}" aria-hidden="true">
                            <span></span>
                        </div>
                        <div class="catalog-card__body">
                            <h3>{{ __("public.home.products.groups.{$catalog}.title") }}</h3>
                            <p>{{ __("public.home.products.groups.{$catalog}.description") }}</p>
                            <a href="#contact">{{ __('public.actions.contact_for_catalog') }} <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="catalog-note">{{ __('public.home.products.disclaimer') }}</p>
        </x-public.container>
    </section>

    <section id="services" class="home-section home-section--services" aria-labelledby="services-heading">
        <x-public.container>
            <div class="section-heading section-heading--center">
                <div>
                    <p class="eyebrow">{{ __('public.home.services.eyebrow') }}</p>
                    <x-public.heading level="2" id="services-heading">{{ __('public.home.services.title') }}</x-public.heading>
                    <p>{{ __('public.home.services.description') }}</p>
                </div>
            </div>
            <div class="service-grid">
                @foreach (['advisory', 'transportation', 'warehouses'] as $service)
                    <article id="{{ $service === 'advisory' ? 'solutions' : $service }}" class="service-card">
                        <span class="service-card__number">0{{ $loop->iteration }}</span>
                        @php
                            $serviceIcons = ['advisory' => 'fa-seedling', 'transportation' => 'fa-truck-fast', 'warehouses' => 'fa-warehouse'];
                        @endphp
                        <div class="service-card__icon service-card__icon--{{ $service }}" aria-hidden="true"><i class="fa-solid {{ $serviceIcons[$service] }}"></i></div>
                        <h3>{{ __("public.home.services.items.{$service}.title") }}</h3>
                        <p>{{ __("public.home.services.items.{$service}.description") }}</p>
                        <a href="#contact">{{ __('public.actions.request_information') }} <span aria-hidden="true">→</span></a>
                    </article>
                @endforeach
            </div>
        </x-public.container>
    </section>

    <section id="news" class="home-section home-section--news" aria-labelledby="news-heading">
        <x-public.container>
            <div class="section-heading">
                <div>
                    <p class="eyebrow">{{ __('public.home.news.eyebrow') }}</p>
                    <x-public.heading level="2" id="news-heading">{{ __('public.home.news.title') }}</x-public.heading>
                    <p>{{ __('public.home.news.description') }}</p>
                </div>
            </div>
            <div class="news-placeholder">
                <span class="news-placeholder__art" aria-hidden="true"><i class="fa-regular fa-newspaper"></i></span>
                <div>
                    <h3>{{ __('public.home.news.pending_title') }}</h3>
                    <p>{{ __('public.home.news.pending_description') }}</p>
                </div>
            </div>
        </x-public.container>
    </section>

    <section id="contact" class="quote-panel" aria-labelledby="contact-heading">
        <x-public.container class="quote-panel__inner">
            <div>
                <p class="eyebrow">{{ __('public.home.contact.eyebrow') }}</p>
                <x-public.heading level="2" id="contact-heading">{{ __('public.home.contact.title') }}</x-public.heading>
                <p>{{ __('public.home.contact.description') }}</p>
            </div>
            <div class="quote-panel__actions">
                @if (data_get($site, 'phone'))
                    <x-public.button :href="'tel:'.preg_replace('/[^0-9+]/', '', data_get($site, 'phone'))">
                        {{ __('public.actions.call_now') }}: {{ data_get($site, 'phone') }}
                    </x-public.button>
                @endif
                @if (data_get($site, 'email'))
                    <x-public.button :href="'mailto:'.data_get($site, 'email')" variant="secondary">
                        {{ __('public.actions.send_email') }}
                    </x-public.button>
                @endif
                @if (! data_get($site, 'phone') && ! data_get($site, 'email'))
                    <p class="quote-panel__pending">{{ __('public.home.contact.pending') }}</p>
                @endif
            </div>
        </x-public.container>
    </section>
@endsection

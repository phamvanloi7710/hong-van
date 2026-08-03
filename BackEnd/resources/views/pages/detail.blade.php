@extends('layouts.public')

@section('content')
    <x-public.container size="narrow" class="detail-page">
        <x-public.breadcrumbs :items="[
            ['label' => __('public.navigation.home'), 'url' => $homeUrl],
            ['label' => $pageTitle, 'current' => true],
        ]" />
        <article>
            <p class="eyebrow">{{ $detailEyebrow ?? __('public.templates.detail_eyebrow') }}</p>
            <x-public.heading level="1">{{ $pageTitle }}</x-public.heading>
            @if (! empty($pageDescription))
                <p class="detail-page__lead">{{ $pageDescription }}</p>
            @endif
            <div class="prose-content">
                @if (($contentHtml ?? null) instanceof Illuminate\Contracts\Support\Htmlable)
                    {!! $contentHtml->toHtml() !!}
                @else
                    {{ $contentHtml ?? '' }}
                @endif
            </div>
            <aside class="detail-quote-card">
                <h2>{{ __('public.templates.quote_title') }}</h2>
                <p>{{ __('public.templates.quote_description') }}</p>
                <x-public.button :href="$quoteUrl">{{ __('public.actions.request_quote') }}</x-public.button>
            </aside>
        </article>
    </x-public.container>
@endsection

@extends('layouts.public')

@section('content')
    <header class="page-banner">
        <x-public.container>
            <x-public.breadcrumbs :items="[
                ['label' => __('public.navigation.home'), 'url' => $homeUrl],
                ['label' => $pageTitle, 'current' => true],
            ]" />
            <x-public.heading level="1">{{ $pageTitle }}</x-public.heading>
            @if (! empty($pageDescription))
                <p>{{ $pageDescription }}</p>
            @endif
        </x-public.container>
    </header>

    <x-public.container class="listing-page">
        @forelse (($items ?? []) as $item)
            <article class="content-card">
                <div class="content-card__body">
                    <h2>{{ $item['title'] }}</h2>
                    @if (! empty($item['summary']))
                        <p>{{ $item['summary'] }}</p>
                    @endif
                    @if (! empty($item['url']))
                        <a href="{{ $item['url'] }}">{{ __('public.actions.view_details') }} <span aria-hidden="true">→</span></a>
                    @endif
                </div>
            </article>
        @empty
            <x-public.alert tone="info" :title="__('public.templates.empty_title')">
                {{ $emptyMessage ?? __('public.templates.empty_description') }}
            </x-public.alert>
        @endforelse
    </x-public.container>
@endsection

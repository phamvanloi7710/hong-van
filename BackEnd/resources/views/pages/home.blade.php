@extends('layouts.public')

@section('content')
    <section class="hero" aria-labelledby="home-heading">
        <x-public.container size="narrow">
            <p class="eyebrow">{{ __('public.pages.home.eyebrow') }}</p>
            <x-public.heading level="1" id="home-heading">
                {{ data_get($site, 'company_name') }}
            </x-public.heading>
            <p class="hero__lead">{{ __('public.pages.home.intro') }}</p>
            <x-public.alert tone="info" :title="__('public.pages.home.status_title')">
                {{ __('public.pages.home.status_body') }}
            </x-public.alert>
        </x-public.container>
    </section>
@endsection

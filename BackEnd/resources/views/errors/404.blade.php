@extends('layouts.public', [
    'currentPage' => 'error',
    'metaTitle' => __('public.errors.404.title'),
    'site' => ['company_name' => config('app.name'), 'short_name' => config('app.name')],
])

@section('content')
    <x-public.container size="narrow" class="error-page">
        <p class="error-page__code" aria-hidden="true">404</p>
        <x-public.heading level="1">{{ __('public.errors.404.title') }}</x-public.heading>
        <p>{{ __('public.errors.404.message') }}</p>
        <x-public.button :href="route('public.home')">{{ __('public.errors.back_home') }}</x-public.button>
    </x-public.container>
@endsection
